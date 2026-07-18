<?php
// load.php — synthetic PHP-plan-scaling instrument. Zero deps, ZERO DB coupling.
// Knobs: ?wait=<ms> (I/O wait), ?burn=<ms> (CPU), ?mb=<N> (hold N MB), ?info=1 (probe).
// See KB: "PHP plan scaling — test design".

$cpuCount = function (): ?int {
    $c = @file_get_contents('/proc/cpuinfo');
    if ($c === false) return null;
    return substr_count($c, 'processor') ?: null;
};

$t0 = microtime(true);
$wait = isset($_GET['wait']) ? max(0, (int) $_GET['wait']) : 0;
$burn = isset($_GET['burn']) ? max(0, (int) $_GET['burn']) : 0;
$mb   = isset($_GET['mb'])   ? max(0, (int) $_GET['mb'])   : 0;

header('Content-Type: application/json');

if (isset($_GET['info'])) {
    echo json_encode([
        'memory_limit' => ini_get('memory_limit'),
        'peak_bytes'   => memory_get_peak_usage(true),
        'php'          => PHP_VERSION,
        'host'         => gethostname(),
        'pid'          => getmypid(),
        'cpu_count'    => $cpuCount(),
    ]);
    exit;
}

// Memory: hold ~mb MB of DISTINCT bytes so it is real resident memory
// (random_bytes defeats copy-on-write and string interning). May OOM -> HTTP 500.
$hold = [];
for ($i = 0; $i < $mb; $i++) {
    $hold[] = random_bytes(1024 * 1024);
}

// CPU: busy-loop ~burn ms (no sleep)
if ($burn > 0) {
    $end = microtime(true) + $burn / 1000.0;
    $x = 0.0;
    while (microtime(true) < $end) {
        for ($j = 1; $j <= 2000; $j++) { $x += sqrt($j); }
    }
}

// Non-DB I/O wait
if ($wait > 0) {
    usleep($wait * 1000);
}

$peak = memory_get_peak_usage(true);
header('X-Peak-MB: ' . round($peak / 1048576, 1));
echo json_encode([
    'wait'         => $wait,
    'burn'         => $burn,
    'mb'           => $mb,
    'peak_bytes'   => $peak,
    'memory_limit' => ini_get('memory_limit'),
    'php'          => PHP_VERSION,
    'host'         => gethostname(),
    'pid'          => getmypid(),
    'elapsed_ms'   => round((microtime(true) - $t0) * 1000.0, 1),
]);
