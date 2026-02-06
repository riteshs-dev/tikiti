<?php

namespace App\Middleware;

class AuthMiddleware {
    /**
     * Handle authentication
     * @param array $options Optional configuration with 'path' key
     * @return void
     */
    public static function handle($options = []) {
        $path = $options['path'] ?? '';
        
        // Get API token from config
        $requiredToken = \Config::get('api.token');
        
        // If no token is configured, skip authentication
        if (empty($requiredToken)) {
            return;
        }
        
        // Check if this route should bypass authentication
        $bypassRoutes = \Config::get('api.auth_bypass_routes') ?? [];
        if (self::shouldBypass($path, $bypassRoutes)) {
            return;
        }
        
        // Get token from header (check multiple possible header names)
        $token = self::getTokenFromRequest();
        
        // Validate token
        if (empty($token)) {
            self::sendUnauthorized();
        }
        
        // First check against static config token (for backward compatibility)
        if ($token === $requiredToken) {
            return; // Valid static token
        }
        
        // If not static token, check database tokens
        try {
            $tokenModel = new \App\Models\TokenModel();
            $tokenRecord = $tokenModel->findByAccessToken($token);
            
            if (!$tokenRecord) {
                self::sendUnauthorized();
            }
            // Token is valid, continue
        } catch (\Exception $e) {
            // If database check fails and it's not the static token, reject
            if ($token !== $requiredToken) {
                error_log("Token validation error: " . $e->getMessage());
                self::sendUnauthorized();
            }
        }
    }
    
    /**
     * Get token from request headers
     * @return string|null
     */
    private static function getTokenFromRequest() {
        // Check various header names (case-insensitive)
        $headers = [
            'X-API-TOKEN',
            'X-API-KEY',
            'Authorization',
            'API-TOKEN',
            'API-KEY'
        ];
        
        // Get all headers
        $allHeaders = [];
        if (function_exists('getallheaders')) {
            $allHeaders = getallheaders();
        } else {
            // Fallback for environments where getallheaders() is not available
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerName = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $allHeaders[$headerName] = $value;
                }
            }
        }
        
        // Normalize headers to lowercase keys for comparison
        $normalizedHeaders = [];
        foreach ($allHeaders as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }
        
        // Check each possible header name
        foreach ($headers as $headerName) {
            $lowerHeader = strtolower($headerName);
            
            // Direct match
            if (isset($normalizedHeaders[$lowerHeader])) {
                $token = $normalizedHeaders[$lowerHeader];
                // Handle "Bearer token" format
                if (stripos($token, 'Bearer ') === 0) {
                    return substr($token, 7);
                }
                return $token;
            }
            
            // Check with HTTP_ prefix (for $_SERVER)
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
            if (isset($_SERVER[$serverKey])) {
                $token = $_SERVER[$serverKey];
                if (stripos($token, 'Bearer ') === 0) {
                    return substr($token, 7);
                }
                return $token;
            }
        }
        
        return null;
    }
    
    /**
     * Send unauthorized response
     * @return void
     */
    private static function sendUnauthorized() {
        http_response_code(\App\Utils\HttpStatus::UNAUTHORIZED);
        header('Content-Type: application/json');
        
        // Encrypt the error response
        try {
            $encryptionService = new \App\Services\EncryptionService();
            $errorData = [
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API token',
                'code' => 'UNAUTHORIZED',
                'status_code' => \App\Utils\HttpStatus::UNAUTHORIZED
            ];
            $encryptedResponse = $encryptionService->encryptResponse($errorData);
            echo json_encode($encryptedResponse);
        } catch (\Exception $e) {
            // Fallback if encryption fails
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API token',
                'code' => 'UNAUTHORIZED',
                'status_code' => \App\Utils\HttpStatus::UNAUTHORIZED
            ]);
        }
        exit;
    }
    
    /**
     * Check if route should bypass authentication
     * @param string $path
     * @param array $bypassRoutes
     * @return bool
     */
    public static function shouldBypass($path, $bypassRoutes = []) {
        $defaultBypass = ['/health', '/api/v1/health'];
        
        // Always bypass auth routes
        $authBypass = [
            '/api/v1/auth/token',
            '/api/v1/auth/refresh',
            '/api/v1/auth/organizer-id',
            '/api/v1/auth/decrypt',
            '/api/v1/organizers/login' 
        ];
        
        $allBypass = array_merge($defaultBypass, $authBypass, $bypassRoutes);
        
        foreach ($allBypass as $bypassPath) {
            if ($path === $bypassPath || strpos($path, $bypassPath) === 0) {
                return true;
            }
        }
        
        return false;
    }
}
