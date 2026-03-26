<?php

namespace App\Models;

use App\Core\Database;
use App\Core\App;
use Exception;

/**
 * Associate Management Class
 * Handles all associate-related operations
 */
class Associate
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = App::getInstance();
    }

    /**
     * Get all associates
     * @param array $filters Optional filters (status, level, etc.)
     * @return array Associates data
     */
    public function getAll($filters = [])
    {
        try {
            $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                    FROM associates a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['level'])) {
                $sql .= " AND a.level = ?";
                $params[] = $filters['level'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (a.name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY a.created_at DESC";

            $associates = $this->db->fetchAll($sql, $params);

            return [
                'success' => true,
                'associates' => $associates,
                'total' => count($associates)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch associates: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get associate by ID
     * @param int $id Associate ID
     * @return array Associate data
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                    FROM associates a 
                    LEFT JOIN users u ON a.user_id = u.id 
                    WHERE a.id = ?";

            $associate = $this->db->fetch($sql, [$id]);

            if (!$associate) {
                return [
                    'success' => false,
                    'error' => 'Associate not found'
                ];
            }

            return [
                'success' => true,
                'associate' => $associate
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch associate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create new associate
     * @param array $data Associate data
     * @return array Creation result
     */
    public function create($data)
    {
        try {
            // Validate required fields
            $required = ['name', 'email', 'phone', 'level'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'error' => "Field {$field} is required"
                    ];
                }
            }

            // Validate email format
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'error' => 'Invalid email format'
                ];
            }

            // Validate phone format
            if (!empty($data['phone'])) {
                $data['phone'] = preg_replace('/[^\d+\s]/', '', $data['phone']);
                if (strlen($data['phone']) < 10 || strlen($data['phone']) > 15) {
                    return [
                        'success' => false,
                        'error' => 'Phone number must be 10-15 digits'
                    ];
                }
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'active';
            $data['level'] = $data['level'] ?? 'bronze';
            $data['created_at'] = date('Y-m-d H:i:s');

            $associateId = $this->db->insert(
                "INSERT INTO associates (name, email, phone, level, status, user_id, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $data['name'],
                    $data['email'],
                    $data['phone'],
                    $data['level'],
                    $data['status'],
                    $data['user_id'] ?? null,
                    $data['created_at']
                ]
            );

            if ($associateId) {
                return [
                    'success' => true,
                    'associate_id' => $associateId,
                    'message' => 'Associate created successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to create associate'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to create associate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update associate
     * @param int $id Associate ID
     * @param array $data Updated data
     * @return array Update result
     */
    public function update($id, $data)
    {
        try {
            // Check if associate exists
            $existing = $this->db->fetch("SELECT id FROM associates WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Associate not found'
                ];
            }

            // Validate email format
            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'error' => 'Invalid email format'
                ];
            }

            // Validate phone format
            if (!empty($data['phone'])) {
                $data['phone'] = preg_replace('/[^\d+\s]/', '', $data['phone']);
                if (strlen($data['phone']) < 10 || strlen($data['phone']) > 15) {
                    return [
                        'success' => false,
                        'error' => 'Phone number must be 10-15 digits'
                    ];
                }
            }

            // Build update query
            $updateFields = [];
            $params = [];

            $allowedFields = ['name', 'email', 'phone', 'level', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }

            if (!empty($updateFields)) {
                $updateFields[] = "updated_at = ?";
                $params[] = date('Y-m-d H:i:s');
                $params[] = $id;
            }

            $sql = "UPDATE associates SET " . implode(', ', $updateFields) . " WHERE id = ?";

            $result = $this->db->query($sql, $params);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Associate updated successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to update associate'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update associate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete associate
     * @param int $id Associate ID
     * @return array Deletion result
     */
    public function delete($id)
    {
        try {
            // Check if associate exists
            $existing = $this->db->fetch("SELECT id FROM associates WHERE id = ?", [$id]);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Associate not found'
                ];
            }

            // Delete associate
            $result = $this->db->query("DELETE FROM associates WHERE id = ?", [$id]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Associate deleted successfully'
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to delete associate'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to delete associate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get associate statistics
     * @return array Statistics data
     */
    public function getStatistics()
    {
        try {
            $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN level = 'platinum' THEN 1 END) as platinum,
                COUNT(CASE WHEN level = 'gold' THEN 1 END) as gold,
                COUNT(CASE WHEN level = 'silver' THEN 1 END) as silver,
                COUNT(CASE WHEN level = 'bronze' THEN 1 END) as bronze
                FROM associates";

            $stats = $this->db->fetch($sql);

            return [
                'success' => true,
                'statistics' => [
                    'total' => $stats['total'] ?? 0,
                    'active' => $stats['active'] ?? 0,
                    'by_level' => [
                        'platinum' => $stats['platinum'] ?? 0,
                        'gold' => $stats['gold'] ?? 0,
                        'silver' => $stats['silver'] ?? 0,
                        'bronze' => $stats['bronze'] ?? 0
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch statistics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get associate levels
     * @return array Available levels
     */
    public function getLevels()
    {
        return [
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum'
        ];
    }
}
