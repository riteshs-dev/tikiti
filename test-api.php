<?php
// Quick API test script
// Access this via: http://localhost/tikiti-organizer-api/test-api.php

require_once __DIR__ . '/bootstrap.php';
\Config::load();

use App\Models\EventModel;

echo "<h2>API Test</h2>";
echo "<p>Testing database connection and event fetching...</p>";

try {
    $model = new EventModel();
    $events = $model->getAllEvents([], ['created_at' => 'DESC'], 5);
    
    echo "<h3>✓ Database Connection: Success</h3>";
    echo "<p>Found " . count($events) . " events</p>";
    
    if (!empty($events)) {
        echo "<h4>Sample Event:</h4>";
        echo "<pre>";
        print_r($events[0]);
        echo "</pre>";
    }
    
    echo "<hr>";
    echo "<h3>API Endpoints:</h3>";
    echo "<ul>";
    echo "<li><a href='/tikiti-organizer-api/public/api/v1/events'>GET /api/v1/events</a></li>";
    echo "<li><a href='/tikiti-organizer-api/public/health'>GET /health</a></li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>Using PHP Built-in Server:</h3>";
    echo "<p>Run: <code>php -S localhost:8000 -t public</code></p>";
    echo "<p>Then access: <a href='http://localhost:8000/api/v1/events'>http://localhost:8000/api/v1/events</a></p>";
    
} catch (Exception $e) {
    echo "<h3>✗ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
