<?php

class Config {
    private static $config = null;
    
    public static function load() {
        if (self::$config === null) {
            $envFile = __DIR__ . '/../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) continue;
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
            
            // self::$config = [
            //     'db' => [
            //         'host' => $_ENV['DB_HOST'] ?? '20.40.45.193',
            //         'port' => $_ENV['DB_PORT'] ?? '5432',
            //         'name' => $_ENV['DB_NAME'] ?? 'belive_events',
            //         'user' => $_ENV['DB_USER'] ?? 'belive_events',
            //         'password' => $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? 'belive$events@123',
            //         'pool_size' => (int)($_ENV['DB_POOL_SIZE'] ?? 10)
            //     ],

                self::$config = [
                    'db' => [
                        'host' => $_ENV['DB_HOST'] ?? '20.40.45.193',
                        'port' => $_ENV['DB_PORT'] ?? '5432',
                        'name' => $_ENV['DB_NAME'] ?? 'tikiti_db',
                        'user' => $_ENV['DB_USER'] ?? 'tikiti',
                        'password' => $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? 'hafse%sean@123',
                        'pool_size' => (int) ($_ENV['DB_POOL_SIZE'] ?? 10),
                    ],
                

                'encryption' => [
                    'key' => $_ENV['ENCRYPTION_KEY'] ?? '',
                    'method' => $_ENV['ENCRYPTION_METHOD'] ?? 'AES-256-CBC'
                ],
                'api' => [
                    'version' => $_ENV['API_VERSION'] ?? 'v1',
                    'base_url' => $_ENV['API_BASE_URL'] ?? 'http://localhost:8000',
                    'token' => $_ENV['API_TOKEN'] ?? '',
                    'auth_bypass_routes' => !empty($_ENV['API_AUTH_BYPASS_ROUTES']) 
                        ? explode(',', $_ENV['API_AUTH_BYPASS_ROUTES']) 
                        : ['/health']
                ],
                'cors' => [
                    'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:4200')
                ]
            ];
        }
        
        return self::$config;
    }
    
    public static function get($key = null) {
        $config = self::load();
        if ($key === null) {
            return $config;
        }
        
        $keys = explode('.', $key);
        $value = $config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}
