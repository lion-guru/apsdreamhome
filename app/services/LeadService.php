<?php

namespace App\Services;

use App\Core\Database;

class LeadService
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get leads with filters
     */
    public function getLeads($filters = []) {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['source'])) {
                $where[] = "source = ?";
                $params[] = $filters['source'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $stmt = $this->db->query("
                SELECT * FROM leads
                $whereClause
                ORDER BY created_at DESC
                LIMIT $offset, {$filters['per_page']}
            ", $params);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead by ID
     */
    public function getLeadById($id) {
        try {
            $stmt = $this->db->query("SELECT * FROM leads WHERE id = ?", [$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get lead activities
     */
    public function getLeadActivities($leadId) {
        try {
            $stmt = $this->db->query("
                SELECT * FROM lead_activities
                WHERE lead_id = ?
                ORDER BY created_at DESC
            ", [$leadId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead notes
     */
    public function getLeadNotes($leadId) {
        try {
            $stmt = $this->db->query("
                SELECT * FROM lead_notes
                WHERE lead_id = ?
                ORDER BY created_at DESC
            ", [$leadId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead statistics
     */
    public function getLeadStats() {
        try {
            $stats = [];

            // Total leads by status
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM leads
                GROUP BY status
            ");
            $stats['by_status'] = $stmt->fetchAll();

            // Total leads by source
            $stmt = $this->db->query("
                SELECT source, COUNT(*) as count
                FROM leads
                GROUP BY source
            ");
            $stats['by_source'] = $stmt->fetchAll();

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead sources
     */
    public function getSources() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT source FROM leads WHERE source IS NOT NULL ORDER BY source");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead statuses
     */
    public function getStatuses() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT status FROM leads WHERE status IS NOT NULL ORDER BY status");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create lead
     */
    public function createLead($data) {
        try {
            $stmt = $this->db->query(
                "INSERT INTO leads (name, email, phone, source, status, priority, budget, property_type, location_preference, notes, assigned_to, created_by, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $data['name'], $data['email'], $data['phone'], $data['source'],
                    $data['status'], $data['priority'], $data['budget'], $data['property_type'],
                    $data['location_preference'], $data['notes'], $data['assigned_to'], $data['created_by']
                ]
            );
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update lead
     */
    public function updateLead($id, $data) {
        try {
            $stmt = $this->db->query(
                "UPDATE leads SET
                 name = ?, email = ?, phone = ?, source = ?, status = ?, priority = ?,
                 budget = ?, property_type = ?, location_preference = ?, notes = ?, assigned_to = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    $data['name'], $data['email'], $data['phone'], $data['source'],
                    $data['status'], $data['priority'], $data['budget'], $data['property_type'],
                    $data['location_preference'], $data['notes'], $data['assigned_to'], $id
                ]
            );
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add activity
     */
    public function addActivity($data) {
        try {
            $stmt = $this->db->query(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, metadata, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $data['lead_id'], $data['activity_type'], $data['description'],
                    $data['created_by'], $data['metadata'], $data['created_at']
                ]
            );
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add note
     */
    public function addNote($data) {
        try {
            $stmt = $this->db->query(
                "INSERT INTO lead_notes (lead_id, note, created_by, created_at)
                 VALUES (?, ?, ?, NOW())",
                [$data['lead_id'], $data['note'], $data['created_by']]
            );
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Assign lead
     */
    public function assignLead($leadId, $userId) {
        try {
            $stmt = $this->db->query(
                "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?",
                [$userId, $leadId]
            );
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Convert to customer
     */
    public function convertToCustomer($leadId) {
        try {
            $lead = $this->getLeadById($leadId);

            if (!$lead) {
                return false;
            }

            $stmt = $this->db->query(
                "INSERT INTO customers (name, email, phone, created_at)
                 VALUES (?, ?, ?, NOW())",
                [$lead['name'], $lead['email'], $lead['phone']]
            );

            $customerId = $this->db->lastInsertId();

            if ($customerId) {
                // Update lead status to converted
                $this->db->query("UPDATE leads SET status = 'converted', updated_at = NOW() WHERE id = ?", [$leadId]);
                return $customerId;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate report
     */
    public function generateReport($reportType, $dateRange) {
        try {
            switch ($reportType) {
                case 'summary':
                    $stmt = $this->db->query("
                        SELECT
                            COUNT(*) as total_leads,
                            COUNT(CASE WHEN status = 'new' THEN 1 END) as new_leads,
                            COUNT(CASE WHEN status = 'contacted' THEN 1 END) as contacted_leads,
                            COUNT(CASE WHEN status = 'qualified' THEN 1 END) as qualified_leads,
                            COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_leads
                        FROM leads
                        WHERE created_at BETWEEN ? AND ?
                    ", [$dateRange['start'], $dateRange['end']]);
                    break;

                default:
                    return [];
            }

            return $stmt->fetch();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get assignable users
     */
    public function getAssignableUsers() {
        try {
            $stmt = $this->db->query("SELECT id, name FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
