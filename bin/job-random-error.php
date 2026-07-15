#!/usr/bin/env php
<?php

/**
 * Random-error worker job (previously App\Jobs\RandomErrorJob).
 *
 * Throws roughly one time in five so worker error handling / logging can be
 * exercised. Prints to stderr before throwing.
 */

echo 'job-random-error' . PHP_EOL;

if (rand(1, 5) === 1) {
    error_log('Next line will throw an Exception.');
    throw new \Exception('Arrgggh, random error');
}

echo 'OK' . PHP_EOL;
