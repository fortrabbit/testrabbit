<?php

namespace App\Framework;

/**
 * Thin accessors over the current request's superglobals.
 */
class Request
{
    /**
     * Read a query-string parameter.
     *
     * @param mixed $default
     * @return mixed
     */
    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');
    }

    /**
     * Incoming request headers as name => [values], mirroring what the old
     * template rendered. Uses getallheaders() when available, otherwise
     * reconstructs from $_SERVER.
     *
     * @return array<string, string[]>
     */
    public static function headers(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower($name)] = [$value];
            }

            return $headers;
        }

        foreach ($_SERVER as $key => $value) {
            if (strncmp($key, 'HTTP_', 5) === 0) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = [$value];
            }
        }

        return $headers;
    }
}
