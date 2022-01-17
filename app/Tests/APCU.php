<?php

namespace App\Tests;

use Faker\Factory;

class APCU implements Test
{
    public function execute(): Result
    {
        $success = true;
        $message = '';

        try {
            $faker = Factory::create();

            apcu_clear_cache();
            $message .= 'Cleared APCu cache.<br>';

            $keys = [];
            for ($i = 0; $i < 1000; $i++) {
                $keys[] = $key = $faker->unique()->sha1;
                apcu_store($key, $faker->name);
            }
            $message .= 'Stored 1000 items in APCu.<br>';

            for ($i = 0; $i < 500; $i++) {
                $key_id = $faker->numberBetween(0, 999);
                apcu_fetch($keys[$key_id]);
            }
            $message .= 'Fetched 500 random items from APCu.<br>';

            for ($i = 0; $i < 100; $i++) {
                apcu_fetch($faker->sha1);
            }
            $message .= 'Fetched 100 missing items from APCu.<br>';

            $message .= print_r(apcu_cache_info(), true);
            $message .= print_r(apcu_sma_info(), true);
        } catch (\Exception $e) {
            $success = false;
            $message .= $e->getMessage();
        }

        return new Result($success, $message);
    }

    public function appType(): string
    {
        return self::APP_UNI;
    }
}
