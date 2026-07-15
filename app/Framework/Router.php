<?php

namespace App\Framework;

/**
 * Tiny array router. Patterns use one optional dynamic segment, e.g.
 * "/tests/{test}"; matching is segment-by-segment, no regex engine.
 */
class Router
{
    /** @var array<int, array{0:string,1:string,2:callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    /**
     * Match method + path against the registered routes.
     *
     * @return array{0:callable,1:array<string,string>}|null
     */
    public function match(string $method, string $path): ?array
    {
        $pathSegments = $this->segments($path);

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }
            $params = $this->matchSegments($this->segments($pattern), $pathSegments);
            if ($params !== null) {
                return [$handler, $params];
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function segments(string $path): array
    {
        $path = trim($path, '/');

        return $path === '' ? [] : explode('/', $path);
    }

    /**
     * @param string[] $pattern
     * @param string[] $path
     * @return array<string, string>|null
     */
    private function matchSegments(array $pattern, array $path): ?array
    {
        if (count($pattern) !== count($path)) {
            return null;
        }

        $params = [];
        foreach ($pattern as $i => $segment) {
            if (strlen($segment) > 1 && $segment[0] === '{' && substr($segment, -1) === '}') {
                $params[substr($segment, 1, -1)] = $path[$i];
                continue;
            }
            if ($segment !== $path[$i]) {
                return null;
            }
        }

        return $params;
    }
}
