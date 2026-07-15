<?php

/**
 * /synth/db-query.php — per-query round-trip time on one open connection.
 *
 * Connects once (reported separately, not part of the loop), warms up, then
 * runs `reps` × `SELECT 1` and reports per-query stats. Isolates the app→MySQL
 * network RTT + server dispatch from connect cost and buffer-pool effects —
 * the "100× SELECT 1" quick check from the latency gap review.
 *
 * Params: ?reps=100 (1–1000)
 */

declare(strict_types=1);

require __DIR__.'/_lib.php';

$config = synth_db_config();
$reps = synth_param('reps', 100, 1, 1000);
$start = hrtime(true);

try {
    $t = hrtime(true);
    $pdo = synth_connect($config, $config['host']);
    $connectMs = (hrtime(true) - $t) / 1e6;
} catch (PDOException $e) {
    synth_fail("connect failed: {$e->getMessage()}");
}

// Warm-up: TCP slow start, auth caches, prepared-path init.
for ($i = 0; $i < 5; $i++) {
    $pdo->query('SELECT 1')->fetchColumn();
}

$samples = [];
for ($i = 0; $i < $reps; $i++) {
    $t = hrtime(true);
    $pdo->query('SELECT 1')->fetchColumn();
    $samples[] = (hrtime(true) - $t) / 1e6;
}

$loopMs = array_sum($samples);

synth_json([
    'test' => 'db-query',
    'php' => PHP_VERSION,
    'reps' => $reps,
    'db_host' => $config['host'],
    'credential_source' => $config['source'],
    'connect_ms' => round($connectMs, 3),
    'query_ms' => synth_stats($samples),
    'queries_per_sec' => round($reps / ($loopMs / 1000)),
    'total_ms' => round((hrtime(true) - $start) / 1e6, 1),
]);
