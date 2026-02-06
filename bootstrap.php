<?php

// Simple autoloader for non-composer classes
spl_autoload_register(function ($class) {
    // Handle Config class
    if ($class === 'Config') {
        require_once __DIR__ . '/config/config.php';
        return;
    }
    
    // Handle App namespace
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
