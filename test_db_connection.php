<?php

// Test database connection
require_once __DIR__ . '/bootstrap.php';
\Config::load();

use App\Database\ConnectionPool;

try {
    echo "Testing database connection...\n";
    echo "Host: " . \Config::get('db.host') . "\n";
    echo "Port: " . \Config::get('db.port') . "\n";
    echo "Database: " . \Config::get('db.name') . "\n";
    echo "User: " . \Config::get('db.user') . "\n";
    echo "\n";
    
    $pool = ConnectionPool::getInstance();
    
    $pool->execute(function($db) {
        $stmt = $db->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "✓ Connection successful!\n";
        echo "PostgreSQL Version: " . $version . "\n";
        
        // Test query
        $stmt = $db->query("SELECT current_database(), current_user");
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Current Database: " . $info['current_database'] . "\n";
        echo "Current User: " . $info['current_user'] . "\n";
    });
    
    $stats = $pool->getStats();
    echo "\nConnection Pool Stats:\n";
    echo "Pool Size: " . $stats['pool_size'] . "\n";
    echo "Active Connections: " . $stats['active_connections'] . "\n";
    echo "Max Connections: " . $stats['max_connections'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
