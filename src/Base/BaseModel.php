<?php

namespace App\Base;

use App\Database\ConnectionPool;
use PDO;

abstract class BaseModel {
    protected $table;
    protected $primaryKey = 'id';
    protected $dbPool;
    
    public function __construct() {
        $this->dbPool = ConnectionPool::getInstance();
    }
    
    /**
     * Find record by ID
     * @param int $id
     * @return array|null
     */
    public function find($id) {
        return $this->dbPool->execute(function(PDO $db) use ($id) {
            $stmt = $db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch() ?: null;
        });
    }
    
    /**
     * Find all records
     * @param array $conditions
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findAll($conditions = [], $orderBy = [], $limit = null, $offset = null) {
        return $this->dbPool->execute(function(PDO $db) use ($conditions, $orderBy, $limit, $offset) {
            $sql = "SELECT * FROM {$this->table}";
            $params = [];
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $key => $value) {
                    $where[] = "$key = :$key";
                    $params[$key] = $value;
                }
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            
            if (!empty($orderBy)) {
                $order = [];
                foreach ($orderBy as $column => $direction) {
                    $order[] = "$column $direction";
                }
                $sql .= " ORDER BY " . implode(', ', $order);
            }
            
            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                $params['limit'] = $limit;
            }
            
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
                $params['offset'] = $offset;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        });
    }
    
    /**
     * Create new record
     * @param array $data
     * @return int
     */
    public function create($data) {
        return $this->dbPool->execute(function(PDO $db) use ($data) {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders) RETURNING {$this->primaryKey}";
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            
            $result = $stmt->fetch();
            return $result[$this->primaryKey] ?? $db->lastInsertId();
        });
    }
    
    /**
     * Update record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        return $this->dbPool->execute(function(PDO $db) use ($id, $data) {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE {$this->primaryKey} = :id";
            $data['id'] = $id;
            
            $stmt = $db->prepare($sql);
            return $stmt->execute($data);
        });
    }
    
    /**
     * Delete record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        return $this->dbPool->execute(function(PDO $db) use ($id) {
            $stmt = $db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
            return $stmt->execute(['id' => $id]);
        });
    }
    
    /**
     * Execute custom query
     * @param string $sql
     * @param array $params
     * @return array
     */
    protected function query($sql, $params = []) {
        return $this->dbPool->execute(function(PDO $db) use ($sql, $params) {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        });
    }
    
    /**
     * Execute custom query (single row)
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    protected function queryOne($sql, $params = []) {
        return $this->dbPool->execute(function(PDO $db) use ($sql, $params) {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() ?: null;
        });
    }
}
