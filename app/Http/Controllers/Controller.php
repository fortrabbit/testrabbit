<?php

namespace App\Http\Controllers;

use App\Tests\Test;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function index()
    {
        $this->deleteTempImages();

        $tests = [
            'Extensions' => 'Extension',
            'Memcached' => 'Memcached',
            'APCu' => 'APCU',
            'MongoDB' => 'MongoDB',
            'MySQL' => 'MySQL',
            'Imagick' => 'ImagickTest',
            'HTTPS Redirect' => 'HttpsRedirect',
            'Custom 404 page' => 'Custom404',
            'Domain Redirect' => 'DomainRedirect',
        ];

        return view('index', [
            'tests' => $tests,
        ]);
    }

    public function test(string $testName): JsonResponse
    {
        $class = '\App\Tests\\' . $testName;
        if (! class_exists($class)) {
            throw new \Exception('Class ' . $testName . ' not found');
        }
        /** @var Test $test */
        $test = new $class;

        if (config('app.type') === 'uni' && $test->appType() !== 'uni') {
            return response()->json([
                'success' => false,
                'pass' => true,
                'message' => 'Pro test: I will not run this on a uni app!'
            ]);
        }

        $result = $test->execute();

        return response()->json([
            'success' => $result->isSuccessful(),
            'pass' => false,
            'message' => $result->getMessage(),
        ]);
    }

    private function deleteTempImages(): void
    {
        $location = __DIR__ . '/../../../public/' . config('imagick.tempLocation');
        $files = glob($location . '/*.webp');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
