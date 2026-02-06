<?php
// Test the actual events endpoint path
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate the actual request
$_SERVER['REQUEST_URI'] = '/tikiti-organizer-api/public/api/v1/events';
$_SERVER['SCRIPT_NAME'] = '/tikiti-organizer-api/public/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../bootstrap.php';
\Config::load();

use App\Router\Router;

$router = new Router();
require_once __DIR__ . '/../routes/api.php';

$debugInfo = $router->getDebugInfo();

echo "<h2>Events Endpoint Path Test</h2>";
echo "<pre>";
echo "REQUEST_URI: " . $debugInfo['request_uri'] . "\n";
echo "SCRIPT_NAME: " . $debugInfo['script_name'] . "\n";
echo "Base Path: " . $debugInfo['base_path'] . "\n";
echo "Processed Path: " . $debugInfo['processed_path'] . "\n\n";

echo "Looking for route: GET " . $debugInfo['processed_path'] . "\n\n";

$found = false;
foreach ($debugInfo['routes'] as $route) {
    if ($route['method'] === 'GET' && $route['path'] === $debugInfo['processed_path']) {
        echo "✓ EXACT MATCH FOUND: {$route['method']} {$route['path']}\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "✗ No exact match found\n";
    echo "\nTrying pattern matching...\n";
    
    // Test matchPath
    $reflection = new ReflectionClass($router);
    $matchMethod = $reflection->getMethod('matchPath');
    $matchMethod->setAccessible(true);
    
    foreach ($debugInfo['routes'] as $route) {
        if ($route['method'] === 'GET') {
            $params = [];
            $match = $matchMethod->invoke($router, $route['path'], $debugInfo['processed_path'], $params);
            if ($match) {
                echo "✓ PATTERN MATCH: {$route['method']} {$route['path']}\n";
                if (!empty($params)) {
                    echo "  Params: " . json_encode($params) . "\n";
                }
                $found = true;
            }
        }
    }
}

if (!$found) {
    echo "\n✗ NO MATCH FOUND\n";
    echo "\nAvailable GET routes:\n";
    foreach ($debugInfo['routes'] as $route) {
        if ($route['method'] === 'GET') {
            echo "  - {$route['path']}\n";
        }
    }
}

echo "</pre>";
