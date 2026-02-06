<?php

namespace App\Base;

use App\Services\EncryptionService;
use App\Database\ConnectionPool;
use App\Utils\HttpStatus;

abstract class BaseController {
    protected $encryptionService;
    protected $dbPool;
    protected $organizerId;
    
    public function __construct() {
        $this->encryptionService = new EncryptionService();
        $this->dbPool = ConnectionPool::getInstance();
        $this->organizerId = $this->getOrganizerId();
    }
    
    /**
     * Get organizer ID from request (encrypted URL parameter or header)
     * Priority: URL parameter > Header
     * @return int|null
     */
    protected function getOrganizerId() {
        // First try to get from URL parameter (for caching)
        // URL parameter comes from route matching (e.g., /organizers/{organizer_id}/events)
        $organizerIdParam = $this->getParam('organizer_id');
        if ($organizerIdParam) {
            // Try URL-safe decoding first (base64url)
            try {
                $decoded = \App\Utils\UrlSafeEncoder::decodeOrganizerId($organizerIdParam);
                $decrypted = \App\Utils\OrganizerHelper::decryptOrganizerId($decoded);
                if ($decrypted !== null && $decrypted > 0) {
                    return $decrypted;
                }
            } catch (\Exception $e) {
                // If URL-safe decoding fails, try regular URL decode
            }
            
            // Fallback to regular URL decode
            $organizerIdParam = urldecode($organizerIdParam);
            $decrypted = \App\Utils\OrganizerHelper::decryptOrganizerId($organizerIdParam);
            if ($decrypted !== null && $decrypted > 0) {
                return $decrypted;
            }
        }
        
        // Fallback to header (for backward compatibility)
        return \App\Utils\OrganizerHelper::getOrganizerIdFromRequest();
    }
    
    /**
     * Require organizer ID (throws error if missing)
     * @return int
     */
    protected function requireOrganizerId() {
        $organizerId = $this->getOrganizerId();
        
        if ($organizerId === null || $organizerId <= 0) {
            $this->sendError(
                'Organizer ID is required. Please provide encrypted organizer ID in URL parameter (organizer_id) or X-ORGANIZER-ID header',
                HttpStatus::BAD_REQUEST,
                [],
                'ORGANIZER_ID_REQUIRED'
            );
        }
        
        return $organizerId;
    }
    
    /**
     * Send encrypted JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    protected function sendResponse($data, $statusCode = HttpStatus::OK) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $encryptedResponse = $this->encryptionService->encryptResponse($data);
        echo json_encode($encryptedResponse);
        exit;
    }
    
    /**
     * Send unencrypted JSON response (for special cases like decrypt endpoint)
     * @param mixed $data
     * @param int $statusCode
     */
    protected function sendUnencryptedResponse($data, $statusCode = HttpStatus::OK) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => time()
        ]);
        exit;
    }
    
    /**
     * Send error response
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @param string|null $code Error code for client reference
     */
    protected function sendError($message, $statusCode = HttpStatus::BAD_REQUEST, $errors = [], $code = null) {
        $errorData = [
            'error' => $message,
            'code' => $code ?? HttpStatus::getMessage($statusCode),
            'status_code' => $statusCode
        ];
        
        if (!empty($errors)) {
            $errorData['errors'] = $errors;
        }
        
        $this->sendResponse($errorData, $statusCode);
    }
    
    /**
     * Send success response
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     */
    protected function sendSuccess($data, $message = null, $statusCode = HttpStatus::OK) {
        $response = ['data' => $data];
        if ($message) {
            $response['message'] = $message;
        }
        $this->sendResponse($response, $statusCode);
    }
    
    /**
     * Send validation error response (422)
     * @param string $message
     * @param array $errors
     */
    protected function sendValidationError($message = 'Validation failed', $errors = []) {
        $this->sendError($message, HttpStatus::UNPROCESSABLE_ENTITY, $errors, 'VALIDATION_ERROR');
    }
    
    /**
     * Send not found error response (404)
     * @param string $message
     * @param string|null $resource
     */
    protected function sendNotFound($message = 'Resource not found', $resource = null) {
        $code = $resource ? strtoupper($resource) . '_NOT_FOUND' : 'NOT_FOUND';
        $this->sendError($message, HttpStatus::NOT_FOUND, [], $code);
    }
    
    /**
     * Send unauthorized error response (401)
     * @param string $message
     */
    protected function sendUnauthorized($message = 'Unauthorized') {
        $this->sendError($message, HttpStatus::UNAUTHORIZED, [], 'UNAUTHORIZED');
    }
    
    /**
     * Send forbidden error response (403)
     * @param string $message
     */
    protected function sendForbidden($message = 'Forbidden') {
        $this->sendError($message, HttpStatus::FORBIDDEN, [], 'FORBIDDEN');
    }
    
    /**
     * Send conflict error response (409)
     * @param string $message
     */
    protected function sendConflict($message = 'Resource conflict') {
        $this->sendError($message, HttpStatus::CONFLICT, [], 'CONFLICT');
    }
    
    /**
     * Send internal server error response (500)
     * @param string $message
     */
    protected function sendServerError($message = 'Internal server error') {
        $this->sendError($message, HttpStatus::INTERNAL_SERVER_ERROR, [], 'INTERNAL_SERVER_ERROR');
    }
    
    /**
     * Get request body as JSON
     * @return array
     */
    protected function getRequestBody() {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        return $data ?? [];
    }
    
    /**
     * Get request parameter
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getParam($key, $default = null) {
        return $_GET[$key] ?? $_POST[$key] ?? $default;
    }
}
