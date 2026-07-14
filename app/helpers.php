<?php

/**
 * Global helper functions — the tiny surface the old Laravel app relied on
 * (env / config / abort / view). Kept as free functions so test and template
 * code reads exactly like it did before.
 */

use App\Framework\HttpException;
use App\Framework\View;

if (! function_exists('env')) {
    /**
     * Read an environment variable, with light type coercion for booleans/null.
     *
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        }
        if ($value === null || $value === false) {
            return $default;
        }
        switch (strtolower((string) $value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'empty':
                return '';
        }
        return $value;
    }
}

if (! function_exists('config')) {
    /**
     * Read a config value by dot path, e.g. config('database.mysql.host').
     *
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        static $config;
        if ($config === null) {
            $config = require BASE_PATH . '/config.php';
        }
        $value = $config;
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (! function_exists('abort')) {
    /**
     * Abort the request with an HTTP status. Caught by the front controller,
     * which sends the status (and optional message) to the client.
     *
     * @throws HttpException
     * @return never
     */
    function abort(int $status, string $message = ''): void
    {
        throw new HttpException($status, $message);
    }
}

if (! function_exists('view')) {
    /**
     * Render a plain-PHP template from templates/ and return the HTML.
     *
     * @param array<string, mixed> $data
     */
    function view(string $name, array $data = []): string
    {
        return View::render($name, $data);
    }
}
