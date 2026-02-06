<?php

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Composer autoloader (if available)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load configuration
\Config::load();

// Load routes
require_once __DIR__ . '/../routes/api.php';

// Dispatch
$router->dispatch();
