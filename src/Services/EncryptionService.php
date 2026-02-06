<?php

namespace App\Services;

use Exception;

class EncryptionService {
    private $key;
    private $method;
    
    public function __construct() {
        $config = \Config::get('encryption');
        $this->key = $config['key'];
        $this->method = $config['method'];
        
        if (empty($this->key)) {
            throw new Exception("Encryption key is not configured");
        }
        
        // Ensure key is 32 bytes for AES-256
        if (strlen($this->key) < 32) {
            $this->key = str_pad($this->key, 32, '0');
        } else {
            $this->key = substr($this->key, 0, 32);
        }
    }
    
    /**
     * Encrypt data
     * @param mixed $data
     * @return string
     * @throws Exception
     */
    public function encrypt($data) {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        
        $ivLength = openssl_cipher_iv_length($this->method);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt($data, $this->method, $this->key, 0, $iv);
        
        if ($encrypted === false) {
            throw new Exception("Encryption failed");
        }
        
        // Combine IV and encrypted data
        $encryptedData = base64_encode($iv . $encrypted);
        
        return $encryptedData;
    }
    
    /**
     * Decrypt data
     * @param string $encryptedData
     * @return mixed
     * @throws Exception
     */
    public function decrypt($encryptedData) {
        $data = base64_decode($encryptedData);
        
        $ivLength = openssl_cipher_iv_length($this->method);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        $decrypted = openssl_decrypt($encrypted, $this->method, $this->key, 0, $iv);
        
        if ($decrypted === false) {
            throw new Exception("Decryption failed");
        }
        
        // Try to decode as JSON, return as string if not valid JSON
        $json = json_decode($decrypted, true);
        return json_last_error() === JSON_ERROR_NONE ? $json : $decrypted;
    }
    
    /**
     * Encrypt response data
     * @param mixed $data
     * @return array
     */
    public function encryptResponse($data) {
        try {
            $encrypted = $this->encrypt($data);
            return [
                'success' => true,
                'data' => $encrypted,
                'timestamp' => time()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Encryption failed',
                'timestamp' => time()
            ];
        }
    }
}
