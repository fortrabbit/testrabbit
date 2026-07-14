<?php

namespace App\Tests;

use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;

/**
 * MongoDB connectivity check via the native ext-mongodb driver (no composer
 * library). Runs listDatabases against the connection string in
 * config('database.mongodb').
 */
class MongoDB implements Test
{
    public function execute(): Result
    {
        $success = true;

        if (! class_exists(Manager::class)) {
            return new Result(false, 'ext-mongodb is not loaded.');
        }

        try {
            $connection = config('database.mongodb');
            if ($connection === '' || $connection === null) {
                return new Result(false, 'No MongoDB connection configured (MONGODB_CONNECTION).');
            }

            $manager = new Manager($connection);
            $cursor = $manager->executeCommand('admin', new Command(['listDatabases' => 1]));
            $message = print_r($cursor->toArray(), true);
        } catch (\Throwable $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return new Result($success, $message);
    }

    public function appType(): string
    {
        return self::APP_UNI;
    }
}
