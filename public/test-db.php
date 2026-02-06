<?php
require_once __DIR__ . '/../bootstrap.php';
\Config::load();

try {
    $dbPool = \App\Database\ConnectionPool::getInstance();
    $dbPool->execute(function($db) {
        $db->query('SELECT 1');
    });
    echo "Database connection: OK";
} catch (Exception $e) {
    echo "Database ERROR: " . $e->getMessage();
}