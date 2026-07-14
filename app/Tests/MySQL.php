<?php

namespace App\Tests;

use PDO;

class MySQL implements Test
{
    public function execute(): Result
    {
        $success = true;
        try {
            $mysql = config('database.mysql');
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=utf8mb4',
                $mysql['host'],
                $mysql['port']
            );
            $pdo = new PDO($dsn, $mysql['username'], $mysql['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $databases = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
            $message = print_r($databases, true);
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
