#!/usr/bin/env php
<?php

/**
 * Memory-eating worker job (previously App\Jobs\EatMemoryJob).
 *
 * Usage: php bin/job-eat-memory.php <maxMemoryMB>
 * Allocates in 1 MB steps until peak usage passes the given ceiling, so the
 * platform's per-worker memory limit / OOM behavior can be observed.
 */

echo 'job-eat-memory' . PHP_EOL;

$maxMemory = (int) ($argv[1] ?? 0);
$maxBytes = $maxMemory * 1024 * 1024;

$base = '';
$usedBytes = memory_get_peak_usage(true);

while ($usedBytes <= $maxBytes) {
    $base .= str_repeat('x', 1024 * 1024);
    $usedBytes = memory_get_peak_usage(true) + 5000;
    echo 'Using ' . round($usedBytes / (1024 * 1024)) . 'MB' . PHP_EOL;
}
