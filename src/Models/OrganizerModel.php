<?php

namespace App\Models;

use App\Base\BaseModel;
use PDO;

class OrganizerModel extends BaseModel {
    protected $table = 'organizers';
    protected $primaryKey = 'id';
    
    /**
     * Find organizer by email
     * @param string $email
     * @return array|null
     */
    public function findByEmail($email) {
        return $this->dbPool->execute(function(PDO $db) use ($email) {
            // Normalize email to lowercase for case-insensitive comparison
            $normalizedEmail = strtolower(trim($email));
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE LOWER(email) = LOWER(:email) AND is_active = true");
            $stmt->execute(['email' => $normalizedEmail]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        });
    }
    
    /**
     * Find organizer by email (including inactive)
     * @param string $email
     * @return array|null
     */
    public function findByEmailIncludingInactive($email) {
        return $this->dbPool->execute(function(PDO $db) use ($email) {
            // Normalize email to lowercase for case-insensitive comparison
            $normalizedEmail = strtolower(trim($email));
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE LOWER(email) = LOWER(:email)");
            $stmt->execute(['email' => $normalizedEmail]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        });
    }
    
    /**
     * Get all organizers with pagination and filters
     * @param array $filters
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getAllOrganizers($filters = [], $orderBy = ['created_at' => 'DESC'], $limit = null, $offset = null) {
        $where = "1=1";
        $params = [];
        
        // Build WHERE clause for filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($key === 'is_active') {
                    $where .= " AND is_active = :is_active";
                    $params['is_active'] = $value;
                } elseif ($key === 'search') {
                    $where .= " AND (name ILIKE :search OR email ILIKE :search)";
                    $params['search'] = "%{$value}%";
                }
            }
        }
        
        // Build ORDER BY clause
        $orderClause = '';
        if (!empty($orderBy)) {
            $order = [];
            foreach ($orderBy as $column => $direction) {
                $order[] = "$column $direction";
            }
            $orderClause = " ORDER BY " . implode(', ', $order);
        }
        
        // Build LIMIT and OFFSET
        $limitClause = '';
        if ($limit !== null) {
            $limitClause = " LIMIT :limit";
            $params['limit'] = $limit;
        }
        if ($offset !== null) {
            $limitClause .= " OFFSET :offset";
            $params['offset'] = $offset;
        }
        
        $sql = "SELECT id, name, email, is_active, created_at, updated_at FROM {$this->table} WHERE $where$orderClause$limitClause";
        return $this->query($sql, $params);
    }
    
    /**
     * Count total organizers
     * @param array $filters
     * @return int
     */
    public function countOrganizers($filters = []) {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($key === 'is_active') {
                    $where .= " AND is_active = :is_active";
                    $params['is_active'] = $value;
                } elseif ($key === 'search') {
                    $where .= " AND (name ILIKE :search OR email ILIKE :search)";
                    $params['search'] = "%{$value}%";
                }
            }
        }
        
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE $where";
        $result = $this->queryOne($sql, $params);
        return (int)($result['total'] ?? 0);
    }
    
    /**
     * Create organizer with password hashing
     * @param array $data
     * @return int
     */
    public function createOrganizer($data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']); // Remove plain password
        }
        
        // Set timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Set default is_active if not provided
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }
        
        return $this->create($data);
    }
    
    /**
     * Update organizer
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateOrganizer($id, $data) {
        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']); // Remove plain password
        }
        
        // Update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }
    
    /**
     * Verify password for organizer
     * @param int $organizerId
     * @param string $password
     * @return bool
     */
    public function verifyPassword($organizerId, $password) {
        $organizer = $this->find($organizerId);
        if (!$organizer || empty($organizer['password_hash'])) {
            return false;
        }
        
        return password_verify($password, $organizer['password_hash']);
    }
    
    /**
     * Get organizer without password hash
     * @param int $id
     * @return array|null
     */
    public function findWithoutPassword($id) {
        $organizer = $this->find($id);
        if ($organizer) {
            unset($organizer['password_hash']);
        }
        return $organizer;
    }
}
