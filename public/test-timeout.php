<?php
// Test database connection with timeout
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...<br>";

$host = '20.40.45.193';
$port = 5432;
$dbname = 'tikiti_db';
$user = 'tikiti';
$password = 'hafse%sean@123';

// Test 1: Check if host is reachable
echo "1. Testing host connectivity...<br>";
$start = microtime(true);
$connection = @fsockopen($host, $port, $errno, $errstr, 5);
$elapsed = microtime(true) - $start;

if ($connection) {
    echo "✓ Host is reachable (took " . round($elapsed, 2) . " seconds)<br>";
    fclose($connection);
} else {
    echo "✗ Cannot reach host: $errstr ($errno)<br>";
    echo "This is likely the cause of the 504 timeout!<br>";
    exit;
}

// Test 2: Try PDO connection with timeout
echo "<br>2. Testing PDO connection...<br>";
try {
    $start = microtime(true);
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $elapsed = microtime(true) - $start;
    echo "✓ PDO connection successful (took " . round($elapsed, 2) . " seconds)<br>";
    
    // Test query
    $stmt = $pdo->query('SELECT 1');
    echo "✓ Query executed successfully<br>";
} catch (PDOException $e) {
    echo "✗ PDO Error: " . $e->getMessage() . "<br>";
    echo "This is likely the cause of the 504 timeout!<br>";
}
