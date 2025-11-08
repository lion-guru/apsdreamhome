<?php
/**
 * CRM Lead Management Model
 * Handles customer relationship management and lead tracking
 */

use App\Models\Model;
use App\Core\Database;

class CRMLead extends Model {
    protected static string $table = 'crm_leads';

    /**
     * Lead status constants
     */
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL = 'proposal';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_CLOSED_WON = 'closed_won';
    const STATUS_CLOSED_LOST = 'closed_lost';

    /**
     * Lead source constants
     */
    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_ADVERTISING = 'advertising';
    const SOURCE_DIRECT = 'direct';
    const SOURCE_MLM = 'mlm';

    /**
     * Create new lead
     */
    public function createLead($lead_data) {
        try {
            $sql = "INSERT INTO {$this->table} (
                customer_name, customer_email, customer_phone, customer_city,
                lead_source, lead_status, property_interest, budget_range,
                preferred_contact_time, notes, assigned_to, priority,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $lead_data['customer_name'],
                $lead_data['customer_email'],
                $lead_data['customer_phone'],
                $lead_data['customer_city'] ?? null,
                $lead_data['lead_source'] ?? self::SOURCE_WEBSITE,
                $lead_data['lead_status'] ?? self::STATUS_NEW,
                $lead_data['property_interest'] ?? null,
                $lead_data['budget_range'] ?? null,
                $lead_data['preferred_contact_time'] ?? null,
                $lead_data['notes'] ?? null,
                $lead_data['assigned_to'] ?? null,
                $lead_data['priority'] ?? 'medium'
            ]);

            if ($success) {
                $lead_id = $this->db->lastInsertId();

                // Log lead creation activity
                $this->logLeadActivity($lead_id, 'created', 'Lead created in CRM system');

                return $lead_id;
            }

