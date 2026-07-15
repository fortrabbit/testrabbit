<?php

/**
 * /synth/fs-read.php — FS data path: reading small files (the vendor-bootstrap
 * shape: many small PHP sources).
 *
 * Same two-tree layout as fs-stat.php: `app` = real vendor/ files ≤ 32 KB
 * (CephFS on NEW, node-local on OLD), `local` = synthetic /dev/shm baseline.
 * scan_ms = collecting the paths; read_ms = per-file_get_contents stats,
 * clearstatcache()d each call (page cache still serves repeats — cold data
 * only on first touch).
 *
 * Params: ?reps=100 (1–1000, reads) ?files=500 (10–1000, paths collected)
 */

declare(strict_types=1);

require __DIR__.'/_lib.php';

$reps = synth_param('reps', 100, 1, 1000);
$maxFiles = synth_param('files', 500, 10, 1000);
$start = hrtime(true);

$measure = function (array $paths) use ($reps): array {
    mt_srand(1337);
    shuffle($paths);
    $count = count($paths);
    $samples = [];
    $bytes = 0;
    for ($i = 0; $i < $reps; $i++) {
        $path = $paths[$i % $count];
        clearstatcache(true, $path);
        $t = hrtime(true);
        $data = file_get_contents($path);
        $samples[] = (hrtime(true) - $t) / 1e6;
        $bytes += strlen((string) $data);
    }

    return [$samples, $bytes];
};

$vendor = realpath(__DIR__.'/../../vendor');
if ($vendor === false) {
    synth_fail('vendor tree not found');
}
$t = hrtime(true);
$appPaths = synth_collect_files($vendor, $maxFiles, 32 * 1024);
$scanMs = (hrtime(true) - $t) / 1e6;
if ($appPaths === []) {
    synth_fail('no small files found under vendor tree');
}

[$appSamples, $appBytes] = $measure($appPaths);

$local = synth_local_tree();
$localStats = null;
if ($local !== null) {
    [$localSamples, $localBytes] = $measure($local['files']);
    $localStats = [
        'base' => $local['base'],
        'read_ms' => synth_stats($localSamples),
        'avg_kb' => round($localBytes / $reps / 1024, 2),
    ];
}

synth_json([
    'test' => 'fs-read',
    'php' => PHP_VERSION,
    'reps' => $reps,
    'app' => [
        'base' => $vendor,
        'files' => count($appPaths),
        'scan_ms' => round($scanMs, 1),
        'read_ms' => synth_stats($appSamples),
        'avg_kb' => round($appBytes / $reps / 1024, 2),
        'mb_per_sec' => round($appBytes / 1048576 / (array_sum($appSamples) / 1000), 1),
    ],
    'local' => $localStats,
    'total_ms' => round((hrtime(true) - $start) / 1e6, 1),
]);
