<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test bootstrap
$bootstrapPath = __DIR__ . '/../bootstrap.php';
echo "Bootstrap exists: " . (file_exists($bootstrapPath) ? 'YES' : 'NO') . "<br>";

try {
    require_once $bootstrapPath;
    echo "Bootstrap loaded OK<br>";
} catch (Exception $e) {
    echo "Bootstrap ERROR: " . $e->getMessage() . "<br>";
}

// Test config
try {
    \Config::load();
    echo "Config loaded OK<br>";
} catch (Exception $e) {
    echo "Config ERROR: " . $e->getMessage() . "<br>";
}

// Test database connection
try {
    $dbPool = \App\Database\ConnectionPool::getInstance();
    echo "Database pool created OK<br>";
} catch (Exception $e) {
    echo "Database ERROR: " . $e->getMessage() . "<br>";
}