<?php

namespace App\Support;

use Imagick;

/**
 * Runs the ImageMagick performance workload for MR-110.
 *
 * Transforms a fixed set of source images to $count JPEG renditions each and
 * records per-rendition timing, so the resulting ms/rendition figure can be
 * compared across the old and new fortrabbit platforms.
 */
class ImagickPerfRunner
{
    /** JPEG output quality for every rendition. */
    public const QUALITY = 82;

    /**
     * Fixed source images. All are decodable on every supported platform
     * (Ubuntu 18/20/22/24, k8s) — deliberately no HEIC/AVIF, which old
     * platforms cannot decode.
     */
    public const SOURCES = [
        'jpg-medium.jpeg',
        'jpg-large.jpg',
        'png-medium.png',
        'png-small.png',
    ];

    private string $sourceDir;
    private string $tempDir;

    /**
     * @param string $sourceDir Absolute path to the directory holding the source images.
     * @param string $tempDir   Absolute path to write renditions into.
     */
    public function __construct(string $sourceDir, string $tempDir)
    {
        $this->sourceDir = rtrim($sourceDir, '/');
        $this->tempDir = rtrim($tempDir, '/');
    }

    /**
     * Execute the workload and return structured results.
     *
     * @return array{count:int, widths:int[], sources:array, totals:array}
     */
    public function run(int $count): array
    {
        $count = Renditions::clampCount($count);
        $widths = Renditions::widths($count);

        $sources = [];
        $totalMs = 0.0;
        $totalRenditions = 0;
        $totalFailed = 0;

        foreach (self::SOURCES as $file) {
            $result = $this->runSource($file, $widths);
            $sources[] = $result;

            $totalMs += $result['totalMs'];
            $totalRenditions += $result['renditions'];
            $totalFailed += $result['failed'];
        }

        return [
            'count' => $count,
            'widths' => $widths,
            'sources' => $sources,
            'totals' => [
                'renditions' => $totalRenditions,
                'failed' => $totalFailed,
                'totalMs' => $totalMs,
                'msPerRendition' => $totalRenditions > 0 ? $totalMs / $totalRenditions : 0.0,
            ],
        ];
    }

    /**
     * Transform a single source image to every target width.
     *
     * @param int[] $widths
     */
    private function runSource(string $file, array $widths): array
    {
        $path = $this->sourceDir . '/' . $file;

        $base = [
            'file' => $file,
            'exists' => false,
            'bytes' => 0,
            'renditions' => 0,
            'failed' => 0,
            'totalMs' => 0.0,
            'msPerRendition' => 0.0,
            'error' => null,
        ];

        if (! is_file($path)) {
            $base['error'] = 'source file missing';

            return $base;
        }

        $base['exists'] = true;
        $base['bytes'] = filesize($path);

        // Cap widths to the source width once (untimed) so we only ever downscale.
        try {
            $probe = new Imagick();
            $probe->pingImage($path);
            $sourceWidth = $probe->getImageWidth();
            $probe->destroy();
        } catch (\Throwable $e) {
            $base['error'] = $e->getMessage();

            return $base;
        }

        $totalMs = 0.0;
        foreach ($widths as $width) {
            $targetWidth = min($width, $sourceWidth);

            $start = microtime(true);
            try {
                // Decode fresh per rendition, mirroring how a CMS transforms an
                // asset on each request: decode + resize + encode + write.
                $img = new Imagick($path);
                $img->setImageFormat('jpeg');
                $img->setImageCompressionQuality(self::QUALITY);
                $img->thumbnailImage($targetWidth, 0); // height 0 = preserve aspect

                $out = $this->tempDir . '/' . uniqid('perf.', true) . '.jpeg';
                $img->writeImage($out);
                $img->destroy();

                $totalMs += (microtime(true) - $start) * 1000;
                $base['renditions']++;
            } catch (\Throwable $e) {
                // One bad rendition must not abort the measurement.
                $base['failed']++;
                $base['error'] = $e->getMessage();
            }
        }

        $base['totalMs'] = $totalMs;
        $base['msPerRendition'] = $base['renditions'] > 0
            ? $totalMs / $base['renditions']
            : 0.0;

        return $base;
    }
}
