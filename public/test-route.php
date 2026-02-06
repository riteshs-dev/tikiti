<?php
// Simple route test
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../bootstrap.php';
\Config::load();

use App\Router\Router;

$router = new Router();

$router->get('/test', function() {
    return json_encode(['message' => 'Test route works!']);
});

$router->get('/api/v1/events', function() {
    require_once __DIR__ . '/../routes/api.php';
    global $router;
    // Get the actual router from routes/api.php
    $router = new Router();
    require __DIR__ . '/../routes/api.php';
    return $router->dispatch();
});

// Simulate request
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/test';
$_SERVER['SCRIPT_NAME'] = '/test-route.php';

echo "<h2>Route Test</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</p>";

$router->dispatch();
