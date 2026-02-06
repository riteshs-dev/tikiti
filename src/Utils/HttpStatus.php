<?php

namespace App\Utils;

/**
 * HTTP Status Code Constants
 * Standard HTTP status codes for API responses
 */
class HttpStatus {
    // Success (2xx)
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;
    
    // Client Errors (4xx)
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const CONFLICT = 409;
    const UNPROCESSABLE_ENTITY = 422;
    const TOO_MANY_REQUESTS = 429;
    
    // Server Errors (5xx)
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    
    /**
     * Get status code message
     * @param int $code
     * @return string
     */
    public static function getMessage($code) {
        $messages = [
            // Success
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            204 => 'No Content',
            
            // Client Errors
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            
            // Server Errors
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];
        
        return $messages[$code] ?? 'Unknown Status';
    }
    
    /**
     * Check if status code is success (2xx)
     * @param int $code
     * @return bool
     */
    public static function isSuccess($code) {
        return $code >= 200 && $code < 300;
    }
    
    /**
     * Check if status code is client error (4xx)
     * @param int $code
     * @return bool
     */
    public static function isClientError($code) {
        return $code >= 400 && $code < 500;
    }
    
    /**
     * Check if status code is server error (5xx)
     * @param int $code
     * @return bool
     */
    public static function isServerError($code) {
        return $code >= 500 && $code < 600;
    }
}
