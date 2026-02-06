<?php
// Simple test - directly test the route
$_SERVER['REQUEST_URI'] = '/tikiti-organizer-api/public/api/v1/events';
$_SERVER['SCRIPT_NAME'] = '/tikiti-organizer-api/public/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../bootstrap.php';
\Config::load();

use App\Router\Router;
use App\Controllers\EventController;

$router = new Router();

// Register route directly
$router->get('/api/v1/events', [EventController::class . '@index']);

echo "Testing direct route registration...\n";
echo "Path: /api/v1/events\n";
echo "Handler: " . EventController::class . '@index' . "\n\n";

$router->dispatch();
