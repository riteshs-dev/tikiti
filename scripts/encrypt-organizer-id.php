#!/usr/bin/env php
<?php
/**
 * Encrypt Organizer ID
 * Usage: php encrypt-organizer-id.php <organizer_id>
 * 
 * This script encrypts an organizer ID for use in the X-ORGANIZER-ID header
 */

require_once __DIR__ . '/../bootstrap.php';

if ($argc < 2) {
    echo "Usage: php encrypt-organizer-id.php <organizer_id>\n";
    echo "Example: php encrypt-organizer-id.php 123\n";
    exit(1);
}

$organizerId = (int)$argv[1];

if ($organizerId <= 0) {
    echo "Error: Organizer ID must be a positive integer\n";
    exit(1);
}

try {
    $encryptionService = new \App\Services\EncryptionService();
    
    // Encrypt the organizer_id data directly (not wrapped in response format)
    $dataToEncrypt = json_encode(['organizer_id' => $organizerId]);
    $encrypted = $encryptionService->encrypt($dataToEncrypt);
    
    // Use URL-safe encoding (base64url) instead of urlencode
    // This avoids issues with / characters in base64
    $urlSafe = \App\Utils\UrlSafeEncoder::encodeOrganizerId($encrypted);
    
    echo "Organizer ID: {$organizerId}\n";
    echo "Encrypted (raw):\n";
    echo $encrypted . "\n\n";
    echo "URL-Safe Encoded (for use in URL - recommended):\n";
    echo $urlSafe . "\n\n";
    echo "URL Encoded (fallback - may have issues with / characters):\n";
    echo urlencode($encrypted) . "\n\n";
    echo "cURL Examples:\n\n";
    echo "# Using URL parameter with URL-safe encoding (recommended for caching):\n";
    echo "curl -H \"X-API-TOKEN: your_token\" \\\n";
    echo "     \"http://localhost/api/v1/organizers/{$urlSafe}/events\"\n\n";
    echo "# Using header (fallback):\n";
    echo "curl -H \"X-API-TOKEN: your_token\" \\\n";
    echo "     -H \"X-ORGANIZER-ID: {$encrypted}\" \\\n";
    echo "     \"http://localhost/api/v1/organizers/{$urlSafe}/events\"\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
