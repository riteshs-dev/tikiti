<?php

namespace App\Middleware;

class CorsMiddleware {
    public static function handle() {
        $allowedOrigins = \Config::get('cors.allowed_origins');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else if (!empty($allowedOrigins)) {
            header("Access-Control-Allow-Origin: " . $allowedOrigins[0]);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-TOKEN, X-API-KEY, API-TOKEN, API-KEY');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
