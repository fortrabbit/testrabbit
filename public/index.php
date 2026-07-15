<?php

/**
 * testrabbit front controller (IN-1649).
 *
 * Zero-dependency plain PHP: no framework, no composer autoloader. Bootstraps
 * the tiny helper layer, registers the routes and dispatches the request.
 */

use App\Framework\HttpException;
use App\Framework\Request;
use App\Framework\Response;
use App\Framework\Router;
use App\Http\Controllers\Controller;

// The feature tests use relative paths (e.g. glob('imagick/*')) and write
// renditions under the document root, so anchor the working directory there.
chdir(__DIR__);

// Never render PHP notices/warnings into a response body — they would corrupt
// the JSON test contract. Errors are still logged. (Set here, at the web
// boundary only, so the bin/ worker jobs keep their normal stdout/stderr.)
ini_set('display_errors', '0');

require __DIR__ . '/../bootstrap.php';

$router = new Router();
$controller = new Controller();

$router->get('/', static fn(): Response => $controller->index());
$router->get('/tests/{test}', static fn(array $p): Response => $controller->test($p['test']));
$router->get('/imagick-perf', static fn(): Response => $controller->perf());
$router->get('/imagick-perf/run', static fn(): Response => $controller->perfRun());
$router->get('/php-errors', static fn(): Response => $controller->phpErrors());
$router->get('/php-errors/emit', static fn(): Response => $controller->emit());

$match = $router->match(Request::method(), Request::path());

try {
    if ($match === null) {
        http_response_code(404);
        echo 'Not found';

        return;
    }

    [$handler, $params] = $match;
    $handler($params)->send();
} catch (HttpException $e) {
    http_response_code($e->getStatus());
    echo $e->getMessage();
} catch (\Throwable $e) {
    error_log('testrabbit: ' . $e->getMessage());
    http_response_code(500);
    echo 'Internal Server Error: ' . $e->getMessage();
}
