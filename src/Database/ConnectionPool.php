<?php

namespace App\Database;

use PDO;
use PDOException;
use Exception;

class ConnectionPool {
    private static $instance = null;
    private $pool = [];
    private $maxConnections;
    private $config;
    private $activeConnections = 0;
    
    private function __construct() {
        $this->config = \Config::get('db');
        $this->maxConnections = $this->config['pool_size'];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get a database connection from the pool
     * @return PDO
     * @throws Exception
     */
    public function getConnection() {
        // Try to reuse existing connection
        if (!empty($this->pool)) {
            $connection = array_pop($this->pool);
            try {
                // Test if connection is still alive
                $connection->query('SELECT 1');
                $this->activeConnections++;
                return $connection;
            } catch (PDOException $e) {
                // Connection is dead, create a new one
            }
        }
        
        // Create new connection if pool is not full
        if ($this->activeConnections < $this->maxConnections) {
            $connection = $this->createConnection();
            $this->activeConnections++;
            return $connection;
        }
        
        // Pool is full, wait and retry or create temporary connection
        return $this->createConnection();
    }
    
    /**
     * Return connection to the pool
     * @param PDO $connection
     */
    public function releaseConnection(PDO $connection) {
        if (count($this->pool) < $this->maxConnections) {
            $this->pool[] = $connection;
            $this->activeConnections--;
        } else {
            // Pool is full, close the connection
            $connection = null;
            $this->activeConnections--;
        }
    }
    
    /**
     * Create a new database connection
     * @return PDO
     * @throws Exception
     */
    private function createConnection() {
        try {
            $dsn = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['name']
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Enable persistent connections
                PDO::ATTR_TIMEOUT => 5
            ];
            
            $pdo = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                $options
            );
            
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a query using connection from pool
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    public function execute(callable $callback) {
        $connection = $this->getConnection();
        try {
            return $callback($connection);
        } finally {
            $this->releaseConnection($connection);
        }
    }
    
    /**
     * Get pool statistics
     * @return array
     */
    public function getStats() {
        return [
            'pool_size' => count($this->pool),
            'active_connections' => $this->activeConnections,
            'max_connections' => $this->maxConnections
        ];
    }
}
