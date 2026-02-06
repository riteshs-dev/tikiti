<?php

namespace App\Controllers;

use App\Base\BaseController;
use App\Models\EventModel;
use App\Utils\ResponseHelper;
use App\Utils\HttpStatus;

class EventController extends BaseController {
    private $eventModel;
    
    public function __construct() {
        parent::__construct();
        $this->eventModel = new EventModel();
    }
    
    /**
     * GET /api/v1/events
     * Fetch all events for the organizer with optional filters and pagination
     * Query params: page, per_page, status, search, upcoming, past, featured, category
     * Requires: X-ORGANIZER-ID header (encrypted)
     */
    public function index() {
        try {
            // Require organizer ID from encrypted header
            $organizerId = $this->requireOrganizerId();
            
            // Get query parameters
            $page = (int)($this->getParam('page') ?? 1);
            $perPage = (int)($this->getParam('per_page') ?? 20);
            $status = $this->getParam('status');
            $search = $this->getParam('search');
            $upcoming = $this->getParam('upcoming');
            $past = $this->getParam('past');
            $featured = $this->getParam('featured');
            $category = $this->getParam('category');
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Build filters (excluding deleted events and organizer_id - already handled)
            $filters = [];
            if ($status) {
                $filters['status'] = $status;
            }
            
            // Determine which method to use
            if ($upcoming === 'true' || $upcoming === '1') {
                $events = $this->eventModel->getUpcomingEvents($organizerId);
                $total = count($events);
                $events = array_slice($events, $offset, $perPage);
            } elseif ($past === 'true' || $past === '1') {
                $events = $this->eventModel->getPastEvents($organizerId);
                $total = count($events);
                $events = array_slice($events, $offset, $perPage);
            } elseif ($featured === 'true' || $featured === '1') {
                $events = $this->eventModel->getFeaturedEvents($organizerId);
                $total = count($events);
                $events = array_slice($events, $offset, $perPage);
            } elseif ($search) {
                $events = $this->eventModel->searchEvents($search, $organizerId);
                $total = count($events);
                $events = array_slice($events, $offset, $perPage);
            } elseif ($category) {
                $events = $this->eventModel->getEventsByCategory($category, $organizerId);
                $total = count($events);
                $events = array_slice($events, $offset, $perPage);
            } else {
                // Get total count for pagination (excluding deleted, scoped to organizer)
                $allEvents = $this->eventModel->getAllEvents($organizerId, $filters);
                $total = count($allEvents);
                
                // Get paginated results
                $events = $this->eventModel->getAllEvents(
                    $organizerId,
                    $filters,
                    ['created_at' => 'DESC'],
                    $perPage,
                    $offset
                );
            }
            
            // Format response with pagination
            $response = ResponseHelper::paginate($events, $page, $perPage, $total);
            
            $this->sendSuccess($response);
            
        } catch (\Exception $e) {
            $this->sendServerError('Failed to fetch events: ' . $e->getMessage());
        }
    }
    
