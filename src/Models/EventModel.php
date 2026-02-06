<?php

namespace App\Models;

use App\Base\BaseModel;

class EventModel extends BaseModel {
    protected $table = 'events';
    protected $primaryKey = 'id';
    
    /**
     * Get all events with optional filters (scoped to organizer)
     * @param int $organizerId Required organizer ID
     * @param array $filters
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getAllEvents($organizerId, $filters = [], $orderBy = ['created_at' => 'DESC'], $limit = null, $offset = null) {
        // Always exclude deleted events and filter by organizer
        $where = "(deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP) AND organizer_id = :organizer_id";
        $params = ['organizer_id' => $organizerId];
        
        // Build WHERE clause for additional filters
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                if ($key !== 'organizer_id') { // Don't allow overriding organizer_id
                    $where .= " AND $key = :$key";
                    $params[$key] = $value;
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
        
        $sql = "SELECT * FROM {$this->table} WHERE $where$orderClause$limitClause";
        return $this->query($sql, $params);
    }
    
    /**
     * Get event by ID (scoped to organizer)
     * @param int $id
     * @param int $organizerId Required organizer ID
     * @return array|null
     */
    public function getEventById($id, $organizerId) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} 
             WHERE {$this->primaryKey} = :id 
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)",
            ['id' => $id, 'organizer_id' => $organizerId]
        );
    }
    
    /**
     * Get events by status (scoped to organizer)
     * @param string $status
     * @param int $organizerId Required organizer ID
     * @return array
     */
    public function getEventsByStatus($status, $organizerId) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE status = :status 
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)
             ORDER BY created_at DESC",
            ['status' => $status, 'organizer_id' => $organizerId]
        );
    }
    
    /**
     * Get upcoming events (scoped to organizer)
     * @param int $organizerId Required organizer ID
     * @return array
     */
    public function getUpcomingEvents($organizerId) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE start_date >= CURRENT_TIMESTAMP 
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)
             ORDER BY start_date ASC",
            ['organizer_id' => $organizerId]
        );
    }
    
    /**
     * Get past events (scoped to organizer)
     * @param int $organizerId Required organizer ID
     * @return array
     */
    public function getPastEvents($organizerId) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE end_date < CURRENT_TIMESTAMP 
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)
             ORDER BY end_date DESC",
            ['organizer_id' => $organizerId]
        );
    }
    
    /**
     * Search events by name or description (scoped to organizer)
     * @param string $searchTerm
     * @param int $organizerId Required organizer ID
     * @return array
     */
    public function searchEvents($searchTerm, $organizerId) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE (name ILIKE :search OR description ILIKE :search)
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)
             ORDER BY created_at DESC",
            ['search' => "%{$searchTerm}%", 'organizer_id' => $organizerId]
        );
    }
    
    /**
     * Get featured events (scoped to organizer)
     * @param int $organizerId Required organizer ID
     * @return array
     */
    public function getFeaturedEvents($organizerId) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE is_featured = true 
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)
             ORDER BY created_at DESC",
            ['organizer_id' => $organizerId]
        );
    }
    
    /**
     * Get events by organizer
     * @param int $organizerId
     * @return array
     */
    public function getEventsByOrganizer($organizerId) {
        return $this->findAll(
            ['organizer_id' => $organizerId],
            ['created_at' => 'DESC']
        );
    }
    
    /**
     * Get events by category (scoped to organizer)
     * @param string $category
     * @param int $organizerId Required organizer ID
     * @return array
     */
    public function getEventsByCategory($category, $organizerId) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE category = :category 
             AND organizer_id = :organizer_id
             AND (deleted_at IS NULL OR deleted_at > CURRENT_TIMESTAMP)
             ORDER BY start_date ASC",
            ['category' => $category, 'organizer_id' => $organizerId]
        );
    }
    
    /**
     * Update event (ensures organizer_id matches)
     * @param int $id
     * @param array $data
     * @param int $organizerId Required organizer ID
     * @return bool
     */
    public function updateEvent($id, $data, $organizerId) {
        // Ensure organizer_id in data matches the provided organizer_id
        $data['organizer_id'] = $organizerId;
        
        return $this->dbPool->execute(function($db) use ($id, $data, $organizerId) {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . 
                   " WHERE {$this->primaryKey} = :id AND organizer_id = :organizer_id";
            $data['id'] = $id;
            $data['organizer_id'] = $organizerId;
            
            $stmt = $db->prepare($sql);
            return $stmt->execute($data);
        });
    }
    
    /**
     * Delete event (ensures organizer_id matches)
     * @param int $id
     * @param int $organizerId Required organizer ID
     * @return bool
     */
    public function deleteEvent($id, $organizerId) {
        return $this->dbPool->execute(function($db) use ($id, $organizerId) {
            $stmt = $db->prepare(
                "DELETE FROM {$this->table} 
                 WHERE {$this->primaryKey} = :id AND organizer_id = :organizer_id"
            );
            return $stmt->execute(['id' => $id, 'organizer_id' => $organizerId]);
        });
    }
}
