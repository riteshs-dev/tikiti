<?php

namespace App\Controllers;

use App\Base\BaseController;
use App\Models\OrganizerModel;
use App\Utils\HttpStatus;
use App\Utils\ResponseHelper;
use Exception;

class OrganizerController extends BaseController {
    private $organizerModel;
    
    public function __construct() {
        parent::__construct();
        $this->organizerModel = new OrganizerModel();
    }
    
    /**
     * GET /api/v1/organizers
     * Get all organizers with pagination and filters
     * Query params: page, per_page, is_active, search
     */
    public function index() {
        try {
            // Get query parameters
            $page = (int)($this->getParam('page') ?? 1);
            $perPage = (int)($this->getParam('per_page') ?? 20);
            $isActive = $this->getParam('is_active');
            $search = $this->getParam('search');
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Build filters
            $filters = [];
            if ($isActive !== null) {
                $filters['is_active'] = $isActive === 'true' || $isActive === '1' || $isActive === 1;
            }
            if ($search) {
                $filters['search'] = trim($search);
            }
            
            // Get organizers
            $organizers = $this->organizerModel->getAllOrganizers(
                $filters,
                ['created_at' => 'DESC'],
                $perPage,
                $offset
            );
            
            // Remove password_hash from response (already excluded in getAllOrganizers, but double-check)
            $organizers = array_map(function($org) {
                unset($org['password_hash']);
                return $org;
            }, $organizers);
            
            // Get total count
            $total = $this->organizerModel->countOrganizers($filters);
            
            // Format response with pagination
            $response = ResponseHelper::paginate($organizers, $page, $perPage, $total);
            
            return $this->sendSuccess($response);
            
        } catch (Exception $e) {
            error_log("Get organizers error: " . $e->getMessage());
            return $this->sendServerError("Failed to fetch organizers: " . $e->getMessage());
        }
    }
    
    /**
     * GET /api/v1/organizers/{id}
     * Get single organizer by ID
     */
    public function show() {
        try {
            $id = (int)$this->getParam('id');
            
            if ($id <= 0) {
                return $this->sendValidationError("Invalid organizer ID");
            }
            
            $organizer = $this->organizerModel->findWithoutPassword($id);
            
            if (!$organizer) {
                return $this->sendNotFound("Organizer not found", "ORGANIZER");
            }
            
            return $this->sendSuccess($organizer);
            
        } catch (Exception $e) {
            error_log("Get organizer error: " . $e->getMessage());
            return $this->sendServerError("Failed to fetch organizer: " . $e->getMessage());
        }
    }
    