    /**
     * GET /api/v1/events/{id}
     * Fetch a single event by ID (scoped to organizer)
     * Requires: X-ORGANIZER-ID header (encrypted)
     */
    public function show() {
        try {
            // Require organizer ID from encrypted header
            $organizerId = $this->requireOrganizerId();
            
            $id = $this->getParam('id');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Valid event ID is required', HttpStatus::BAD_REQUEST, [], 'INVALID_ID');
            }
            
            $event = $this->eventModel->getEventById($id, $organizerId);
            
            if (!$event) {
                $this->sendNotFound('Event not found', 'EVENT');
            }
            
            $this->sendSuccess($event);
            
        } catch (\Exception $e) {
            $this->sendServerError('Failed to fetch event: ' . $e->getMessage());
        }
    }
    
    /**
     * POST /api/v1/events
     * Create a new event (organizer_id from encrypted header)
     * Requires: X-ORGANIZER-ID header (encrypted)
     */
    public function create() {
        try {
            // Require organizer ID from encrypted header
            $organizerId = $this->requireOrganizerId();
            
            $body = $this->getRequestBody();
            
            // Validation - required fields (organizer_id comes from header, not body)
            $required = ['name', 'start_date', 'end_date'];
            $errors = ResponseHelper::validateRequired($body, $required);
            
            if (!empty($errors)) {
                $this->sendValidationError('Validation failed', $errors);
            }
            
            // Sanitize and prepare data (organizer_id from header)
            $data = [
                'name' => ResponseHelper::sanitize($body['name']),
                'organizer_id' => $organizerId, // From encrypted header
                'start_date' => $body['start_date'],
                'end_date' => $body['end_date'],
                'description' => isset($body['description']) ? ResponseHelper::sanitize($body['description']) : null,
                'short_description' => isset($body['short_description']) ? ResponseHelper::sanitize($body['short_description']) : null,
                'status' => $body['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Optional fields
            $optionalFields = [
                'venue_id', 'event_type', 'category', 'genre', 'timezone',
                'city', 'state', 'country', 'address', 'banner_image',
                'image_gallery', 'video_url', 'artist_lineup', 'event_schedule',
                'seating_layout', 'venue_map', 'website_url', 'attendees_range',
                'pricing', 'ticket_available', 'refund_policy', 'terms_conditions',
                'language', 'age_restriction', 'entry_limits', 'is_featured',
                'is_promoted', 'promotion_expiry'
            ];
            
            foreach ($optionalFields as $field) {
                if (isset($body[$field])) {
                    if (in_array($field, ['venue_id', 'ticket_available', 'is_featured', 'is_promoted'])) {
                        $data[$field] = is_bool($body[$field]) ? $body[$field] : (bool)$body[$field];
                    } elseif (in_array($field, ['description', 'short_description', 'address', 'banner_image', 
                                                  'image_gallery', 'video_url', 'artist_lineup', 'event_schedule',
                                                  'seating_layout', 'venue_map', 'refund_policy', 'terms_conditions', 
                                                  'entry_limits'])) {
                        $data[$field] = ResponseHelper::sanitize($body[$field]);
                    } else {
                        $data[$field] = $body[$field];
                    }
                }
            }
            
            $eventId = $this->eventModel->create($data);
            
            $event = $this->eventModel->getEventById($eventId, $organizerId);
            
            $this->sendSuccess($event, 'Event created successfully', HttpStatus::CREATED);
            
        } catch (\Exception $e) {
            $this->sendServerError('Failed to create event: ' . $e->getMessage());
        }
    }
    
    /**
     * PUT /api/v1/events/{id}
     * Update an existing event (scoped to organizer)
     * Requires: X-ORGANIZER-ID header (encrypted)
     */
    public function update() {
        try {
            // Require organizer ID from encrypted header
            $organizerId = $this->requireOrganizerId();
            
            $id = $this->getParam('id');
            $body = $this->getRequestBody();
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Valid event ID is required', HttpStatus::BAD_REQUEST, [], 'INVALID_ID');
            }
            
            // Check if event exists and belongs to organizer
            $event = $this->eventModel->getEventById($id, $organizerId);
            if (!$event) {
                $this->sendNotFound('Event not found', 'EVENT');
            }
            
            // Prepare update data - all fields from events table (except organizer_id)
            $data = [];
            $allowedFields = [
                'name', 'venue_id', 'description', 'short_description',
                'event_type', 'category', 'genre', 'start_date', 'end_date', 'timezone',
                'city', 'state', 'country', 'address', 'banner_image', 'image_gallery',
                'video_url', 'artist_lineup', 'event_schedule', 'seating_layout',
                'venue_map', 'website_url', 'attendees_range', 'pricing',
                'ticket_available', 'refund_policy', 'terms_conditions', 'language',
                'age_restriction', 'entry_limits', 'status', 'is_featured',
                'is_promoted', 'promotion_expiry'
            ];
            
            foreach ($allowedFields as $field) {
                if (isset($body[$field])) {
                    if (in_array($field, ['venue_id'])) {
                        $data[$field] = (int)$body[$field];
                    } elseif (in_array($field, ['ticket_available', 'is_featured', 'is_promoted'])) {
                        $data[$field] = is_bool($body[$field]) ? $body[$field] : (bool)$body[$field];
                    } elseif (in_array($field, ['name', 'description', 'short_description', 'address',
                                                  'banner_image', 'image_gallery', 'video_url',
                                                  'artist_lineup', 'event_schedule', 'seating_layout',
                                                  'venue_map', 'refund_policy', 'terms_conditions',
                                                  'entry_limits'])) {
                        $data[$field] = ResponseHelper::sanitize($body[$field]);
                    } else {
                        $data[$field] = $body[$field];
                    }
                }
            }
            
            if (empty($data)) {
                $this->sendError('No fields to update', HttpStatus::BAD_REQUEST, [], 'NO_FIELDS_TO_UPDATE');
            }
            
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Update with organizer_id check
            $this->eventModel->updateEvent($id, $data, $organizerId);
            
            $updatedEvent = $this->eventModel->getEventById($id, $organizerId);
            
            $this->sendSuccess($updatedEvent, 'Event updated successfully');
            
        } catch (\Exception $e) {
            $this->sendServerError('Failed to update event: ' . $e->getMessage());
        }
    }
    
    /**
     * DELETE /api/v1/events/{id}
     * Delete an event (scoped to organizer)
     * Requires: X-ORGANIZER-ID header (encrypted)
     */
    public function delete() {
        try {
            // Require organizer ID from encrypted header
            $organizerId = $this->requireOrganizerId();
            
            $id = $this->getParam('id');
            
            if (!$id || !is_numeric($id)) {
                $this->sendError('Valid event ID is required', HttpStatus::BAD_REQUEST, [], 'INVALID_ID');
            }
            
            // Check if event exists and belongs to organizer
            $event = $this->eventModel->getEventById($id, $organizerId);
            if (!$event) {
                $this->sendNotFound('Event not found', 'EVENT');
            }
            
            // Delete with organizer_id check
            $this->eventModel->deleteEvent($id, $organizerId);
            
            $this->sendSuccess(null, 'Event deleted successfully', HttpStatus::OK);
            
        } catch (\Exception $e) {
            $this->sendServerError('Failed to delete event: ' . $e->getMessage());
        }
    }
    
    /**
     * GET /api/v1/events/status/{status}
     * Get events by status (scoped to organizer)
     * Requires: X-ORGANIZER-ID header (encrypted)
     */
    public function getByStatus() {
        try {
            // Require organizer ID from encrypted header
            $organizerId = $this->requireOrganizerId();
            
            $status = $this->getParam('status');
            
            if (!$status) {
                $this->sendError('Status is required', HttpStatus::BAD_REQUEST, [], 'STATUS_REQUIRED');
            }
            
            $events = $this->eventModel->getEventsByStatus($status, $organizerId);
            
            $this->sendSuccess($events);
            
        } catch (\Exception $e) {
            $this->sendServerError('Failed to fetch events: ' . $e->getMessage());
        }
    }
}