            return false;

        } catch (\Exception $e) {
            error_log('Lead creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update lead information
     */
    public function updateLead($lead_id, $update_data) {
        try {
            $allowed_fields = [
                'customer_name', 'customer_email', 'customer_phone', 'customer_city',
                'lead_source', 'lead_status', 'property_interest', 'budget_range',
                'preferred_contact_time', 'notes', 'assigned_to', 'priority'
            ];

            $update_fields = [];
            $params = [];

            foreach ($allowed_fields as $field) {
                if (isset($update_data[$field])) {
                    $update_fields[] = "{$field} = ?";
                    $params[] = $update_data[$field];
                }
            }

            if (empty($update_fields)) {
                return false;
            }

            $params[] = $lead_id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $update_fields) . ", updated_at = NOW() WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($params);

            if ($success) {
                // Log status change if status was updated
                if (isset($update_data['lead_status'])) {
                    $this->logLeadActivity($lead_id, 'status_changed', "Status changed to: {$update_data['lead_status']}");
                }

                return true;
            }

            return false;

        } catch (\Exception $e) {
            error_log('Lead update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get lead by ID
     */
    public function getLead($lead_id) {
        try {
            $sql = "SELECT l.*, u.name as assigned_agent_name, u.email as assigned_agent_email,
                           a.name as associate_name
                    FROM {$this->table} l
                    LEFT JOIN users u ON l.assigned_to = u.id
                    LEFT JOIN users a ON l.created_by_associate = a.id
                    WHERE l.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$lead_id]);

            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Lead fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get leads with filters and pagination
     */
    public function getLeads($filters = [], $page = 1, $per_page = 20) {
        try {
            $where_conditions = [];
            $params = [];
            $offset = ($page - 1) * $per_page;

            // Status filter
            if (isset($filters['status']) && !empty($filters['status'])) {
                $where_conditions[] = "l.lead_status = ?";
                $params[] = $filters['status'];
            }

            // Source filter
            if (isset($filters['source']) && !empty($filters['source'])) {
                $where_conditions[] = "l.lead_source = ?";
                $params[] = $filters['source'];
            }

            // Agent filter
            if (isset($filters['assigned_to']) && !empty($filters['assigned_to'])) {
                $where_conditions[] = "l.assigned_to = ?";
                $params[] = $filters['assigned_to'];
            }

            // Search filter
            if (isset($filters['search']) && !empty($filters['search'])) {
                $search_term = '%' . $filters['search'] . '%';
                $where_conditions[] = "(l.customer_name LIKE ? OR l.customer_email LIKE ? OR l.customer_phone LIKE ?)";
                $params = array_merge($params, [$search_term, $search_term, $search_term]);
            }

            // Date range filter
            if (isset($filters['date_from']) && !empty($filters['date_from'])) {
                $where_conditions[] = "l.created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (isset($filters['date_to']) && !empty($filters['date_to'])) {
                $where_conditions[] = "l.created_at <= ?";
                $params[] = $filters['date_to'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Get leads
            $sql = "SELECT l.*, u.name as assigned_agent_name, u.email as assigned_agent_email
                    FROM {$this->table} l
                    LEFT JOIN users u ON l.assigned_to = u.id
                    {$where_clause}
                    ORDER BY
                        CASE l.priority
                            WHEN 'high' THEN 1
                            WHEN 'medium' THEN 2
                            WHEN 'low' THEN 3
                        END,
                        l.created_at DESC
                    LIMIT ? OFFSET ?";

            $params[] = $per_page;
            $db = Database::getInstance();
            $stmt = $db->query($sql, $params);
            $leads = $stmt->fetchAll();

            // Get total count
            $count_sql = "SELECT COUNT(*) as total FROM {$this->table} l {$where_clause}";
            $count_params = array_slice($params, 0, -2);

            $db = Database::getInstance();
            $count_stmt = $db->query($count_sql, $count_params);
            $total_count = (int)$count_stmt->fetch()['total'];

            return [
                'leads' => $leads,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_pages' => ceil($total_count / $per_page),
                    'total_count' => $total_count
                ]
            ];

        } catch (\Exception $e) {
            error_log('Leads fetch error: ' . $e->getMessage());
            return ['leads' => [], 'pagination' => []];
        }
    }

    /**
     * Assign lead to agent
     */
    public function assignLead($lead_id, $agent_id) {
        try {
            $db = Database::getInstance();
            $sql = "UPDATE {$this->table} SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->query($sql, [$agent_id, $lead_id]);
            $success = $stmt !== false;

            if ($success) {
                $this->logLeadActivity($lead_id, 'assigned', "Assigned to agent ID: {$agent_id}");
                return true;
            }

            return false;

        } catch (\Exception $e) {
            error_log('Lead assignment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($lead_id, $new_status, $notes = '') {
        try {
            $db = Database::getInstance();
            $sql = "UPDATE {$this->table} SET lead_status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $db->query($sql, [$new_status, $lead_id]);
            $success = $stmt !== false;

            if ($success) {
                $this->logLeadActivity($lead_id, 'status_changed', "Status changed to: {$new_status}" . ($notes ? " - {$notes}" : ""));
                return true;
            }

            return false;

        } catch (\Exception $e) {
            error_log('Lead status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log lead activity
     */
    private function logLeadActivity($lead_id, $activity_type, $description) {
        try {
            $db = Database::getInstance();
            $sql = "INSERT INTO crm_lead_activities (lead_id, activity_type, description, created_by, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $stmt = $db->query($sql, [$lead_id, $activity_type, $description, $created_by]);
            return $stmt !== false;

        } catch (\Exception $e) {
            error_log('Lead activity logging error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get lead activities
     */
    public function getLeadActivities($lead_id) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT la.*, u.name as created_by_name
                    FROM crm_lead_activities la
                    LEFT JOIN users u ON la.created_by = u.id
                    WHERE la.lead_id = ?
                    ORDER BY la.created_at DESC";

            $stmt = $db->query($sql, [$lead_id]);
            return $stmt ? $stmt->fetchAll() : [];

        } catch (\Exception $e) {
            error_log('Lead activities fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate lead score based on various factors
     */
    public function calculateLeadScore($lead_id) {
        try {
            $lead = $this->getLead($lead_id);
            if (!$lead) {
                return 0;
            }

            $score = 0;

            // Budget score (higher budget = higher score)
            if ($lead['budget_range']) {
                $budget_ranges = [
                    'under_10L' => 10,
                    '10L_25L' => 20,
                    '25L_50L' => 30,
                    '50L_1Cr' => 40,
                    '1Cr_2Cr' => 50,
                    'above_2Cr' => 60
                ];
                $score += $budget_ranges[$lead['budget_range']] ?? 0;
            }

            // Source score
            $source_scores = [
                self::SOURCE_REFERRAL => 30,
                self::SOURCE_MLM => 25,
                self::SOURCE_DIRECT => 20,
                self::SOURCE_WEBSITE => 15,
                self::SOURCE_SOCIAL_MEDIA => 10,
                self::SOURCE_ADVERTISING => 5
            ];
            $score += $source_scores[$lead['lead_source']] ?? 0;

            // Status score (more advanced status = higher score)
            $status_scores = [
                self::STATUS_NEW => 10,
                self::STATUS_CONTACTED => 20,
                self::STATUS_QUALIFIED => 40,
                self::STATUS_PROPOSAL => 60,
                self::STATUS_NEGOTIATION => 80,
                self::STATUS_CLOSED_WON => 100,
                self::STATUS_CLOSED_LOST => 0
            ];
            $score += $status_scores[$lead['lead_status']] ?? 0;

            // Priority bonus
            if ($lead['priority'] === 'high') {
                $score += 20;
            } elseif ($lead['priority'] === 'medium') {
                $score += 10;
            }

            // Recency bonus (newer leads get higher score)
            $days_old = floor((time() - strtotime($lead['created_at'])) / 86400);
            if ($days_old <= 1) {
                $score += 20;
            } elseif ($days_old <= 7) {
                $score += 10;
            }

            return min($score, 100); // Cap at 100

        } catch (\Exception $e) {
            error_log('Lead scoring error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get lead statistics for dashboard
     */
    public function getLeadStats() {
        try {
            global $pdo;

            $stats = [];

            // Total leads by status
            $sql = "SELECT lead_status, COUNT(*) as count FROM {$this->table} GROUP BY lead_status";
            $stmt = $pdo->query($sql);
            $status_distribution = $stmt->fetchAll();
            $stats['status_distribution'] = $status_distribution;

            // Total leads by source
            $sql = "SELECT lead_source, COUNT(*) as count FROM {$this->table} GROUP BY lead_source";
            $stmt = $pdo->query($sql);
            $source_distribution = $stmt->fetchAll();
            $stats['source_distribution'] = $source_distribution;

            // Conversion rates
            $total_leads = array_sum(array_column($status_distribution, 'count'));
            $won_leads = 0;
            foreach ($status_distribution as $status) {
                if ($status['lead_status'] === self::STATUS_CLOSED_WON) {
                    $won_leads = $status['count'];
                    break;
                }
            }
            $stats['conversion_rate'] = $total_leads > 0 ? round(($won_leads / $total_leads) * 100, 2) : 0;

            // Average lead score
            $sql = "SELECT AVG(lead_score) as avg_score FROM {$this->table} WHERE lead_score > 0";
            $stmt = $pdo->query($sql);
            $stats['avg_lead_score'] = (float)($stmt->fetch()['avg_score'] ?? 0);

            // Recent activity (last 7 days)
            $sql = "SELECT COUNT(*) as recent_leads FROM {$this->table}
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $pdo->query($sql);
            $stats['recent_leads'] = (int)$stmt->fetch()['recent_leads'];

            return $stats;

        } catch (\Exception $e) {
            error_log('Lead stats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get leads assigned to specific agent
     */
    public function getAgentLeads($agent_id, $status = null) {
        try {
            $where_conditions = ["l.assigned_to = ?"];
            $params = [$agent_id];

            if ($status) {
                $where_conditions[] = "l.lead_status = ?";
                $params[] = $status;
            }

            $where_clause = implode(' AND ', $where_conditions);

            $sql = "SELECT l.*, u.name as assigned_agent_name
                    FROM {$this->table} l
                    LEFT JOIN users u ON l.assigned_to = u.id
                    WHERE {$where_clause}
                    ORDER BY
                        CASE l.priority
                            WHEN 'high' THEN 1
                            WHEN 'medium' THEN 2
                            WHEN 'low' THEN 3
                        END,
                        l.created_at DESC";

            $db = Database::getInstance();
            return $db->query($sql, $params)->fetchAll();

        } catch (\Exception $e) {
            error_log('Agent leads fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Bulk update lead status
     */
    public function bulkUpdateStatus($lead_ids, $new_status) {
        try {
            if (empty($lead_ids)) {
                return false;
            }

            $placeholders = str_repeat('?,', count($lead_ids) - 1) . '?';
            $sql = "UPDATE {$this->table} SET lead_status = ?, updated_at = NOW() WHERE id IN ({$placeholders})";

            $db = Database::getInstance();
            $params = array_merge([$new_status], $lead_ids);
            $stmt = $db->query($sql, $params);
            $success = $stmt !== false;

            if ($success) {
                // Log activity for each lead
                foreach ($lead_ids as $lead_id) {
                    $this->logLeadActivity($lead_id, 'bulk_status_update', "Bulk status update to: {$new_status}");
                }
                return true;
            }

            return false;

        } catch (\Exception $e) {
            error_log('Bulk status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get leads requiring follow-up
     */
    public function getFollowUpLeads($days_overdue = 3) {
        try {
            $sql = "SELECT l.*, u.name as assigned_agent_name,
                           DATEDIFF(NOW(), l.last_contact_date) as days_since_contact
                    FROM {$this->table} l
                    LEFT JOIN users u ON l.assigned_to = u.id
                    WHERE l.lead_status IN ('new', 'contacted', 'qualified')
                      AND (l.last_contact_date IS NULL OR l.last_contact_date <= DATE_SUB(NOW(), INTERVAL ? DAY))
                    ORDER BY l.last_contact_date ASC, l.priority DESC";

            $db = Database::getInstance();
            $stmt = $db->query($sql, [$days_overdue]);
            return $stmt ? $stmt->fetchAll() : [];

        } catch (\Exception $e) {
            error_log('Follow-up leads fetch error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update last contact date
     */
    public function updateLastContact($lead_id, $contact_notes = '') {
        try {
            $sql = "UPDATE {$this->table}
                    SET last_contact_date = NOW(), updated_at = NOW()
                    WHERE id = ?";

            $db = Database::getInstance();
            $stmt = $db->query($sql, [$lead_id]);
            $success = $stmt !== false;

            if ($success && !empty($contact_notes)) {
                $this->logLeadActivity($lead_id, 'contacted', $contact_notes);
            }

            return $success;

        } catch (\Exception $e) {
            error_log('Last contact update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get lead conversion funnel
     */
    public function getConversionFunnel() {
        try {
            global $pdo;

            $sql = "SELECT
                        COUNT(CASE WHEN lead_status = 'new' THEN 1 END) as new_leads,
                        COUNT(CASE WHEN lead_status = 'contacted' THEN 1 END) as contacted_leads,
                        COUNT(CASE WHEN lead_status = 'qualified' THEN 1 END) as qualified_leads,
                        COUNT(CASE WHEN lead_status = 'proposal' THEN 1 END) as proposal_leads,
                        COUNT(CASE WHEN lead_status = 'negotiation' THEN 1 END) as negotiation_leads,
                        COUNT(CASE WHEN lead_status = 'closed_won' THEN 1 END) as won_leads,
                        COUNT(CASE WHEN lead_status = 'closed_lost' THEN 1 END) as lost_leads
                    FROM {$this->table}";

            $stmt = $pdo->query($sql);
            return $stmt->fetch();

        } catch (\Exception $e) {
            error_log('Conversion funnel error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get agent performance metrics
     */
    public function getAgentPerformance($agent_id, $date_range = 30) {
        try {
            global $pdo;

            $sql = "SELECT
                        COUNT(*) as total_leads,
                        COUNT(CASE WHEN lead_status = 'closed_won' THEN 1 END) as won_leads,
                        COUNT(CASE WHEN lead_status = 'closed_lost' THEN 1 END) as lost_leads,
                        AVG(DATEDIFF(CLOSE_DATE, created_at)) as avg_conversion_time,
                        SUM(CASE WHEN lead_status = 'closed_won' THEN estimated_value ELSE 0 END) as total_value_won
                    FROM {$this->table}
                    WHERE assigned_to = ?
                      AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$agent_id, $date_range]);

            $performance = $stmt->fetch();

            // Calculate conversion rate
            $total_leads = $performance['total_leads'] ?? 0;
            $won_leads = $performance['won_leads'] ?? 0;
            $performance['conversion_rate'] = $total_leads > 0 ? round(($won_leads / $total_leads) * 100, 2) : 0;

            return $performance;

        } catch (\Exception $e) {
            error_log('Agent performance error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Export leads to CSV/Excel
     */
    public function exportLeads($format = 'csv', $filters = []) {
        try {
            $leads_data = $this->getLeads($filters, 1, 10000); // Get all leads for export

            if (empty($leads_data['leads'])) {
                return false;
            }

            $export_data = [];
            foreach ($leads_data['leads'] as $lead) {
                $export_data[] = [
                    'ID' => $lead['id'],
                    'Customer Name' => $lead['customer_name'],
                    'Email' => $lead['customer_email'],
                    'Phone' => $lead['customer_phone'],
                    'City' => $lead['customer_city'],
                    'Source' => $lead['lead_source'],
                    'Status' => $lead['lead_status'],
                    'Budget Range' => $lead['budget_range'],
                    'Property Interest' => $lead['property_interest'],
                    'Priority' => $lead['priority'],
                    'Assigned Agent' => $lead['assigned_agent_name'],
                    'Created Date' => $lead['created_at']
                ];
            }

            if ($format === 'csv') {
                // Simple CSV generation
                $output = fopen('php://temp', 'r+');
                if (!empty($export_data)) {
                    fputcsv($output, array_keys($export_data[0]));
                    foreach ($export_data as $row) {
                        fputcsv($output, $row);
                    }
                }
                rewind($output);
                $csv = stream_get_contents($output);
                fclose($output);
                return $csv;
            } elseif ($format === 'json') {
                return json_encode($export_data, JSON_PRETTY_PRINT);
            }

            return $export_data;

        } catch (\Exception $e) {
            error_log('Leads export error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get lead source analysis
     */
    public function getSourceAnalysis() {
        try {
            global $pdo;

            $sql = "SELECT
                        lead_source,
                        COUNT(*) as total_leads,
                        COUNT(CASE WHEN lead_status = 'closed_won' THEN 1 END) as converted_leads,
                        ROUND(AVG(lead_score), 2) as avg_score,
                        SUM(CASE WHEN lead_status = 'closed_won' THEN estimated_value ELSE 0 END) as total_value
                    FROM {$this->table}
                    GROUP BY lead_source
                    ORDER BY total_leads DESC";

            $stmt = $pdo->query($sql);
            $source_analysis = $stmt->fetchAll();

            // Calculate conversion rates
            foreach ($source_analysis as &$source) {
                $source['conversion_rate'] = $source['total_leads'] > 0 ?
                    round(($source['converted_leads'] / $source['total_leads']) * 100, 2) : 0;
            }

            return $source_analysis;

        } catch (\Exception $e) {
            error_log('Source analysis error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete lead (soft delete)
     */
    public function deleteLead($lead_id) {
        try {
            $sql = "UPDATE {$this->table} SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$lead_id]);

        } catch (\Exception $e) {
            error_log('Lead deletion error: ' . $e->getMessage());
            return false;
        }
    }
}
