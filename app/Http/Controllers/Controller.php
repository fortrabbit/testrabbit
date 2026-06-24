<?php

namespace App\Http\Controllers;

use App\Support\ImagickPerfRunner;
use App\Support\Renditions;
use App\Tests\Test;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Imagick;

class Controller extends BaseController
{
    public function index()
    {
        // Stupid log entry to check if log tailing works
        Log::info('We have visitors!');

        $this->deleteTempImages();

        $tests = [
            'Extensions' => 'Extension',
            'Memcached' => 'Memcached',
            'APCu' => 'APCU',
            'MongoDB' => 'MongoDB',
            'MySQL' => 'MySQL',
            'Imagick' => 'ImagickTest',
            'GD' => 'GD',
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

    /**
     * Dedicated ImageMagick performance test (MR-110).
     *
     * Transforms a fixed set of source images to ?count= JPEG renditions each
     * (default 4) and reports the ms/rendition figure, so the same workload can
     * be run and compared across the old and new fortrabbit platforms.
     */
    public function perf()
    {
        $publicDir = __DIR__ . '/../../../public';
        $tempLocation = config('imagick.tempLocation');
        $tempDir = $publicDir . '/' . $tempLocation;

        // Clear prior renditions so a run reflects only its own work.
        $this->clearDirectory($tempDir);

        $count = Renditions::clampCount($_GET['count'] ?? 4);

        $runner = new ImagickPerfRunner($publicDir . '/imagick', $tempDir);
        $results = $runner->run($count);

        return view('imagick-perf', [
            'count' => $count,
            'results' => $results,
            'platform' => config('fortrabbit.platform'),
            'imagickVersion' => Imagick::getVersion()['versionString'] ?? 'unknown',
            'limits' => $this->imagickLimits(),
        ]);
    }

    /**
     * @return array<string, int|string>
     */
    private function imagickLimits(): array
    {
        return [
            'RESOURCETYPE_MEMORY' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_MEMORY),
            'RESOURCETYPE_MAP' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_MAP),
            'RESOURCETYPE_AREA' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_AREA),
            'RESOURCETYPE_DISK' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_DISK),
            'RESOURCETYPE_FILE' => Imagick::getResourceLimit(Imagick::RESOURCETYPE_FILE),
        ];
    }

    private function deleteTempImages(): void
    {
        $locations = [
            __DIR__ . '/../../../public/' . config('imagick.tempLocation'),
            __DIR__ . '/../../../public/' . config('gd.tempLocation')
        ];

        foreach ($locations as $location) {
            $this->clearDirectory($location);
        }
    }

    private function clearDirectory(string $location): void
    {
        $files = glob($location . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
