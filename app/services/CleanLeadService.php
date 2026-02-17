<?php

namespace App\Services;

use App\Core\Database;

class CleanLeadService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get leads with filters
     */
    public function getLeads($filters = [])
    {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['search'])) {
                $where[] = "(leads.name LIKE ? OR leads.email LIKE ? OR leads.phone LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($filters['status'])) {
                $where[] = "leads.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['source'])) {
                $where[] = "leads.source = ?";
                $params[] = $filters['source'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $offset = ($filters['page'] - 1) * $filters['per_page'];
            $stmt = $this->db->prepare("
                SELECT leads.*, 
                       leads.property_interest as property_type,
                       lead_sources.name as source_name, 
                       lead_statuses.status_name as status_label,
                       users.name as assigned_to_name
                FROM leads
                LEFT JOIN lead_sources ON leads.source = lead_sources.id
                LEFT JOIN lead_statuses ON leads.status = lead_statuses.id
                LEFT JOIN users ON leads.assigned_to = users.id
                $whereClause
                ORDER BY leads.created_at DESC
                LIMIT $offset, {$filters['per_page']}
            ");
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead by ID
     */
    public function getLeadById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT leads.*, 
                       leads.property_interest as property_type,
                       lead_sources.name as source_name, 
                       lead_statuses.status_name as status_label,
                       users.name as assigned_to_name
                FROM leads
                LEFT JOIN lead_sources ON leads.source = lead_sources.id
                LEFT JOIN lead_statuses ON leads.status = lead_statuses.id
                LEFT JOIN users ON leads.assigned_to = users.id
                WHERE leads.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get lead activities
     */
    public function getLeadActivities($leadId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM lead_activities
                WHERE lead_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$leadId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead notes
     */
    public function getLeadNotes($leadId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM lead_notes
                WHERE lead_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$leadId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead files
     */
    public function getLeadFiles($leadId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM lead_files
                WHERE lead_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$leadId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead statistics
     */
    public function getLeadStats()
    {
        try {
            $stats = [];

            // Total leads by status
            $stmt = $this->db->query("
                SELECT lead_statuses.status_name as status, COUNT(leads.id) as count
                FROM leads
                LEFT JOIN lead_statuses ON leads.status = lead_statuses.id
                GROUP BY leads.status
            ");
            $stats['by_status'] = $stmt->fetchAll();

            // Total leads by source
            $stmt = $this->db->query("
                SELECT lead_sources.name as source, COUNT(leads.id) as count
                FROM leads
                LEFT JOIN lead_sources ON leads.source = lead_sources.id
                GROUP BY leads.source
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
    public function getSources()
    {
        try {
            $stmt = $this->db->query("SELECT id, name as source_name FROM lead_sources WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead statuses
     */
    public function getStatuses()
    {
        try {
            $stmt = $this->db->query("SELECT id, status_name FROM lead_statuses ORDER BY id ASC");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get assignable users
     */
    public function getAssignableUsers()
    {
        try {
            $stmt = $this->db->query("
                SELECT u.id, u.name, COUNT(l.id) as lead_count
                FROM users u
                LEFT JOIN leads l ON u.id = l.assigned_to
                WHERE u.status = 'active'
                GROUP BY u.id
                ORDER BY u.name ASC
            ");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create lead
     */
    public function createLead($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO leads (name, email, phone, source, status, priority, budget, property_interest, location_preference, notes, company, assigned_to, created_by, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['source'],
                $data['status'],
                $data['priority'],
                $data['budget'],
                $data['property_type'], // Maps to property_interest
                $data['location_preference'],
                $data['notes'],
                $data['company'] ?? null,
                $data['assigned_to'],
                $data['created_by']
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update lead
     */
    public function updateLead($id, $data)
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE leads SET
                 name = ?, email = ?, phone = ?, source = ?, status = ?, priority = ?,
                 budget = ?, property_interest = ?, location_preference = ?, notes = ?, company = ?, assigned_to = ?, updated_at = NOW()
                 WHERE id = ?"
            );
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['source'],
                $data['status'],
                $data['priority'],
                $data['budget'],
                $data['property_type'], // Maps to property_interest
                $data['location_preference'],
                $data['notes'],
                $data['company'] ?? null,
                $data['assigned_to'],
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add activity
     */
    public function addActivity($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, metadata, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['lead_id'],
                $data['activity_type'],
                $data['description'],
                $data['created_by'],
                $data['metadata'],
                $data['created_at']
            ]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add note
     */
    public function addNote($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO lead_notes (lead_id, note, created_by, created_at)
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$data['lead_id'], $data['note'], $data['created_by']]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Assign lead
     */
    public function assignLead($leadId, $userId)
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$userId, $leadId]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete lead
     */
    public function deleteLead($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM leads WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Convert to customer
     */
    public function convertToCustomer($leadId)
    {
        try {
            $lead = $this->getLeadById($leadId);

            if (!$lead) {
                return false;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO customers (name, email, phone, created_at)
                 VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$lead['name'], $lead['email'], $lead['phone']]);

            $customerId = $this->db->lastInsertId();

            if ($customerId) {
                // Update lead status to converted
                $stmt = $this->db->prepare("UPDATE leads SET status = 'converted', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$leadId]);
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
    public function generateReport($reportType, $dateRange)
    {
        try {
            switch ($reportType) {
                case 'summary':
                    $stmt = $this->db->prepare("
                        SELECT
                            COUNT(*) as total_leads,
                            COUNT(CASE WHEN status = 'new' THEN 1 END) as new_leads,
                            COUNT(CASE WHEN status = 'contacted' THEN 1 END) as contacted_leads,
                            COUNT(CASE WHEN status = 'qualified' THEN 1 END) as qualified_leads,
                            COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_leads
                        FROM leads
                        WHERE created_at BETWEEN ? AND ?
                    ");
                    $stmt->execute([$dateRange['start'], $dateRange['end']]);
                    break;

                default:
                    return [];
            }

            return $stmt->fetch();
        } catch (\Exception $e) {
            return [];
        }
    }
}
