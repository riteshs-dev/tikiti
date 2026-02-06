<?php

namespace App\Router;

class Router {
    private $routes = [];
    private $middleware = [];
    
    /**
     * Add route
     * @param string $method
     * @param string $path
     * @param callable|string $handler
     */
    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Add GET route
     */
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Add PUT route
     */
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Add PATCH route
     */
    public function patch($path, $handler) {
        $this->addRoute('PATCH', $path, $handler);
    }
    
    /**
     * Add middleware
     */
    public function middleware($middleware) {
        $this->middleware[] = $middleware;
    }
    
    /**
     * Get base path from script name
     * @return string
     */
    private function getBasePath() {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        
        // If script is in a subdirectory, return that path
        // Handle both /path/public/index.php and /path/index.php cases
        if ($scriptDir !== '/' && $scriptDir !== '\\' && $scriptDir !== '.') {
            // If accessing via /tikiti-organizer-api/public/, use the parent directory
            if (basename($scriptDir) === 'public') {
                return dirname($scriptDir);
            }
            return rtrim($scriptDir, '/');
        }
        
        return '';
    }
    
    /**
     * Dispatch request
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        
        // Remove base path if project is in a subdirectory
        $basePath = $this->getBasePath();
        if ($basePath && $basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Remove /public if present in path
        if (strpos($path, '/public') === 0) {
            $path = substr($path, 7);
        }
        
        // Normalize path (but keep URL-encoded characters for route matching)
        // We'll decode them later when extracting parameters
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/') ?: '/';
        
        // Debug: Log path (remove in production)
        // error_log("Router: Method=$method, Path=$path, SCRIPT_NAME=" . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));
        
        // Execute middleware (with path context for conditional auth)
        foreach ($this->middleware as $mw) {
            if (is_callable($mw)) {
                // Call with path parameter
                call_user_func($mw, $path);
            } elseif (is_string($mw) && class_exists($mw)) {
                $mw::handle(['path' => $path]);
            }
        }
        
        // Find matching route
        foreach ($this->routes as $routeIndex => $route) {
            $params = [];
            // Check if method matches
            if ($route['method'] === $method) {
                // Try exact match first for simple routes
                if ($route['path'] === $path) {
                    $handler = $route['handler'];
                    
                    // Merge route params with $_GET
                    $_GET = array_merge($_GET, $params);
                    
                    if (is_callable($handler)) {
                        return call_user_func($handler);
                    } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                        list($class, $methodName) = explode('@', $handler);
                        if (class_exists($class)) {
                            $controller = new $class();
                            if (method_exists($controller, $methodName)) {
                                return call_user_func([$controller, $methodName]);
                            }
                        }
                    }
                } elseif ($this->matchPath($route['path'], $path, $params)) {
                    // Pattern match for routes with parameters
                    $handler = $route['handler'];
                    
                    // Merge route params with $_GET
                    $_GET = array_merge($_GET, $params);
                    
                    if (is_callable($handler)) {
                        return call_user_func($handler);
                    } elseif (is_string($handler) && strpos($handler, '@') !== false) {
                        list($class, $methodName) = explode('@', $handler);
                        if (class_exists($class)) {
                            $controller = new $class();
                            if (method_exists($controller, $methodName)) {
                                return call_user_func([$controller, $methodName]);
                            }
                        }
                    }
                }
            }
        }
        
        // 404 Not Found - Return structured API response
        http_response_code(404);
        header('Content-Type: application/json');
        
        // Get available routes for the requested method
        $availableRoutes = [];
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $availableRoutes[] = $route['method'] . ' ' . $route['path'];
            }
        }
        
        // Build response
        $response = [
            'success' => false,
            'error' => 'Route not found',
            'message' => "The requested route '{$method} {$path}' was not found on this server.",
            'request' => [
                'method' => $method,
                'path' => $path
            ],
            'status_code' => 404,
            'code' => 'ROUTE_NOT_FOUND',
            'available_routes' => !empty($availableRoutes) ? $availableRoutes : null,
            'suggestion' => !empty($availableRoutes) 
                ? 'Check available routes above or visit / for API information' 
                : 'Visit / for API endpoint information',
            'timestamp' => time()
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Match path with route pattern
     */
    private function matchPath($pattern, $path, &$params) {
        $params = [];
        $originalPattern = rtrim($pattern, '/') ?: '/';
        $path = rtrim($path, '/') ?: '/';
        
        // Exact match for simple routes (no parameters)
        if (strpos($originalPattern, '{') === false) {
            return $originalPattern === $path;
        }
        
        // Extract parameter names from original pattern
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $originalPattern, $paramNames);
        
        // Convert route pattern to regex
        // For organizer_id, allow URL-encoded characters
        // Use [^?]+ to match any characters except ? (allows /, #, and % for URL encoding)
        $regexPattern = $originalPattern;
        
        // Special handling for organizer_id - allow more characters including URL-encoded ones
        if (strpos($regexPattern, '{organizer_id}') !== false) {
            // Allow any characters except ? (including /, #, and % for URL encoding)
            $regexPattern = str_replace('{organizer_id}', '([^?]+)', $regexPattern);
            // Replace other parameters (standard pattern)
            $regexPattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/?#]+)', $regexPattern);
        } else {
            // Standard pattern for other parameters
            $regexPattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/?#]+)', $regexPattern);
        }
        
        $regexPattern = '#^' . $regexPattern . '$#';
        
        if (preg_match($regexPattern, $path, $matches)) {
            // Map values to parameter names
            array_shift($matches);
            if (!empty($paramNames[1])) {
                foreach ($paramNames[1] as $index => $name) {
                    if (isset($matches[$index])) {
                        // URL decode the parameter value
                        $decoded = urldecode($matches[$index]);
                        $params[$name] = $decoded;
                    }
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get debug info (for debugging only)
     * @return array
     */
    public function getDebugInfo() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $basePath = $this->getBasePath();
        
        // Process path like dispatch does
        if ($basePath && $basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        if (strpos($path, '/public') === 0) {
            $path = substr($path, 7);
        }
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/') ?: '/';
        
        return [
            'method' => $method,
            'request_uri' => $requestUri,
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'base_path' => $basePath,
            'processed_path' => $path,
            'routes_count' => count($this->routes),
            'routes' => array_map(function($r) {
                return [
                    'method' => $r['method'],
                    'path' => $r['path']
                ];
            }, $this->routes)
        ];
    }
}
