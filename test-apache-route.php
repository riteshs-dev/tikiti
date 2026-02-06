<?php
// Simulate Apache request
$_SERVER['REQUEST_URI'] = '/tikiti-organizer-api/public/api/v1/events';
$_SERVER['SCRIPT_NAME'] = '/tikiti-organizer-api/public/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/bootstrap.php';
\Config::load();

use App\Router\Router;

$router = new Router();
$router->get('/api/v1/events', function() {
    return json_encode(['test' => 'success', 'path' => '/api/v1/events']);
});

echo "Testing route matching...\n";
$router->dispatch();
