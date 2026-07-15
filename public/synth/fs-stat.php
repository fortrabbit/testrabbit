<?php

/**
 * /synth/fs-stat.php — FS metadata cost (CephFS MDS vs local btrfs/overlay).
 *
 * Two trees, same pod, same PHP:
 *   app   — the real vendor/ tree, i.e. wherever app code lives (CephFS on
 *           NEW, node-local on OLD). scan_ms = directory walk collecting the
 *           paths (readdir + first-touch metadata — the coldest numbers a
 *           single request can get); stat_ms = per-call stats over the
 *           collected paths, clearstatcache()d each call.
 *   local — a small synthetic tree on /dev/shm (fallback: sys_get_temp_dir()),
 *           the node-local RAM/disk baseline. app minus local ≈ what the
 *           app FS costs per metadata op.
 *
 * Caveat: the stat loop revisits paths, so kernel/CephFS-client caches serve
 * repeats — expect stat_ms ≪ scan_ms/file on CephFS. The opcache stat storm
 * behaves the same way, so it's the realistic number; scan_ms is the cold one.
 *
 * Params: ?reps=1000 (10–5000, stat calls) ?files=1000 (10–2000, paths collected)
 */

declare(strict_types=1);

require __DIR__.'/_lib.php';

$reps = synth_param('reps', 1000, 10, 5000);
$maxFiles = synth_param('files', 1000, 10, 2000);
$start = hrtime(true);

$measure = function (array $paths) use ($reps): array {
    mt_srand(1337);
    shuffle($paths);
    $count = count($paths);
    $samples = [];
    for ($i = 0; $i < $reps; $i++) {
        $path = $paths[$i % $count];
        clearstatcache(true, $path);
        $t = hrtime(true);
        stat($path);
        $samples[] = (hrtime(true) - $t) / 1e6;
    }

    return $samples;
};

$vendor = realpath(__DIR__.'/../../vendor');
if ($vendor === false) {
    synth_fail('vendor tree not found');
}
$t = hrtime(true);
$appPaths = synth_collect_files($vendor, $maxFiles);
$scanMs = (hrtime(true) - $t) / 1e6;
if ($appPaths === []) {
    synth_fail('no files found under vendor tree');
}

$local = synth_local_tree();
$localStats = null;
if ($local !== null) {
    $localStats = ['base' => $local['base'], 'stat_ms' => synth_stats($measure($local['files']))];
}

synth_json([
    'test' => 'fs-stat',
    'php' => PHP_VERSION,
    'reps' => $reps,
    'app' => [
        'base' => $vendor,
        'files' => count($appPaths),
        'scan_ms' => round($scanMs, 1),
        'scan_ms_per_file' => round($scanMs / count($appPaths), 4),
        'stat_ms' => synth_stats($measure($appPaths)),
    ],
    'local' => $localStats,
    'total_ms' => round((hrtime(true) - $start) / 1e6, 1),
]);
