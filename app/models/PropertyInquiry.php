<?php
/**
 * Property Inquiry Model
 * Handles property inquiry operations
 */

use App\Models\Model;
use App\Core\Database;

class PropertyInquiry extends Model {
    protected static $table = 'property_inquiries';

    /**
     * Create new inquiry
     */
    public function createInquiry($data) {
        try {
            $sql = "INSERT INTO {$this->table} (
                property_id, user_id, guest_name, guest_email, guest_phone,
                message, inquiry_type, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $db = Database::getInstance();
            $stmt = $db->query($sql, [
                $data['property_id'],
                $data['user_id'] ?? null,
                $data['guest_name'] ?? null,
                $data['guest_email'] ?? null,
                $data['guest_phone'] ?? null,
                $data['message'],
                $data['inquiry_type'] ?? 'general',
                $data['status'] ?? 'new'
            ]);

            return $db->lastInsertId();

        } catch (\Exception $e) {
            error_log('PropertyInquiry creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inquiries by property
     */
    public function getByProperty($property_id, $limit = 10) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM {$this->table}
                    WHERE property_id = ?
                    ORDER BY created_at DESC LIMIT ?";

            $stmt = $db->query($sql, [$property_id, $limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('PropertyInquiry fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get inquiries by user
     */
    public function getByUser($user_id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT i.*, p.title as property_title, p.city, p.state
                    FROM {$this->table} i
                    LEFT JOIN properties p ON i.property_id = p.id
                    WHERE i.user_id = ?";

            $stmt = $db->query($sql, [$user_id]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('User inquiries fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update inquiry status
     */
    public function updateStatus($inquiry_id, $status) {
        try {
            $db = Database::getInstance();
            $sql = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->query($sql, [$status, $inquiry_id]);
            return true;

        } catch (\Exception $e) {
            error_log('Inquiry status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inquiry statistics
     */
    public function getStats() {
        try {
            $db = Database::getInstance();
            $stats = [];

            // Total inquiries
            $stmt = $db->query("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total'] = (int)$stmt->fetch()['total'];

            // New inquiries (last 30 days)
            $stmt = $db->query("SELECT COUNT(*) as new FROM {$this->table}
                                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats['new'] = (int)$stmt->fetch()['new'];

            // Status breakdown
            $stmt = $db->query("SELECT status, COUNT(*) as count FROM {$this->table}
                                     GROUP BY status");
            $stats['status_breakdown'] = $stmt->fetchAll();

            return $stats;

        } catch (\Exception $e) {
            error_log('Inquiry stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent inquiries for admin
     */
    public function getRecent($limit = 20) {
        try {
            $sql = "SELECT i.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} i
                    LEFT JOIN properties p ON i.property_id = p.id
                    LEFT JOIN users u ON i.user_id = u.id
                    ORDER BY i.created_at DESC LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Recent inquiries fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search inquiries
     */
    public function search($search_term, $limit = 50) {
        try {
            $sql = "SELECT i.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} i
                    LEFT JOIN properties p ON i.property_id = p.id
                    LEFT JOIN users u ON i.user_id = u.id
                    WHERE i.guest_name LIKE ?
                       OR i.guest_email LIKE ?
                       OR i.message LIKE ?
                       OR p.title LIKE ?
                    ORDER BY i.created_at DESC LIMIT ?";

            $search_pattern = "%{$search_term}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$search_pattern, $search_pattern, $search_pattern, $search_pattern, $limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Inquiry search error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete inquiry
     */
    public function deleteById($inquiry_id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$inquiry_id]);

        } catch (\Exception $e) {
            error_log('Inquiry deletion error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get inquiry by ID
     */
    public function getById($inquiry_id) {
        try {
            $sql = "SELECT i.*, p.title as property_title, p.city, p.state, p.price,
                           u.name as user_name, u.email as user_email, u.phone as user_phone
                    FROM {$this->table} i
                    LEFT JOIN properties p ON i.property_id = p.id
                    LEFT JOIN users u ON i.user_id = u.id
                    WHERE i.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inquiry_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Inquiry fetch by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get inquiries by status
     */
    public function getByStatus($status, $limit = 50) {
        try {
            $sql = "SELECT i.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} i
                    LEFT JOIN properties p ON i.property_id = p.id
                    LEFT JOIN users u ON i.user_id = u.id
                    WHERE i.status = ?
                    ORDER BY i.created_at DESC LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $limit]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Inquiries by status fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mark inquiry as responded
     */
    public function markAsResponded($inquiry_id) {
        return $this->updateStatus($inquiry_id, 'responded');
    }

    /**
     * Mark inquiry as closed
     */
    public function markAsClosed($inquiry_id) {
        return $this->updateStatus($inquiry_id, 'closed');
    }

    /**
     * Get inquiry trends
     */
    public function getTrends($days = 30) {
        try {
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                    FROM {$this->table}
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$days]);

            return $stmt->fetchAll();

        } catch (\Exception $e) {
            error_log('Inquiry trends error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export inquiries
     */
    public function export($format = 'csv', $conditions = []) {
        try {
            $sql = "SELECT i.*, p.title as property_title, p.city, p.state,
                           u.name as user_name, u.email as user_email
                    FROM {$this->table} i
                    LEFT JOIN properties p ON i.property_id = p.id
                    LEFT JOIN users u ON i.user_id = u.id";

            if (!empty($conditions)) {
                $where_parts = [];
                $params = [];

                foreach ($conditions as $field => $value) {
                    $where_parts[] = "{$field} = ?";
                    $params[] = $value;
                }

                $sql .= " WHERE " . implode(' AND ', $where_parts);
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            } else {
                $stmt = $this->db->query($sql);
            }

            $data = $stmt->fetchAll();

            if ($format === 'csv') {
                // Simple CSV generation
                $output = fopen('php://temp', 'r+');
                if (!empty($data)) {
                    fputcsv($output, array_keys($data[0]));
                    foreach ($data as $row) {
                        fputcsv($output, $row);
                    }
                }
                rewind($output);
                $csv = stream_get_contents($output);
                fclose($output);
                return $csv;
            } elseif ($format === 'json') {
                return json_encode($data, JSON_PRETTY_PRINT);
            }

            return $data;

        } catch (\Exception $e) {
            error_log('Inquiry export error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus($inquiry_ids, $status) {
        try {
            if (empty($inquiry_ids)) {
                return false;
            }

            $placeholders = str_repeat('?,', count($inquiry_ids) - 1) . '?';
            $sql = "UPDATE {$this->table}
                    SET status = ?, updated_at = NOW()
                    WHERE id IN ({$placeholders})";

            $params = array_merge([$status], $inquiry_ids);
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (\Exception $e) {
            error_log('Bulk inquiry status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get agent performance
     */
    public function getAgentPerformance($agent_id, $period = 30) {
        try {
            $sql = "SELECT
                        COUNT(*) as total_inquiries,
                        COUNT(CASE WHEN status = 'responded' THEN 1 END) as responded,
                        COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed,
                        AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_response_time
                    FROM {$this->table}
                    WHERE user_id = ?
                      AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$agent_id, $period]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Agent performance error: ' . $e->getMessage());
            return [];
        }
    }
}
