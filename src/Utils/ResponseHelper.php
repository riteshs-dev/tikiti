<?php

namespace App\Utils;

class ResponseHelper {
    /**
     * Format paginated response
     * @param array $data
     * @param int $page
     * @param int $perPage
     * @param int $total
     * @return array
     */
    public static function paginate($data, $page, $perPage, $total) {
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => ($page * $perPage) < $total,
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Validate required fields
     * @param array $data
     * @param array $required
     * @return array Empty array if valid, array of errors if invalid
     */
    public static function validateRequired($data, $required) {
        $errors = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = "Field '{$field}' is required";
            }
        }
        return $errors;
    }
    
    /**
     * Sanitize string input
     * @param string $input
     * @return string
     */
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}
