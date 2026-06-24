<?php

namespace App\Support;

/**
 * Deterministic helpers for the ImageMagick performance test (MR-110).
 *
 * Kept free of Laravel and Imagick dependencies so the rendition maths can be
 * reasoned about and verified in isolation.
 */
class Renditions
{
    /** Largest target width produced. */
    public const MAX_WIDTH = 1600;

    /** Smallest target width produced. */
    public const MIN_WIDTH = 200;

    /** Allowed range for the per-source rendition count. */
    public const MIN_COUNT = 1;
    public const MAX_COUNT = 64;

    /**
     * Clamp a raw ?count= value to a sane integer.
     */
    public static function clampCount($raw): int
    {
        $count = (int) $raw;

        if ($count < self::MIN_COUNT) {
            return self::MIN_COUNT;
        }

        if ($count > self::MAX_COUNT) {
            return self::MAX_COUNT;
        }

        return $count;
    }

    /**
     * Generate $count target widths, evenly spaced and descending from
     * MAX_WIDTH down to MIN_WIDTH (both inclusive).
     *
     * Deterministic: the same count always yields the same list, so runs are
     * comparable across platforms and across repeated runs.
     *
     * @return int[]
     */
    public static function widths(int $count): array
    {
        $count = self::clampCount($count);

        if ($count === 1) {
            return [self::MAX_WIDTH];
        }

        $step = (self::MAX_WIDTH - self::MIN_WIDTH) / ($count - 1);

        $widths = [];
        foreach (range(0, $count - 1) as $i) {
            $widths[] = (int) round(self::MAX_WIDTH - ($i * $step));
        }

        return $widths;
    }
}
