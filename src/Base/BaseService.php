<?php

namespace App\Base;

use App\Database\ConnectionPool;

abstract class BaseService {
    protected $dbPool;
    
    public function __construct() {
        $this->dbPool = ConnectionPool::getInstance();
    }
    
    /**
     * Execute database operation with connection from pool
     * @param callable $callback
     * @return mixed
     */
    protected function execute(callable $callback) {
        return $this->dbPool->execute($callback);
    }
}
