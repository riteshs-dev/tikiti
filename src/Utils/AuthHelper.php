<?php

namespace App\Utils;

/**
 * Authentication Helper
 * Utility methods for authentication-related operations
 */
class AuthHelper {
    /**
     * Generate a secure API token
     * @param int $length Token length in bytes (default: 32 bytes = 64 hex characters)
     * @return string
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate token format (basic validation)
     * @param string $token
     * @return bool
     */
    public static function isValidTokenFormat($token) {
        // Token should be at least 16 characters
        return strlen($token) >= 16 && ctype_xdigit($token);
    }
    
    /**
     * Get token from request (helper method)
     * @return string|null
     */
    public static function getTokenFromRequest() {
        return \App\Middleware\AuthMiddleware::getTokenFromRequest();
    }
}
