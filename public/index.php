<?php

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/php-errors.log');

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
