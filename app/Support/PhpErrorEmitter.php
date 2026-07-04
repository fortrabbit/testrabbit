<?php

namespace App\Support;

/**
 * Generates a single, deliberate PHP / HTTP error for the testrabbit
 * "PHP errors test" page (FR-6108).
 *
 * One call == one error. The browser fires many of these through a bounded
 * concurrency pool; this class is the single place the actual error semantics
 * live, so the controller stays thin and the behaviour is easy to reason about.
 */
class PhpErrorEmitter
{
    /** Concrete error types (everything except the "random" meta-type). */
    public const TYPES = ['500', '503', '504', 'warning', 'info'];

    public const SLEEP_MIN = 1;
    public const SLEEP_MAX = 600;
    public const SLEEP_DEFAULT = 65;

    /**
     * Whether $type is something we can emit (incl. the "random" meta-type).
     */
    public static function isValidType(string $type): bool
    {
        return $type === 'random' || in_array($type, self::TYPES, true);
    }

    /**
     * Clamp a raw ?sleep= value into the allowed range.
     */
    public static function clampSleep($raw): int
    {
        $seconds = (int) $raw;

        if ($seconds < self::SLEEP_MIN) {
            return self::SLEEP_MIN;
        }

        if ($seconds > self::SLEEP_MAX) {
            return self::SLEEP_MAX;
        }

        return $seconds;
    }

    /**
     * Resolve the "random" meta-type to a concrete type; pass others through.
     */
    public static function resolveType(string $type): string
    {
        if ($type === 'random') {
            return self::TYPES[array_rand(self::TYPES)];
        }

        return $type;
    }

    /**
     * Emit one error of the (already-resolved, concrete) $type.
     *
     * - 500: throw a real exception (faithful) or abort(500) (instant)
     * - 503: abort(503) — a real overload-503 can't be forced on demand
     * - 504: sleep past the gateway timeout then abort(504) (faithful), or
     *        abort(504) immediately (instant)
     * - warning / info: write a line to PHP's error log (stderr), HTTP 200
     *
     * For 500/503/504 this never returns — it throws or aborts. For warning and
     * info it returns the type so the caller can report it as "logged".
     */
    public function emit(string $type, bool $abort, int $sleep): string
    {
        switch ($type) {
            case '500':
                if ($abort) {
                    abort(500);
                }
                throw new \RuntimeException('testrabbit: synthetic 500 error');

            case '503':
                abort(503);

            case '504':
                if (! $abort) {
                    // Run past the gateway's upstream timeout. Behind the
                    // fortrabbit gateway this produces a genuine 504 while PHP
                    // is still sleeping; locally (no gateway) PHP falls through
                    // to the abort() below after $sleep seconds.
                    set_time_limit(0);
                    sleep($sleep);
                }
                abort(504);

            case 'warning':
                error_log('testrabbit: synthetic PHP warning');

                return 'warning';

            case 'info':
                error_log('testrabbit: synthetic PHP info');

                return 'info';
        }

        // The controller validates first, so this is defensive only.
        abort(400, 'Unknown error type: ' . $type);
    }
}
