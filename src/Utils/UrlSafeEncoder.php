<?php

namespace App\Utils;

/**
 * URL-Safe Encoding Helper
 * Provides URL-safe encoding for encrypted organizer IDs
 */
class UrlSafeEncoder {
    /**
     * Encode for URL (base64url encoding - URL-safe base64)
     * @param string $data
     * @return string
     */
    public static function encode($data) {
        // Use base64url encoding (URL-safe base64)
        // Replace + with -, / with _, and remove = padding
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decode from URL (base64url decoding)
     * @param string $encoded
     * @return string
     */
    public static function decode($encoded) {
        // Convert base64url back to base64
        $data = strtr($encoded, '-_', '+/');
        // Add padding if needed
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= str_repeat('=', 4 - $mod4);
        }
        return base64_decode($data);
    }
    
    /**
     * Encode encrypted organizer ID for URL
     * @param string $encryptedId
     * @return string
     */
    public static function encodeOrganizerId($encryptedId) {
        // The encrypted ID is already base64, so we need to make it URL-safe
        return rtrim(strtr($encryptedId, '+/', '-_'), '=');
    }
    
    /**
     * Decode organizer ID from URL
     * @param string $encodedId
     * @return string
     */
    public static function decodeOrganizerId($encodedId) {
        // Convert base64url back to base64
        $data = strtr($encodedId, '-_', '+/');
        // Add padding if needed
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= str_repeat('=', 4 - $mod4);
        }
        return $data;
    }
}
