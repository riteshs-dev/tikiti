<?php

namespace App\Models;

use App\Base\BaseModel;
use PDO;
use Exception;

class TokenModel extends BaseModel {
    protected $table = 'api_tokens';
    
    /**
     * Create a new token pair
     * @param int $organizerId
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $accessTokenExpiry Seconds until access token expires (default: 1 hour)
     * @param int $refreshTokenExpiry Seconds until refresh token expires (default: 30 days)
     * @return array|false
     */
    public function createToken($organizerId, $accessToken, $refreshToken, $accessTokenExpiry = 3600, $refreshTokenExpiry = 2592000) {
        return $this->dbPool->execute(function(PDO $db) use ($organizerId, $accessToken, $refreshToken, $accessTokenExpiry, $refreshTokenExpiry) {
            // Deactivate old tokens for this organizer
            $this->deactivateOrganizerTokens($organizerId);
            
            $expiresAt = date('Y-m-d H:i:s', time() + $accessTokenExpiry);
            $refreshExpiresAt = date('Y-m-d H:i:s', time() + $refreshTokenExpiry);
            
            $stmt = $db->prepare("
                INSERT INTO {$this->table} 
                (organizer_id, access_token, refresh_token, expires_at, refresh_expires_at, created_at, updated_at)
                VALUES (:organizer_id, :access_token, :refresh_token, :expires_at, :refresh_expires_at, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                RETURNING *
            ");
            
            $stmt->execute([
                'organizer_id' => $organizerId,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt,
                'refresh_expires_at' => $refreshExpiresAt
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        });
    }
    
    /**
     * Find token by access token
     * @param string $accessToken
     * @return array|null
     */
    public function findByAccessToken($accessToken) {
        return $this->dbPool->execute(function(PDO $db) use ($accessToken) {
            $stmt = $db->prepare("
                SELECT * FROM {$this->table} 
                WHERE access_token = :access_token 
                AND is_active = TRUE 
                AND expires_at > CURRENT_TIMESTAMP
            ");
            $stmt->execute(['access_token' => $accessToken]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Update last_used_at
                $this->updateLastUsed($result['id']);
            }
            
            return $result ?: null;
        });
    }
    
    /**
     * Find token by refresh token
     * @param string $refreshToken
     * @return array|null
     */
    public function findByRefreshToken($refreshToken) {
        return $this->dbPool->execute(function(PDO $db) use ($refreshToken) {
            $stmt = $db->prepare("
                SELECT * FROM {$this->table} 
                WHERE refresh_token = :refresh_token 
                AND is_active = TRUE 
                AND refresh_expires_at > CURRENT_TIMESTAMP
            ");
            $stmt->execute(['refresh_token' => $refreshToken]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        });
    }
    
    /**
     * Deactivate all tokens for an organizer
     * @param int $organizerId
     * @return bool
     */
    public function deactivateOrganizerTokens($organizerId) {
        return $this->dbPool->execute(function(PDO $db) use ($organizerId) {
            $stmt = $db->prepare("
                UPDATE {$this->table} 
                SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP
                WHERE organizer_id = :organizer_id AND is_active = TRUE
            ");
            return $stmt->execute(['organizer_id' => $organizerId]);
        });
    }
    
    /**
     * Deactivate a specific token
     * @param string $accessToken
     * @return bool
     */
    public function deactivateToken($accessToken) {
        return $this->dbPool->execute(function(PDO $db) use ($accessToken) {
            $stmt = $db->prepare("
                UPDATE {$this->table} 
                SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP
                WHERE access_token = :access_token
            ");
            return $stmt->execute(['access_token' => $accessToken]);
        });
    }
    
    /**
     * Update last used timestamp
     * @param int $tokenId
     * @return bool
     */
    private function updateLastUsed($tokenId) {
        return $this->dbPool->execute(function(PDO $db) use ($tokenId) {
            $stmt = $db->prepare("
                UPDATE {$this->table} 
                SET last_used_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            return $stmt->execute(['id' => $tokenId]);
        });
    }
    
    /**
     * Clean up expired tokens (optional maintenance method)
     * @return int Number of tokens deactivated
     */
    public function cleanupExpiredTokens() {
        return $this->dbPool->execute(function(PDO $db) {
            $stmt = $db->prepare("
                UPDATE {$this->table} 
                SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP
                WHERE (expires_at < CURRENT_TIMESTAMP OR refresh_expires_at < CURRENT_TIMESTAMP)
                AND is_active = TRUE
            ");
            $stmt->execute();
            return $stmt->rowCount();
        });
    }
}
