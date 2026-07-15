<?php

/**
 * /synth/cpu.php — fixed CPU workload: per-core speed (hardware lottery on the
 * mixed Karpenter frontend pool, PHP version deltas).
 *
 * Two single-threaded workloads with fixed default sizes — compare hash_ms /
 * loop_ms across platforms and runs at the same params:
 *   hash_ms — hash_iters × sha256 over a 1 KB buffer (C-level throughput)
 *   loop_ms — loop_iters of integer arithmetic (opcode/JIT speed)
 *
 * Params: ?hash_iters=20000 (1k–500k) ?loop_iters=2000000 (100k–50M)
 */

declare(strict_types=1);

require __DIR__.'/_lib.php';

$hashIters = synth_param('hash_iters', 20000, 1000, 500000);
$loopIters = synth_param('loop_iters', 2000000, 100000, 50000000);
$start = hrtime(true);

$buf = str_repeat('a', 1024);
$t = hrtime(true);
for ($i = 0; $i < $hashIters; $i++) {
    hash('sha256', $buf);
}
$hashMs = (hrtime(true) - $t) / 1e6;

$acc = 0;
$t = hrtime(true);
for ($i = 0; $i < $loopIters; $i++) {
    $acc = ($acc * 31 + $i) % 1000003;
}
$loopMs = (hrtime(true) - $t) / 1e6;

synth_json([
    'test' => 'cpu',
    'php' => PHP_VERSION,
    'jit' => (string) ini_get('opcache.jit'),
    'hash_iters' => $hashIters,
    'hash_ms' => round($hashMs, 1),
    'hash_mb_per_sec' => round($hashIters / 1024 / ($hashMs / 1000)),
    'loop_iters' => $loopIters,
    'loop_ms' => round($loopMs, 1),
    'checksum' => $acc,
    'total_ms' => round((hrtime(true) - $start) / 1e6, 1),
]);
