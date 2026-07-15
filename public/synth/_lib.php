<?php

/**
 * Shared helpers for the /synth/* self-timing endpoints (plain PHP, no Laravel).
 *
 * See performancetest/docs/2026-07-14-old-vs-new-latency-gap-review.md
 * ("Synthetic benchmark proposal"). Each endpoint returns its measurement as
 * JSON so k6 can record both TTFB and the inner time.
 */

declare(strict_types=1);

function synth_env(string $key): ?string
{
    $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);

    return ($value === false || $value === null || $value === '') ? null : (string) $value;
}

/**
 * Resolve MySQL credentials across all deployment targets:
 *
 *  1. FORTRABBIT_DB_*   — NEW platform (k8s-app-operator injected)
 *  2. APP_SECRETS json  — OLD platform (MYSQL section)
 *  3. DB_*              — VPS / generic (Laravel-style env vars)
 *  4. ../.env file      — local dev (plain PHP doesn't get Laravel's env loading)
 *
 * @return array{host: string, port: int, user: string, password: string, database: string, source: string}
 */
function synth_db_config(): array
{
    if (($host = synth_env('FORTRABBIT_DB_HOST')) !== null) {
        return [
            'host' => $host,
            'port' => (int) (synth_env('FORTRABBIT_DB_PORT') ?? 3306),
            'user' => synth_env('FORTRABBIT_DB_USER') ?? '',
            'password' => synth_env('FORTRABBIT_DB_PASSWORD') ?? '',
            'database' => synth_env('FORTRABBIT_DB_NAME') ?? '',
            'source' => 'FORTRABBIT_DB_*',
        ];
    }

    if (($secretsFile = synth_env('APP_SECRETS')) !== null && is_readable($secretsFile)) {
        $secrets = json_decode((string) file_get_contents($secretsFile), true);
        if (isset($secrets['MYSQL']['HOST'])) {
            $mysql = $secrets['MYSQL'];

            return [
                'host' => (string) $mysql['HOST'],
                'port' => (int) ($mysql['PORT'] ?? 3306),
                'user' => (string) ($mysql['USER'] ?? ''),
                'password' => (string) ($mysql['PASSWORD'] ?? ''),
                'database' => (string) ($mysql['DATABASE'] ?? ''),
                'source' => 'APP_SECRETS',
            ];
        }
    }

    if (($host = synth_env('DB_HOST')) !== null) {
        return [
            'host' => $host,
            'port' => (int) (synth_env('DB_PORT') ?? 3306),
            'user' => synth_env('DB_USERNAME') ?? '',
            'password' => synth_env('DB_PASSWORD') ?? '',
            'database' => synth_env('DB_DATABASE') ?? '',
            'source' => 'DB_*',
        ];
    }

    $dotenv = synth_parse_dotenv(dirname(__DIR__, 2).'/.env');
    if (isset($dotenv['DB_HOST'])) {
        return [
            'host' => $dotenv['DB_HOST'],
            'port' => (int) ($dotenv['DB_PORT'] ?? 3306),
            'user' => $dotenv['DB_USERNAME'] ?? '',
            'password' => $dotenv['DB_PASSWORD'] ?? '',
            'database' => $dotenv['DB_DATABASE'] ?? '',
            'source' => '.env',
        ];
    }

    synth_fail('no database credentials found (tried FORTRABBIT_DB_*, APP_SECRETS, DB_*, .env)');
}

/** @return array<string, string> */
function synth_parse_dotenv(string $path): array
{
    if (!is_readable($path)) {
        return [];
    }

    $vars = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if ($line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $vars[trim($key)] = trim(trim($value), '"\'');
    }

    return $vars;
}

/** PDO options, honouring the optional SSL CA the platforms may require. */
function synth_pdo_options(bool $verifyServerCert = true): array
{
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ];

    // Same version split as config/database.php: Pdo\Mysql exists only on
    // PHP 8.4+; the PDO::MYSQL_ATTR_* constants are deprecated on 8.5.
    $sslCa = PHP_VERSION_ID >= 80400 ? \Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA;
    $sslVerify = PHP_VERSION_ID >= 80400 ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;

    if (($ca = synth_env('MYSQL_ATTR_SSL_CA')) !== null) {
        $options[$sslCa] = $ca;
        // Connecting by IP literal makes hostname verification fail by design.
        $options[$sslVerify] = $verifyServerCert;
    }

    return $options;
}

function synth_connect(array $config, string $host, bool $verifyServerCert = true): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $host, $config['port'], $config['database']);

    return new PDO($dsn, $config['user'], $config['password'], synth_pdo_options($verifyServerCert));
}

/** min/median/avg/p95/max over millisecond samples. */
function synth_stats(array $ms): array
{
    sort($ms);
    $n = count($ms);

    return [
        'n' => $n,
        'min' => round($ms[0], 3),
        'med' => round($ms[intdiv($n, 2)], 3),
        'avg' => round(array_sum($ms) / $n, 3),
        'p95' => round($ms[min($n - 1, (int) ceil($n * 0.95) - 1)], 3),
        'max' => round($ms[$n - 1], 3),
    ];
}

/** Clamped integer query parameter. */
function synth_param(string $name, int $default, int $min, int $max): int
{
    $value = isset($_GET[$name]) ? (int) $_GET[$name] : $default;

    return max($min, min($max, $value));
}

function synth_json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit;
}

function synth_fail(string $message): never
{
    synth_json(['error' => $message], 500);
}
