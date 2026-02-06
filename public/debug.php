<?php
// Debug endpoint - Remove in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../bootstrap.php';
\Config::load();

use App\Router\Router;

echo "<h2>Router Debug</h2>";
echo "<pre>";

echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n\n";

// Load routes
require_once __DIR__ . '/../routes/api.php';

if (!isset($router)) {
    echo "ERROR: Router not defined!\n";
    exit;
}

$debugInfo = $router->getDebugInfo();

echo "Router class: " . get_class($router) . "\n";
echo "Routes count: " . $debugInfo['routes_count'] . "\n\n";

echo "Registered Routes:\n";
foreach ($debugInfo['routes'] as $i => $route) {
    echo "  [$i] {$route['method']} {$route['path']}\n";
}

echo "\nPath Processing:\n";
echo "  Original REQUEST_URI: " . $debugInfo['request_uri'] . "\n";
echo "  SCRIPT_NAME: " . $debugInfo['script_name'] . "\n";
echo "  Base Path: " . $debugInfo['base_path'] . "\n";
echo "  Processed Path: " . $debugInfo['processed_path'] . "\n\n";

echo "Testing route match for: $method $path\n";
foreach ($router->routes as $route) {
    if ($route['method'] === $method) {
        $params = [];
        // Use reflection to call private method for testing
        $reflection = new ReflectionClass($router);
        $methodRef = $reflection->getMethod('matchPath');
        $methodRef->setAccessible(true);
        $match = $methodRef->invoke($router, $route['path'], $path, $params);
        if ($match) {
            echo "  ✓ MATCH: {$route['method']} {$route['path']}\n";
            if (!empty($params)) {
                echo "    Params: " . json_encode($params) . "\n";
            }
        }
    }
}

echo "</pre>";
echo "<hr>";
echo "<p><a href='/tikiti-organizer-api/public/api/v1/events'>Test Events Endpoint</a></p>";
