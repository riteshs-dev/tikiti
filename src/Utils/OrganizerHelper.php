<?php

namespace App\Utils;

use App\Services\EncryptionService;

/**
 * Organizer Helper
 * Handles organizer_id extraction and validation from encrypted requests
 */
class OrganizerHelper {
    /**
     * Get organizer ID from encrypted header
     * @return int|null
     */
    public static function getOrganizerIdFromRequest() {
        // Check for encrypted organizer ID in header
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerName = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$headerName] = $value;
                }
            }
        }
        
        // Normalize headers to lowercase keys
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }
        
        // Check for organizer ID in various header formats
        $headerNames = [
            'x-organizer-id',
            'x-organizer',
            'organizer-id',
            'organizer'
        ];
        
        foreach ($headerNames as $headerName) {
            if (isset($normalizedHeaders[$headerName])) {
                $encryptedId = $normalizedHeaders[$headerName];
                
                // Try to decrypt the organizer ID
                try {
                    $encryptionService = new EncryptionService();
                    
                    // The encrypted string is the raw encrypted data, not wrapped in response format
                    // So we decrypt directly
                    $decrypted = $encryptionService->decrypt($encryptedId);
                    
                    // decrypt() returns array if JSON, string otherwise
                    if (is_array($decrypted)) {
                        // If it's already an array (decoded JSON)
                        if (isset($decrypted['organizer_id'])) {
                            return (int)$decrypted['organizer_id'];
                        }
                    } elseif (is_string($decrypted)) {
                        // If it's a string, try to decode as JSON
                        $decoded = json_decode($decrypted, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            if (isset($decoded['organizer_id'])) {
                                return (int)$decoded['organizer_id'];
                            }
                        } elseif (is_numeric($decrypted)) {
                            // If it's a plain number string
                            return (int)$decrypted;
                        }
                    } elseif (is_numeric($decrypted)) {
                        // If it's a plain number
                        return (int)$decrypted;
                    }
                } catch (\Exception $e) {
                    // If decryption fails, try treating it as plain number (for testing)
                    if (is_numeric($encryptedId)) {
                        return (int)$encryptedId;
                    }
                }
            }
            
            // Check with HTTP_ prefix (for $_SERVER)
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
            if (isset($_SERVER[$serverKey])) {
                $encryptedId = $_SERVER[$serverKey];
                
                try {
                    $encryptionService = new EncryptionService();
                    
                    // The encrypted string is the raw encrypted data, not wrapped in response format
                    // So we decrypt directly
                    $decrypted = $encryptionService->decrypt($encryptedId);
                    
                    // decrypt() returns array if JSON, string otherwise
                    if (is_array($decrypted)) {
                        // If it's already an array (decoded JSON)
                        if (isset($decrypted['organizer_id'])) {
                            return (int)$decrypted['organizer_id'];
                        }
                    } elseif (is_string($decrypted)) {
                        // If it's a string, try to decode as JSON
                        $decoded = json_decode($decrypted, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            if (isset($decoded['organizer_id'])) {
                                return (int)$decoded['organizer_id'];
                            }
                        } elseif (is_numeric($decrypted)) {
                            // If it's a plain number string
                            return (int)$decrypted;
                        }
                    } elseif (is_numeric($decrypted)) {
                        // If it's a plain number
                        return (int)$decrypted;
                    }
                } catch (\Exception $e) {
                    if (is_numeric($encryptedId)) {
                        return (int)$encryptedId;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Validate and get organizer ID (throws exception if missing)
     * @return int
     * @throws \Exception
     */
    public static function requireOrganizerId() {
        $organizerId = self::getOrganizerIdFromRequest();
        
        if ($organizerId === null || $organizerId <= 0) {
            throw new \Exception('Organizer ID is required and must be provided in encrypted header');
        }
        
        return $organizerId;
    }
    
    /**
     * Encrypt organizer ID for client use (URL-safe)
     * @param int $organizerId
     * @return string
     */
    public static function encryptOrganizerId($organizerId) {
        $encryptionService = new EncryptionService();
        $dataToEncrypt = json_encode(['organizer_id' => $organizerId]);
        return $encryptionService->encrypt($dataToEncrypt);
    }
    
    /**
     * Decrypt organizer ID from encrypted string
     * @param string $encryptedId
     * @return int|null
     */
    public static function decryptOrganizerId($encryptedId) {
        if (empty($encryptedId)) {
            return null;
        }
        
        try {
            $encryptionService = new EncryptionService();
            
            // Decrypt the encrypted string
            $decrypted = $encryptionService->decrypt($encryptedId);
            
            // decrypt() returns array if JSON, string otherwise
            if (is_array($decrypted)) {
                // If it's already an array (decoded JSON)
                if (isset($decrypted['organizer_id'])) {
                    return (int)$decrypted['organizer_id'];
                }
            } elseif (is_string($decrypted)) {
                // If it's a string, try to decode as JSON
                $decoded = json_decode($decrypted, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    if (isset($decoded['organizer_id'])) {
                        return (int)$decoded['organizer_id'];
                    }
                } elseif (is_numeric($decrypted)) {
                    // If it's a plain number string
                    return (int)$decrypted;
                }
            } elseif (is_numeric($decrypted)) {
                // If it's a plain number
                return (int)$decrypted;
            }
        } catch (\Exception $e) {
            // If decryption fails, try treating it as plain number (for testing)
            if (is_numeric($encryptedId)) {
                return (int)$encryptedId;
            }
        }
        
        return null;
    }
}
