#!/usr/bin/env php
<?php

/**
 * Long-running worker job (previously App\Jobs\SleepJob).
 *
 * Standalone CLI script the platform worker/scheduler can run directly — no
 * Laravel queue. Prints to stdout while sleeping so log tailing shows progress.
 */

echo 'job-sleep' . PHP_EOL;

foreach (range(1, 10) as $count) {
    echo $count . PHP_EOL;
    sleep(3);
}

echo 'DONE' . PHP_EOL;
