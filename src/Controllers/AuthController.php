<?php

namespace App\Controllers;

use App\Base\BaseController;
use App\Models\TokenModel;
use App\Utils\AuthHelper;
use App\Utils\HttpStatus;
use App\Utils\OrganizerHelper;
use App\Utils\UrlSafeEncoder;
use Exception;

class AuthController extends BaseController {
    private $tokenModel;
    
    public function __construct() {
        parent::__construct();
        $this->tokenModel = new TokenModel();
    }
    
    /**
     * Generate access and refresh tokens
     * POST /api/v1/auth/token
     * 
     * Request Body:
     * {
     *   "organizer_id": 123,
     *   "password": "optional_password" // If you have password authentication
     * }
     */
    public function generateToken() {
        try {
            $body = $this->getRequestBody();
            
            // Validate required fields
            $required = ['organizer_id'];
            foreach ($required as $field) {
                if (empty($body[$field])) {
                    return $this->sendValidationError("Field '{$field}' is required");
                }
            }
            
            $organizerId = (int)$body['organizer_id'];
            
            // Validate organizer_id is positive
            if ($organizerId <= 0) {
                return $this->sendValidationError("Invalid organizer_id");
            }
            
            // TODO: Add password validation here if you have organizer authentication
            // For now, we'll just validate that organizer_id exists or is valid
            
            // Generate tokens
            $accessToken = AuthHelper::generateToken(32); // 64 hex characters
            $refreshToken = AuthHelper::generateToken(32); // 64 hex characters
            
            // Token expiry: Access token = 1 hour, Refresh token = 30 days
            $accessTokenExpiry = 3600; // 1 hour
            $refreshTokenExpiry = 2592000; // 30 days
            
            // Store tokens in database
            $tokenRecord = $this->tokenModel->createToken(
                $organizerId,
                $accessToken,
                $refreshToken,
                $accessTokenExpiry,
                $refreshTokenExpiry
            );
            
            if (!$tokenRecord) {
                return $this->sendServerError("Failed to create token");
            }
            
            // Encrypt organizer ID for client use
            $encryptedOrganizerId = OrganizerHelper::encryptOrganizerId($organizerId);
            $urlSafeOrganizerId = UrlSafeEncoder::encodeOrganizerId($encryptedOrganizerId);
            
            // Return tokens and encrypted organizer ID
            return $this->sendResponse([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $accessTokenExpiry,
                'refresh_expires_in' => $refreshTokenExpiry,
                'organizer_id' => $organizerId,
                'encrypted_organizer_id' => $encryptedOrganizerId,
                'url_safe_organizer_id' => $urlSafeOrganizerId,
                'expires_at' => $tokenRecord['expires_at'],
                'refresh_expires_at' => $tokenRecord['refresh_expires_at']
            ], HttpStatus::CREATED);
            
        } catch (Exception $e) {
            error_log("Token generation error: " . $e->getMessage());
            return $this->sendServerError("Failed to generate token: " . $e->getMessage());
        }
    }
    
    /**
     * Refresh access token using refresh token
     * POST /api/v1/auth/refresh
     * 
     * Request Body:
     * {
     *   "refresh_token": "refresh_token_here"
     * }
     */
    public function refreshToken() {
        try {
            $body = $this->getRequestBody();
            
            if (empty($body['refresh_token'])) {
                return $this->sendValidationError("Field 'refresh_token' is required");
            }
            
            $refreshToken = $body['refresh_token'];
            
            // Find token by refresh token
            $tokenRecord = $this->tokenModel->findByRefreshToken($refreshToken);
            
            if (!$tokenRecord) {
                return $this->sendUnauthorized("Invalid or expired refresh token");
            }
            
            $organizerId = (int)$tokenRecord['organizer_id'];
            
            // Generate new tokens
            $newAccessToken = AuthHelper::generateToken(32);
            $newRefreshToken = AuthHelper::generateToken(32);
            
            // Deactivate old token
            $this->tokenModel->deactivateToken($tokenRecord['access_token']);
            
            // Create new token pair
            $accessTokenExpiry = 3600; // 1 hour
            $refreshTokenExpiry = 2592000; // 30 days
            
            $newTokenRecord = $this->tokenModel->createToken(
                $organizerId,
                $newAccessToken,
                $newRefreshToken,
                $accessTokenExpiry,
                $refreshTokenExpiry
            );
            
            if (!$newTokenRecord) {
                return $this->sendServerError("Failed to refresh token");
            }
            
            // Encrypt organizer ID for client use
            $encryptedOrganizerId = OrganizerHelper::encryptOrganizerId($organizerId);
            $urlSafeOrganizerId = UrlSafeEncoder::encodeOrganizerId($encryptedOrganizerId);
            
            // Return new tokens
            return $this->sendResponse([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $accessTokenExpiry,
                'refresh_expires_in' => $refreshTokenExpiry,
                'organizer_id' => $organizerId,
                'encrypted_organizer_id' => $encryptedOrganizerId,
                'url_safe_organizer_id' => $urlSafeOrganizerId,
                'expires_at' => $newTokenRecord['expires_at'],
                'refresh_expires_at' => $newTokenRecord['refresh_expires_at']
            ], HttpStatus::OK);
            
        } catch (Exception $e) {
            error_log("Token refresh error: " . $e->getMessage());
            return $this->sendServerError("Failed to refresh token: " . $e->getMessage());
        }
    }
    
