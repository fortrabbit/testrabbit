<?php

namespace App\Tests;

use MongoDB\Client;

class MongoDB implements Test
{
    public function execute(): Result
    {
        $success = true;
        try {
            $client = new Client(config('database.mongodb'));
            $message = print_r($client->listDatabases(), true);
        } catch (\Exception $e) {
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