    /**
     * POST /api/v1/organizers
     * Create a new organizer
     * Required: name, email, password
     */
    public function create() {
        try {
            $body = $this->getRequestBody();
            
            // Validate required fields
            $required = ['name', 'email', 'password'];
            $errors = [];
            
            foreach ($required as $field) {
                if (empty($body[$field])) {
                    $errors[$field] = "Field '{$field}' is required";
                }
            }
            
            if (!empty($errors)) {
                return $this->sendValidationError("Validation failed", $errors);
            }
            
            // Validate email format
            if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->sendValidationError("Invalid email format", ['email' => 'Invalid email format']);
            }
            
            // Check if email already exists
            $existing = $this->organizerModel->findByEmailIncludingInactive($body['email']);
            if ($existing) {
                return $this->sendConflict("Email already exists", ['email' => 'Email is already registered']);
            }
            
            // Validate password length
            if (strlen($body['password']) < 6) {
                return $this->sendValidationError("Password must be at least 6 characters", ['password' => 'Password must be at least 6 characters']);
            }
            
            // Prepare data
            $data = [
                'name' => trim($body['name']),
                'email' => strtolower(trim($body['email'])),
                'password' => $body['password'], // Will be hashed in model
                'is_active' => isset($body['is_active']) ? (bool)$body['is_active'] : true
            ];
            
            // Create organizer
            $organizerId = $this->organizerModel->createOrganizer($data);
            
            if (!$organizerId) {
                return $this->sendServerError("Failed to create organizer");
            }
            
            // Get created organizer (without password)
            $organizer = $this->organizerModel->findWithoutPassword($organizerId);
            
            return $this->sendSuccess($organizer, "Organizer created successfully", HttpStatus::CREATED);
            
        } catch (Exception $e) {
            error_log("Create organizer error: " . $e->getMessage());
            return $this->sendServerError("Failed to create organizer: " . $e->getMessage());
        }
    }
    
    /**
     * PUT /api/v1/organizers/{id}
     * Update an existing organizer
     */
    public function update() {
        try {
            $id = (int)$this->getParam('id');
            
            if ($id <= 0) {
                return $this->sendValidationError("Invalid organizer ID");
            }
            
            // Check if organizer exists
            $existing = $this->organizerModel->find($id);
            if (!$existing) {
                return $this->sendNotFound("Organizer not found", "ORGANIZER");
            }
            
            $body = $this->getRequestBody();
            
            // Validate email if provided
            if (isset($body['email'])) {
                if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
                    return $this->sendValidationError("Invalid email format", ['email' => 'Invalid email format']);
                }
                
                // Check if email is already taken by another organizer
                $emailCheck = $this->organizerModel->findByEmailIncludingInactive($body['email']);
                if ($emailCheck && $emailCheck['id'] != $id) {
                    return $this->sendConflict("Email already exists", ['email' => 'Email is already registered']);
                }
                
                $body['email'] = strtolower(trim($body['email']));
            }
            
            // Validate password if provided
            if (isset($body['password']) && !empty($body['password'])) {
                if (strlen($body['password']) < 6) {
                    return $this->sendValidationError("Password must be at least 6 characters", ['password' => 'Password must be at least 6 characters']);
                }
            }
            
            // Prepare update data
            $data = [];
            $allowedFields = ['name', 'email', 'password', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($body[$field])) {
                    if ($field === 'name') {
                        $data['name'] = trim($body['name']);
                    } elseif ($field === 'is_active') {
                        $data['is_active'] = (bool)$body['is_active'];
                    } else {
                        $data[$field] = $body[$field];
                    }
                }
            }
            
            if (empty($data)) {
                return $this->sendValidationError("No fields to update");
            }
            
            // Update organizer
            $updated = $this->organizerModel->updateOrganizer($id, $data);
            
            if (!$updated) {
                return $this->sendServerError("Failed to update organizer");
            }
            
            // Get updated organizer (without password)
            $organizer = $this->organizerModel->findWithoutPassword($id);
            
            return $this->sendSuccess($organizer, "Organizer updated successfully");
            
        } catch (Exception $e) {
            error_log("Update organizer error: " . $e->getMessage());
            return $this->sendServerError("Failed to update organizer: " . $e->getMessage());
        }
    }
    
    /**
     * DELETE /api/v1/organizers/{id}
     * Delete an organizer (soft delete by setting is_active = false)
     */
    public function delete() {
        try {
            $id = (int)$this->getParam('id');
            
            if ($id <= 0) {
                return $this->sendValidationError("Invalid organizer ID");
            }
            
            // Check if organizer exists
            $existing = $this->organizerModel->find($id);
            if (!$existing) {
                return $this->sendNotFound("Organizer not found", "ORGANIZER");
            }
            
            // Soft delete (set is_active = false) - recommended
            $deleted = $this->organizerModel->updateOrganizer($id, ['is_active' => false]);
            
            if (!$deleted) {
                return $this->sendServerError("Failed to delete organizer");
            }
            
            return $this->sendSuccess(null, "Organizer deleted successfully");
            
        } catch (Exception $e) {
            error_log("Delete organizer error: " . $e->getMessage());
            return $this->sendServerError("Failed to delete organizer: " . $e->getMessage());
        }
    }
    
    /**
     * POST /api/v1/organizers/login
     * Login organizer with email and password
     * Returns organizer data (without password) - tokens should be generated separately via /auth/token
     */
    public function login() {
        try {
            $body = $this->getRequestBody();
            
            // Validate required fields
            if (empty($body['email']) || empty($body['password'])) {
                return $this->sendValidationError("Email and password are required", [
                    'email' => empty($body['email']) ? 'Email is required' : null,
                    'password' => empty($body['password']) ? 'Password is required' : null
                ]);
            }
            
            // Find organizer by email
            $organizer = $this->organizerModel->findByEmail($body['email']);
            
            if (!$organizer) {
                return $this->sendUnauthorized("Invalid email or password");
            }
            
            // Verify password
            if (!$this->organizerModel->verifyPassword($organizer['id'], $body['password'])) {
                return $this->sendUnauthorized("Invalid email or password");
            }
            
            // Remove password hash from response
            unset($organizer['password_hash']);
            
            return $this->sendSuccess($organizer, "Login successful");
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return $this->sendServerError("Failed to login: " . $e->getMessage());
        }
    }
}