    /**
     * Get encrypted organizer ID
     * POST /api/v1/auth/organizer-id
     * 
     * Request Body:
     * {
     *   "organizer_id": 123
     * }
     * 
     * OR use access_token in header (if token is valid)
     */
    public function getEncryptedOrganizerId() {
        try {
            $body = $this->getRequestBody();
            
            // Try to get organizer_id from request body first
            $organizerId = null;
            if (!empty($body['organizer_id'])) {
                $organizerId = (int)$body['organizer_id'];
            } else {
                // Try to get from access token if provided
                $accessToken = $this->getAccessTokenFromRequest();
                if ($accessToken) {
                    $tokenRecord = $this->tokenModel->findByAccessToken($accessToken);
                    if ($tokenRecord) {
                        $organizerId = (int)$tokenRecord['organizer_id'];
                    }
                }
            }
            
            if (!$organizerId || $organizerId <= 0) {
                return $this->sendValidationError("organizer_id is required or provide valid access_token");
            }
            
            // Encrypt organizer ID
            $encryptedOrganizerId = OrganizerHelper::encryptOrganizerId($organizerId);
            $urlSafeOrganizerId = UrlSafeEncoder::encodeOrganizerId($encryptedOrganizerId);
            
            return $this->sendResponse([
                'organizer_id' => $organizerId,
                'encrypted_organizer_id' => $encryptedOrganizerId,
                'url_safe_organizer_id' => $urlSafeOrganizerId,
                'usage' => [
                    'header' => 'X-ORGANIZER-ID: ' . $encryptedOrganizerId,
                    'url' => '/api/v1/organizers/' . $urlSafeOrganizerId . '/events'
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Encrypt organizer ID error: " . $e->getMessage());
            return $this->sendServerError("Failed to encrypt organizer ID: " . $e->getMessage());
        }
    }
    
    /**
     * Get access token from request headers
     * @return string|null
     */
    private function getAccessTokenFromRequest() {
        $headers = [
            'X-API-TOKEN',
            'X-API-KEY',
            'Authorization',
            'API-TOKEN',
            'API-KEY'
        ];
        
        $allHeaders = [];
        if (function_exists('getallheaders')) {
            $allHeaders = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerName = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $allHeaders[$headerName] = $value;
                }
            }
        }
        
        $normalizedHeaders = [];
        foreach ($allHeaders as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }
        
        foreach ($headers as $headerName) {
            $lowerHeader = strtolower($headerName);
            if (isset($normalizedHeaders[$lowerHeader])) {
                $token = $normalizedHeaders[$lowerHeader];
                if (stripos($token, 'Bearer ') === 0) {
                    return substr($token, 7);
                }
                return $token;
            }
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

    // ... existing code ...

    /**
     * Decrypt encrypted data
     * POST /api/v1/auth/decrypt
     * 
     * Request Body:
     * {
     *   "encrypted_data": "encrypted_string_here"
     * }
     */
    public function decrypt() {
        try {
            $body = $this->getRequestBody();
            
            if (empty($body['encrypted_data'])) {
                return $this->sendValidationError("Field 'encrypted_data' is required");
            }
            
            $encryptedData = $body['encrypted_data'];
            
            // Decrypt the data
            $decrypted = $this->encryptionService->decrypt($encryptedData);
            
            // Send unencrypted response (since this is a decrypt endpoint)
            return $this->sendUnencryptedResponse($decrypted);
            
        } catch (Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return $this->sendServerError("Failed to decrypt data: " . $e->getMessage());
        }
    }

    public function decryptold() {
        try {
            $body = $this->getRequestBody();
            
            if (empty($body['encrypted_data'])) {
                return $this->sendValidationError("Field 'encrypted_data' is required");
            }
            
            $encryptedData = $body['encrypted_data'];
            
            // Decrypt the data
            $decrypted = $this->encryptionService->decrypt($encryptedData);
            
            return $this->sendResponse([
                'decrypted_data' => $decrypted
            ]);
            
        } catch (Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return $this->sendServerError("Failed to decrypt data: " . $e->getMessage());
        }
    }
}
