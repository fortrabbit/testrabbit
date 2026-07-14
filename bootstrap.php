<?php

/**
 * testrabbit bootstrap — zero-dependency, framework-free (IN-1649).
 *
 * Loaded by public/index.php and the bin/ CLI scripts. Registers a small
 * PSR-4 autoloader for the App\ namespace, loads the .env file (if present),
 * defines the platform constants and the config()/env()/abort()/view() helpers.
 *
 * Deliberately kept to the PHP 7.4 language subset so the exact same code runs
 * on every version the platform serves (7.4 → 8.5).
 */

define('BASE_PATH', __DIR__);

// --- Platform identifiers (previously config/fortrabbit.php) ---------------
const PLATFORM_UBUNTU18 = 'ubuntu18';
const PLATFORM_UBUNTU20 = 'ubuntu20';
const PLATFORM_UBUNTU22 = 'ubuntu22';
const PLATFORM_UBUNTU24 = 'ubuntu24';
const PLATFORM_K8S = 'new';

// --- Autoloader: App\Foo\Bar -> app/Foo/Bar.php ----------------------------
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// --- Minimal .env loader ---------------------------------------------------
(static function (): void {
    $path = BASE_PATH . '/.env';
    if (! is_file($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Strip a single pair of matching surrounding quotes.
        $len = strlen($value);
        if ($len >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[$len - 1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        // Real environment always wins over the .env file.
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
})();

require BASE_PATH . '/app/helpers.php';
