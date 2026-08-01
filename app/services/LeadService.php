<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class LeadService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
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

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : 'WHERE 1=1';
            $whereClause .= $this->tenantSql();
            if ($this->tenantId() > 1) $params[] = $this->tenantId();

            $page = max(1, (int)($filters['page'] ?? 1));
            $perPage = min(100, max(1, (int)($filters['per_page'] ?? 25)));
            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->query("
                SELECT * FROM leads
                $whereClause
                ORDER BY created_at DESC
                LIMIT $offset, $perPage
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
            $sql = "SELECT * FROM leads WHERE id = ?" . $this->tenantSql();
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
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
            $sql = "SELECT * FROM lead_activities WHERE lead_id = ?" . $this->tenantSql() . " ORDER BY created_at DESC";
            $params = [$leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
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
            $sql = "SELECT * FROM lead_notes WHERE lead_id = ?" . $this->tenantSql() . " ORDER BY created_at DESC";
            $params = [$leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
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
            $tenantSql = $this->tenantSql();

            // Total leads by status
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM leads
                WHERE 1=1 {$tenantSql}
                GROUP BY status
            ");
            $stats['by_status'] = $stmt->fetchAll();

            // Total leads by source
            $stmt = $this->db->query("
                SELECT source, COUNT(*) as count
                FROM leads
                WHERE 1=1 {$tenantSql}
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
            $stmt = $this->db->query("SELECT DISTINCT source FROM leads WHERE source IS NOT NULL" . $this->tenantSql() . " ORDER BY source");
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
            $stmt = $this->db->query("SELECT DISTINCT status FROM leads WHERE status IS NOT NULL" . $this->tenantSql() . " ORDER BY status");
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
            $insertData = $this->tenantInsertData();
            $columns = "name, email, phone, source, status, priority, budget, property_type, location_preference, notes, assigned_to, created_by, created_at";
            $values = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()";
            $params = [
                $data['name'], $data['email'], $data['phone'], $data['source'],
                $data['status'], $data['priority'], $data['budget'], $data['property_type'],
                $data['location_preference'], $data['notes'], $data['assigned_to'], $data['created_by']
            ];
            if (!empty($insertData)) {
                $columns .= ", " . implode(', ', array_keys($insertData));
                $values .= ", ?";
                $params = array_merge($params, array_values($insertData));
            }
            $stmt = $this->db->query(
                "INSERT INTO leads ($columns) VALUES ($values)",
                $params
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
            $sql = $this->tenantSql();
            $sql = "UPDATE leads SET
                 name = ?, email = ?, phone = ?, source = ?, status = ?, priority = ?,
                 budget = ?, property_type = ?, location_preference = ?, notes = ?, assigned_to = ?, updated_at = NOW()
                 WHERE id = ?" . $this->tenantSql();
            $params = [
                $data['name'], $data['email'], $data['phone'], $data['source'],
                $data['status'], $data['priority'], $data['budget'], $data['property_type'],
                $data['location_preference'], $data['notes'], $data['assigned_to'], $id
            ];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
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
            $insertData = $this->tenantInsertData();
            $columns = "lead_id, activity_type, description, created_by, metadata, created_at";
            $values = "?, ?, ?, ?, ?, NOW()";
            $params = [
                $data['lead_id'], $data['activity_type'], $data['description'],
                $data['created_by'], $data['metadata']
            ];
            if (!empty($insertData)) {
                $columns .= ", " . implode(', ', array_keys($insertData));
                $values .= ", ?";
                $params = array_merge($params, array_values($insertData));
            }
            $stmt = $this->db->query(
                "INSERT INTO lead_activities ($columns) VALUES ($values)",
                $params
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
            $insertData = $this->tenantInsertData();
            $columns = "lead_id, note, created_by, created_at";
            $values = "?, ?, NOW()";
            $params = [$data['lead_id'], $data['note'], $data['created_by']];
            if (!empty($insertData)) {
                $columns .= ", " . implode(', ', array_keys($insertData));
                $values .= ", ?";
                $params = array_merge($params, array_values($insertData));
            }
            $stmt = $this->db->query(
                "INSERT INTO lead_notes ($columns) VALUES ($values)",
                $params
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
            $sql = "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql();
            $params = [$userId, $leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
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

            // Generate temporary password and hash it
            $tempPassword = 'temp_' . rand(100000, 999999);
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Generate customer ID and referral code
            $customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referralCode = strtoupper(substr($lead['name'], 0, 3)) . date('ymd') . rand(100, 999);
            
            // Insert into users table first
            $tid = $this->getTenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $this->db->query(
                "INSERT INTO users (
                    name, email, phone, password, customer_id, referral_code,
                    role, status, kyc_status, mlm_position,
                    activity_logs_unified, is_newsletter_subscribed, is_promotional_subscribed,
                    rera_deduction_wallet, cumulative_sales, associate_payout_slab,
                    mlm_points, wallet_balance, mlm_rank, commission_rate,
                    mlm_target, experience_years, country, created_at, updated_at
                    $tenantCol
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                    $tenantVal
                )",
                [
                    $lead['name'],
                    $lead['email'],
                    $lead['phone'],
                    $hashedPassword,
                    $customerId,
                    $referralCode,
                    'customer',
                    'active',
                    'pending',
                    'none',
                    0,
                    1,
                    1,
                    0.00,
                    0.00,
                    '5%',
                    0,
                    0.00,
                    'Associate',
                    6.00,
                    1000000.00,
                    0,
                    'India'
                ]
            );
            
            // Get the newly inserted user ID
            $userId = $this->db->lastInsertId();
            
            // Update lead status to converted
            $sql = "UPDATE leads SET status = 'converted', updated_at = NOW() WHERE id = ?" . $this->tenantSql();
            $params = [$leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $this->db->query($sql, $params);
            return $userId;
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
                        WHERE created_at BETWEEN ? AND ? " . $this->tenantSql() . "
                    ", array_merge([$dateRange['start'], $dateRange['end']], $this->tenantId() > 1 ? [$this->tenantId()] : []));
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
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->db->query("SELECT id, name FROM users WHERE status = 'active' $tenantWhere ORDER BY name");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
