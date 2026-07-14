<?php

namespace App\Tests;

class APCU implements Test
{
    public function execute(): Result
    {
        $success = true;
        $message = '';

        if (! function_exists('apcu_store')) {
            return new Result(false, 'APCu is not available (apcu extension not loaded).');
        }

        try {
            apcu_clear_cache();
            $message .= 'Cleared APCu cache.<br>';

            $keys = [];
            for ($i = 0; $i < 1000; $i++) {
                $keys[] = $key = bin2hex(random_bytes(20));
                apcu_store($key, bin2hex(random_bytes(16)));
            }
            $message .= 'Stored 1000 items in APCu.<br>';

            for ($i = 0; $i < 500; $i++) {
                apcu_fetch($keys[random_int(0, 999)]);
            }
            $message .= 'Fetched 500 random items from APCu.<br>';

            for ($i = 0; $i < 100; $i++) {
                apcu_fetch(bin2hex(random_bytes(20)));
            }
            $message .= 'Fetched 100 missing items from APCu.<br>';

            $message .= print_r(apcu_cache_info(), true);
            $message .= print_r(apcu_sma_info(), true);
        } catch (\Throwable $e) {
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
