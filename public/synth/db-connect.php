<?php

/**
 * /synth/db-connect.php — DNS + TCP + auth split of the per-request connect cost.
 *
 * Times, over `reps` iterations each (fresh connection every time):
 *   dns_ms          — hostname resolution alone (gethostbyname, no PHP-side cache,
 *                     so this includes the CoreDNS lookup + CNAME chase on NEW)
 *   connect_host_ms — new PDO by hostname (DNS + TCP + auth handshake)
 *   connect_ip_ms   — new PDO by resolved IP literal (TCP + auth only)
 *
 * connect_host_ms − connect_ip_ms ≈ what name resolution costs every request
 * on a platform without persistent connections.
 *
 * Params: ?reps=10 (1–50)
 */

declare(strict_types=1);

require __DIR__.'/_lib.php';

$config = synth_db_config();
$reps = synth_param('reps', 10, 1, 50);
$start = hrtime(true);

$hostIsIp = filter_var($config['host'], FILTER_VALIDATE_IP) !== false;

// DNS resolution alone.
$dns = [];
$ip = $config['host'];
if (!$hostIsIp) {
    for ($i = 0; $i < $reps; $i++) {
        $t = hrtime(true);
        $resolved = gethostbyname($config['host']);
        $dns[] = (hrtime(true) - $t) / 1e6;
        if ($resolved === $config['host']) {
            synth_fail("cannot resolve host {$config['host']}");
        }
        $ip = $resolved;
    }
}

// Fresh PDO connection by hostname, then by IP literal.
$connectBy = function (string $host, bool $verifyCert) use ($config, $reps): array {
    $samples = [];
    for ($i = 0; $i < $reps; $i++) {
        $t = hrtime(true);
        $pdo = synth_connect($config, $host, $verifyCert);
        $samples[] = (hrtime(true) - $t) / 1e6;
        $pdo = null;
    }

    return $samples;
};

try {
    $connectHost = $connectBy($config['host'], true);
} catch (PDOException $e) {
    synth_fail("connect by hostname failed: {$e->getMessage()}");
}

$connectIp = null;
$ipError = null;
if (!$hostIsIp) {
    try {
        $connectIp = $connectBy($ip, false);
    } catch (PDOException $e) {
        $ipError = $e->getMessage();
    }
}

$result = [
    'test' => 'db-connect',
    'php' => PHP_VERSION,
    'reps' => $reps,
    'db_host' => $config['host'],
    'db_ip' => $ip,
    'credential_source' => $config['source'],
    'dns_ms' => $dns ? synth_stats($dns) : null,
    'connect_host_ms' => synth_stats($connectHost),
    'connect_ip_ms' => $connectIp ? synth_stats($connectIp) : null,
    'total_ms' => round((hrtime(true) - $start) / 1e6, 1),
];
if ($ipError !== null) {
    $result['connect_ip_error'] = $ipError;
}

synth_json($result);
