#!/usr/bin/env php
<?php
/**
 * Generate API Token
 * Usage: php generate-token.php [length]
 */

$length = isset($argv[1]) ? (int)$argv[1] : 32;
$token = bin2hex(random_bytes($length));

echo "Generated API Token:\n";
echo $token . "\n\n";
echo "Add this to your .env file:\n";
echo "API_TOKEN={$token}\n";
