<?php

namespace App\Tests;

use Illuminate\Support\Facades\DB;

class MySQL implements Test
{
    public function execute(): Result
    {
        $success = true;
        try {
            $databases = DB::select('SHOW DATABASES');
            $message = print_r($databases, true);
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return new Result($success, $message);
    }
}
