<?php

namespace App\Http\Controllers;

use App\Tests\Test;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        $tests = [
            'Extensions' => 'Extension',
            'Memcached' => 'Memcached',
            'APCu' => 'APCU',
            'MongoDB' => 'MongoDB',
            'MySQL' => 'MySQL',
            'Imagick' => 'ImagickTest',
        ];

        return view('index', [
            'tests' => $tests,
        ]);
    }

    public function test(string $testName)
    {
        $class = '\App\Tests\\' . $testName;
        if (! class_exists($class)) {
            throw new \Exception('Class ' . $testName . ' not found');
        }
        /** @var Test $test */
        $test = new $class;
        $result = $test->execute();

        return json_encode([
            'success' => $result->isSuccessful(),
            'message' => $result->getMessage(),
        ]);
    }
}
