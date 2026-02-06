<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/v1/events';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once __DIR__ . '/bootstrap.php';
\Config::load();

use App\Router\Router;

$router = new Router();
$router->get('/api/v1/events', function() {
    return ['test' => 'success'];
});

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = '/' . ltrim($path, '/');
$path = rtrim($path, '/') ?: '/';

echo "Method: $method\n";
echo "Path: $path\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Routes:\n";
foreach ($router->routes as $r) {
    echo "  {$r['method']} {$r['path']}\n";
}
