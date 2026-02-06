<?php

namespace App\Controllers;

use App\Base\BaseController;
use App\Database\ConnectionPool;

class HealthController extends BaseController {
    /**
     * Health check endpoint
     */
    public function check() {
        try {
            $dbPool = ConnectionPool::getInstance();
            $stats = $dbPool->getStats();
            
            // Test database connection
            $dbPool->execute(function($db) {
                $db->query('SELECT 1');
            });
            
            $this->sendSuccess([
                'status' => 'healthy',
                'database' => 'connected',
                'pool_stats' => $stats,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->sendError('Service unhealthy: ' . $e->getMessage(), \App\Utils\HttpStatus::SERVICE_UNAVAILABLE, [], 'SERVICE_UNAVAILABLE');
        }
    }
}
