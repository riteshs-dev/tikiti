<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/health';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/bootstrap.php';
\Config::load();

use App\Router\Router;

$router = new Router();
$router->get('/health', function() {
    return json_encode(['status' => 'ok']);
});

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';

echo "Method: $method\n";
echo "REQUEST_URI: $requestUri\n";
echo "Path: $path\n";
echo "Routes count: " . count($router->routes) . "\n";

foreach ($router->routes as $r) {
    echo "  Route: {$r['method']} {$r['path']}\n";
    $params = [];
    $match = $router->matchPath($r['path'], $path, $params);
    echo "    Match: " . ($match ? 'YES' : 'NO') . "\n";
}

$router->dispatch();
