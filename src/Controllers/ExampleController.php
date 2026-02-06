<?php

namespace App\Controllers;

use App\Base\BaseController;

class ExampleController extends BaseController {
    /**
     * GET /api/v1/example
     */
    public function index() {
        $data = [
            'message' => 'This is an example endpoint',
            'items' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2']
            ]
        ];
        
        $this->sendSuccess($data);
    }
    
    /**
     * GET /api/v1/example/{id}
     */
    public function show() {
        $id = $this->getParam('id');
        
        if (!$id) {
            $this->sendError('ID is required', \App\Utils\HttpStatus::BAD_REQUEST, [], 'ID_REQUIRED');
        }
        
        $data = [
            'id' => $id,
            'name' => 'Example Item ' . $id,
            'description' => 'This is item number ' . $id
        ];
        
        $this->sendSuccess($data);
    }
    
    /**
     * POST /api/v1/example
     */
    public function create() {
        $body = $this->getRequestBody();
        
        // Validation example
        if (empty($body['name'])) {
            $this->sendValidationError('Name is required', ['name' => 'This field is required']);
        }
        
        // Simulate creation
        $data = [
            'id' => rand(1000, 9999),
            'name' => $body['name'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->sendSuccess($data, 'Item created successfully', \App\Utils\HttpStatus::CREATED);
    }
}
