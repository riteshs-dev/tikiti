<?php

use App\Router\Router;
use App\Middleware\CorsMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\HealthController;
use App\Controllers\ExampleController;
use App\Controllers\EventController;
use App\Controllers\AuthController;
use App\Controllers\OrganizerController;

// Ensure classes are loaded
if (!class_exists(EventController::class)) {
    throw new Exception('EventController class not found');
}

$router = new Router();

// Apply CORS middleware globally
$router->middleware(function($path = null) {
    CorsMiddleware::handle();
});

// Apply Authentication middleware globally (with path context)
$router->middleware(function($path = null) {
    AuthMiddleware::handle(['path' => $path ?? '']);
});

// Health check
$router->get('/health', HealthController::class . '@check');

// API routes
$apiVersion = \Config::get('api.version');
$apiPrefix = "/api/{$apiVersion}";

// Authentication routes (no auth required for these)
$router->post("{$apiPrefix}/auth/token", AuthController::class . '@generateToken');
$router->post("{$apiPrefix}/auth/refresh", AuthController::class . '@refreshToken');
$router->post("{$apiPrefix}/auth/organizer-id", AuthController::class . '@getEncryptedOrganizerId');
$router->post("{$apiPrefix}/auth/decrypt", AuthController::class . '@decrypt'); // Add this line

// Example routes
$router->get("{$apiPrefix}/example", ExampleController::class . '@index');
$router->get("{$apiPrefix}/example/{id}", ExampleController::class . '@show');
$router->post("{$apiPrefix}/example", ExampleController::class . '@create');

// Event routes (with organizer_id in URL for caching)
$router->get("{$apiPrefix}/organizers/{organizer_id}/events", EventController::class . '@index');
$router->get("{$apiPrefix}/organizers/{organizer_id}/events/{id}", EventController::class . '@show');
$router->post("{$apiPrefix}/organizers/{organizer_id}/events", EventController::class . '@create');
$router->put("{$apiPrefix}/organizers/{organizer_id}/events/{id}", EventController::class . '@update');
$router->delete("{$apiPrefix}/organizers/{organizer_id}/events/{id}", EventController::class . '@delete');
$router->get("{$apiPrefix}/organizers/{organizer_id}/events/status/{status}", EventController::class . '@getByStatus');

// Organizer CRUD routes (organizers table only)
$router->get("{$apiPrefix}/organizers", OrganizerController::class . '@index');
$router->get("{$apiPrefix}/organizers/{id}", OrganizerController::class . '@show');
$router->post("{$apiPrefix}/organizers", OrganizerController::class . '@create');
$router->put("{$apiPrefix}/organizers/{id}", OrganizerController::class . '@update');
$router->delete("{$apiPrefix}/organizers/{id}", OrganizerController::class . '@delete');
$router->post("{$apiPrefix}/organizers/login", OrganizerController::class . '@login');

// Add your routes here
