<?php

/**
 * Application configuration (replaces Laravel's config/*.php).
 *
 * A plain nested array, read via the config('dot.path') helper. Values come
 * from the environment so the same image behaves differently per app/region.
 */

return [
    'app' => [
        // "uni" or "pro". Pro-only tests are skipped on uni apps.
        'type' => env('APP_TYPE', 'uni'),
        'locale' => env('APP_LOCALE', 'en'),
    ],

    'fortrabbit' => [
        // One of the PLATFORM_* constants; drives Imagick format support.
        'platform' => env('FRBIT_PLATFORM', PLATFORM_UBUNTU18),
    ],

    'imagick' => [
        'tempLocation' => 'imagick/tmp/',
    ],

    'gd' => [
        'tempLocation' => 'gd/tmp/',
    ],

    'database' => [
        // Full MongoDB connection string (mongodb+srv://…).
        'mongodb' => env('MONGODB_CONNECTION', ''),

        'mysql' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
        ],
    ],
];
