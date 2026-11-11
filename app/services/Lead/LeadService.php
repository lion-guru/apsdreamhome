<?php

namespace App\Services\Lead;


// NOTE (S449): this file is namespaced, so a bare PDO:: resolves to
// <this-namespace>\PDO -> "Class App\...\PDO not found" fatal. The
// custom autoloader does not fall back to the global PDO class.
use PDO;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

/**
 * Unified LeadService
 *
 * Consolidates all lead-related services from:
 *   - LeadService.php              (Lead CRUD, stats, conversion)
 *   - LeadScoringService.php       (Lead scoring, deal probability, priority leads)
 *   - LeadRoutingService.php       (Lead routing, smart assignment, stats)
 *   - LeadWorkflowService.php      (Per-user lead automation rules)
 *   - LeadFollowUpService.php      (Follow-up for incomplete registrations & new leads)
 *   - InquiryToLeadService.php     (Auto-wire inquiries into CRM leads)
 *   - LeadScoringAutoHook.php      (Legacy hooks for scoring on lead events)
 *
 * Single entry point for all lead operations:
 *   - Lead CRUD (create, read, update, delete, stats)
 *   - Lead scoring & deal probability
 *   - Lead routing & smart assignment
 *   - Lead workflow automation
 *   - Lead follow-up (email/SMS)
 *   - Inquiry to lead conversion
 *   - Lead lifecycle hooks (onCreate, onUpdate, onStatusChange, etc.)
 *
 * Backed by tables: leads, lead_activities, lead_notes, lead_scores,
 * lead_followups, site_visits, crm_routing_rules, lead_routing_log,
 * crm_assignments, incomplete_registrations
 */
class LeadService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================================================
    // LEAD CRUD (from LeadService)
    // ========================================================================

    public function getLeads($filters = [])
    {
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
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getLeadById($id)
    {
        try {
            $sql = "SELECT * FROM leads WHERE id = ?" . $this->tenantSql();
            $params = [$id];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetch();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getLeadActivities($leadId)
    {
        try {
            $sql = "SELECT * FROM lead_activities WHERE lead_id = ?" . $this->tenantSql() . " ORDER BY created_at DESC";
            $params = [$leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getLeadNotes($leadId)
    {
        try {
            $sql = "SELECT * FROM lead_notes WHERE lead_id = ?" . $this->tenantSql() . " ORDER BY created_at DESC";
            $params = [$leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getLeadStats()
    {
        try {
            $stats = [];
            $tenantSql = $this->tenantSql();

            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM leads
                WHERE 1=1 {$tenantSql}
                GROUP BY status
            ");
            $stats['by_status'] = $stmt->fetchAll();

            $stmt = $this->db->query("
                SELECT source, COUNT(*) as count
                FROM leads
                WHERE 1=1 {$tenantSql}
                GROUP BY source
            ");
            $stats['by_source'] = $stmt->fetchAll();

            return $stats;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getSources()
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT source FROM leads WHERE source IS NOT NULL" . $this->tenantSql() . " ORDER BY source");
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getStatuses()
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT status FROM leads WHERE status IS NOT NULL" . $this->tenantSql() . " ORDER BY status");
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function createLead($data)
    {
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
            $leadId = $this->db->lastInsertId();

            // Fire lifecycle hook: lead created
            if ($leadId) {
                $this->onLeadCreated((int)$leadId);
            }

            return $leadId;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function updateLead($id, $data)
    {
        try {
            $sql = "UPDATE leads SET
                 name = ?, email = ?, phone = ?, source = ?, status = ?, priority = ?,
                 budget = ?, location_preference = ?, notes = ?, assigned_to = ?, updated_at = NOW()
                 WHERE id = ?" . $this->tenantSql();
            $params = [
                $data['name'], $data['email'], $data['phone'], $data['source'],
                $data['status'], $data['priority'], $data['budget'],
                $data['location_preference'], $data['notes'], $data['assigned_to'], $id
            ];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);

            // Fire lifecycle hook: lead updated
            if ($stmt->rowCount() > 0) {
                $this->onLeadUpdated((int)$id);
                if (!empty($data['status'])) {
                    $this->onStatusChanged((int)$id, $data['status']);
                }
            }

            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function addActivity($data)
    {
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
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function addNote($data)
    {
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
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function assignLead($leadId, $userId)
    {
        try {
            $sql = "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql();
            $params = [$userId, $leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt = $this->db->query($sql, $params);
            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function convertToCustomer($leadId)
    {
        try {
            $lead = $this->getLeadById($leadId);

            if (!$lead) {
                return false;
            }

            $tempPassword = 'temp_' . rand(100000, 999999);
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

            $customerId = 'CUS' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referralCode = strtoupper(substr($lead['name'], 0, 3)) . date('ymd') . rand(100, 999);

            $tid = $this->getTenantId();
            $tenantCol = $tid > 1 ? ", tenant_id" : "";
            $tenantVal = $tid > 1 ? ", ?" : "";
            $this->db->query(
                "INSERT INTO users (
                    name, email, phone, password, customer_id, referral_code,
                    role, status, kyc_status, mlm_position,
                    rera_deduction_wallet, cumulative_sales, associate_payout_slab,
                    mlm_points, wallet_balance, mlm_rank, commission_rate,
                    mlm_target, created_at, updated_at
                    $tenantCol
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
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

            $userId = $this->db->lastInsertId();

            $sql = "UPDATE leads SET status = 'converted', updated_at = NOW() WHERE id = ?" . $this->tenantSql();
            $params = [$leadId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $this->db->query($sql, $params);

            // Fire lifecycle hook: status changed to converted
            $this->onStatusChanged((int)$leadId, 'converted');

            return $userId;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function generateReport($reportType, $dateRange)
    {
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
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getAssignableUsers()
    {
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $stmt = $this->db->query("SELECT id, name FROM users WHERE status = 'active' $tenantWhere ORDER BY name");
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ========================================================================
    // LEAD SCORING (from LeadScoringService)
    // ========================================================================

    public function calculateScore(int $leadId): array
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $factors = [
            'engagement' => $this->scoreEngagement($lead),
            'recency' => $this->scoreRecency($lead),
            'source_quality' => $this->scoreSourceQuality($lead),
            'budget_match' => $this->scoreBudgetMatch($lead),
            'interaction_velocity' => $this->scoreInteractionVelocity($lead),
            'demographics' => $this->scoreDemographics($lead),
        ];

        $weights = [
            'engagement' => 0.25,
            'recency' => 0.20,
            'source_quality' => 0.15,
            'budget_match' => 0.15,
            'interaction_velocity' => 0.15,
            'demographics' => 0.10,
        ];

        $totalScore = 0;
        foreach ($weights as $factor => $weight) {
            $totalScore += ($factors[$factor] ?? 0) * $weight;
        }

        $totalScore = round(min(100, max(0, $totalScore)));

        $tier = $this->getTier($totalScore);

        $this->saveScore($leadId, $totalScore, $factors, $tier);

        return [
            'success' => true,
            'lead_id' => $leadId,
            'score' => $totalScore,
            'tier' => $tier,
            'factors' => $factors,
            'weights' => $weights,
        ];
    }

    private function scoreEngagement(array $lead): float
    {
        $score = 0;

        $calls = $this->db->fetchColumn("SELECT COUNT(*) FROM lead_activities WHERE lead_id = ? AND activity_type = 'call'", [$lead['id']]) ?? 0;
        $score += min(25, $calls * 5);

        $messages = $this->db->fetchColumn("SELECT COUNT(*) FROM lead_activities WHERE lead_id = ? AND activity_type IN ('email','sms','whatsapp')", [$lead['id']]) ?? 0;
        $score += min(20, $messages * 3);

        $notes = $this->db->fetchColumn("SELECT COUNT(*) FROM lead_notes WHERE lead_id = ?", [$lead['id']]) ?? 0;
        $score += min(15, $notes * 5);

        $visits = $this->db->fetchColumn("SELECT COUNT(*) FROM site_visits WHERE lead_id = ?", [$lead['id']]) ?? 0;
        $score += min(20, $visits * 10);

        $followups = $this->db->fetchColumn("SELECT COUNT(*) FROM followups WHERE lead_id = ?", [$lead['id']]) ?? 0;
        $score += min(20, $followups * 4);

        return min(100, $score);
    }

    private function scoreRecency(array $lead): float
    {
        $lastActivity = $this->db->fetchColumn("
            SELECT MAX(created_at) FROM (
                SELECT created_at FROM lead_activities WHERE lead_id = ?
                UNION ALL
                SELECT created_at FROM lead_notes WHERE lead_id = ?
                UNION ALL
                SELECT created_at FROM followups WHERE lead_id = ?
                UNION ALL
                SELECT created_at FROM site_visits WHERE lead_id = ?
            ) AS combined
        ", [$lead['id'], $lead['id'], $lead['id'], $lead['id']]);

        if (!$lastActivity) return 10;

        $daysSince = (strtotime(date('Y-m-d')) - strtotime($lastActivity)) / 86400;

        if ($daysSince <= 1) return 100;
        if ($daysSince <= 3) return 90;
        if ($daysSince <= 7) return 75;
        if ($daysSince <= 14) return 60;
        if ($daysSince <= 30) return 40;
        if ($daysSince <= 60) return 20;
        return 10;
    }

    private function scoreSourceQuality(array $lead): float
    {
        $sourceScores = [
            'referral' => 90,
            'website' => 80,
            'walkin' => 85,
            'call' => 75,
            'social_media' => 60,
            'google' => 70,
            'facebook' => 65,
            'instagram' => 60,
            'justdial' => 55,
            'magicbricks' => 70,
            '99acres' => 70,
            'housing' => 65,
            'referral_partner' => 85,
            'exhibition' => 75,
            'newspaper' => 50,
            'hoarding' => 55,
            'sms' => 50,
            'email' => 55,
            'whatsapp' => 65,
            'portal' => 60,
            'inquiry' => 60,
        ];

        $source = strtolower($lead['source'] ?? 'unknown');
        return $sourceScores[$source] ?? 50;
    }

    private function scoreBudgetMatch(array $lead): float
    {
        $budget = $lead['budget'] ?? 0;
        if (!$budget && !empty($lead['budget_range'])) {
            $budget = (float) preg_replace('/[^0-9.]/', '', $lead['budget_range']);
        }
        if (!$budget) return 50;

        $range = $budget * 0.3;
        $minBudget = $budget - $range;
        $maxBudget = $budget + $range;

        $count = $this->db->fetchColumn("
            SELECT COUNT(*) FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE p.status = 'available'
            AND p.base_price_per_sqft BETWEEN ? AND ?
        ", [$minBudget, $maxBudget]);

        if ($count >= 10) return 95;
        if ($count >= 5) return 80;
        if ($count >= 2) return 65;
        if ($count >= 1) return 50;
        return 30;
    }

    private function scoreInteractionVelocity(array $lead): float
    {
        $activities = $this->db->fetchAll("
            SELECT activity_type, created_at FROM lead_activities
            WHERE lead_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY created_at ASC
        ", [$lead['id']]);

        if (empty($activities)) return 20;

        $responseTimes = [];
        $lastContact = null;

        foreach ($activities as $activity) {
            if ($lastContact) {
                $diff = (strtotime($activity['created_at']) - strtotime($lastContact)) / 3600;
                $responseTimes[] = $diff;
            }
            $lastContact = $activity['created_at'];
        }

        if (empty($responseTimes)) return 50;

        $avgHours = array_sum($responseTimes) / count($responseTimes);

        if ($avgHours <= 1) return 100;
        if ($avgHours <= 4) return 85;
        if ($avgHours <= 12) return 70;
        if ($avgHours <= 24) return 55;
        if ($avgHours <= 48) return 40;
        return 25;
    }

    private function scoreDemographics(array $lead): float
    {
        $score = 0;

        if (!empty($lead['name'])) $score += 15;
        if (!empty($lead['phone'])) $score += 20;
        if (!empty($lead['email'])) $score += 15;
        if (!empty($lead['budget']) || !empty($lead['budget_max'])) $score += 20;
        if (!empty($lead['preferred_location']) || !empty($lead['city'])) $score += 15;
        if (!empty($lead['property_type'])) $score += 15;

        return min(100, $score);
    }

    private function getTier(int $score): string
    {
        if ($score >= 80) return 'Hot';
        if ($score >= 60) return 'Warm';
        if ($score >= 40) return 'Cool';
        return 'Cold';
    }

    private function saveScore(int $leadId, int $score, array $factors, string $tier): void
    {
        // lead_scores has no UNIQUE(lead_id) — each calculation is a history row
        $this->db->execute("
            INSERT INTO lead_scores (lead_id, score, score_details, calculated_at)
            VALUES (?, ?, ?, NOW())
        ", [$leadId, $score, json_encode($factors)]);
    }

    public function getDealProbability(int $leadId): array
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $scoreResult = $this->calculateScore($leadId);
        if (!$scoreResult['success']) return $scoreResult;

        $score = $scoreResult['score'];
        $baseProbability = $score / 100;

        $stageMultipliers = [
            'new' => 1.0,
            'contacted' => 1.1,
            'qualified' => 1.25,
            'site_visit' => 1.4,
            'proposal' => 1.6,
            'negotiation' => 1.8,
            'closed_won' => 2.0,
            'closed_lost' => 0,
            'nurture' => 0.8,
        ];

        $stage = $lead['status'] ?? 'new';
        $multiplier = $stageMultipliers[$stage] ?? 1.0;

        $probability = min(95, round($baseProbability * $multiplier * 100));

        $similarClosed = $this->db->fetchColumn("
            SELECT COUNT(*) FROM leads l
            LEFT JOIN plot_bookings pb ON pb.lead_id = l.id
            WHERE l.status = 'closed_won'
            AND l.budget BETWEEN ? AND ?
            AND pb.id IS NOT NULL
        ", [$lead['budget'] * 0.7, $lead['budget'] * 1.3]) ?? 0;

        $totalSimilar = $this->db->fetchColumn("
            SELECT COUNT(*) FROM leads
            WHERE budget BETWEEN ? AND ?
        ", [$lead['budget'] * 0.7, $lead['budget'] * 1.3]) ?? 1;

        $historicalRate = $totalSimilar > 0 ? ($similarClosed / $totalSimilar) * 100 : 0;

        $finalProbability = round(($probability * 0.7) + ($historicalRate * 0.3));

        return [
            'success' => true,
            'lead_id' => $leadId,
            'deal_probability' => $finalProbability,
            'score' => $scoreResult['score'],
            'tier' => $scoreResult['tier'],
            'stage' => $stage,
            'historical_conversion_rate' => round($historicalRate, 1),
            'similar_deals_closed' => $similarClosed,
            'total_similar_leads' => $totalSimilar,
        ];
    }

    public function bulkScore(array $leadIds, int $batchSize = 50): array
    {
        $results = [];
        $processed = 0;
        $failed = 0;

        foreach (array_chunk($leadIds, $batchSize) as $chunk) {
            foreach ($chunk as $leadId) {
                $result = $this->calculateScore($leadId);
                if ($result['success']) {
                    $results[] = ['lead_id' => $leadId, 'score' => $result['score'], 'tier' => $result['tier']];
                    $processed++;
                } else {
                    $results[] = ['lead_id' => $leadId, 'error' => $result['error']];
                    $failed++;
                }
            }
        }

        return [
            'success' => true,
            'processed' => $processed,
            'failed' => $failed,
            'results' => $results,
        ];
    }

    public function getPriorityLeads(int $limit = 20, int $associateId = null): array
    {
        $where = "ls.score >= 70 AND l.deleted_at IS NULL";
        $params = [$limit];

        if ($associateId) {
            $where .= " AND (l.assigned_to = ? OR l.created_by = ?)";
            $params = array_merge([$associateId, $associateId], $params);
        }

        $sql = "
            SELECT l.id, l.name, l.phone, l.email, l.status, l.source, l.budget,
                   ls.score, ls.score_details as factors,
                   MAX(la.created_at) as last_activity
            FROM leads l
            JOIN lead_scores ls ON ls.lead_id = l.id
            LEFT JOIN lead_activities la ON la.lead_id = l.id
            WHERE $where
            GROUP BY l.id
            ORDER BY ls.score DESC, last_activity ASC
            LIMIT ?
        ";

        $results = $this->db->fetchAll($sql, $params);

        foreach ($results as &$row) {
            if ($row['last_activity']) {
                $row['days_since_activity'] = round((time() - strtotime($row['last_activity'])) / 86400);
            } else {
                $row['days_since_activity'] = 999;
            }
        }

        return ['success' => true, 'leads' => $results];
    }

    // ========================================================================
    // LEAD ROUTING (from LeadRoutingService)
    // ========================================================================

    public function getAllRules(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT rr.*, d.name as department_name, u.name as target_user_name
                 FROM crm_routing_rules rr
                 LEFT JOIN departments d ON d.id = rr.target_department_id
                 LEFT JOIN users u ON u.id = rr.target_user_id
                 " . $this->tenantSqlForAlias('rr') . "
                 ORDER BY rr.priority ASC, rr.created_at DESC"
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('Silent catch: ' . $e->getMessage());
            return [];
        }
    }

    public function getActiveRules(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM crm_routing_rules WHERE is_active = 1" . $this->tenantSql()
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('Silent catch: ' . $e->getMessage());
            return [];
        }
    }

    public function getRuleById(int $id): ?array
    {
        try {
            return $this->db->fetch("SELECT * FROM crm_routing_rules WHERE id = ?" . $this->tenantSql(), array_merge([$id], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        } catch (\Throwable $e) {
            error_log('Silent catch: ' . $e->getMessage());
            return null;
        }
    }

    public function createRule(array $data): array
    {
        try {
            $insertCols = array_merge(['name', 'source_pattern', 'city_pattern', 'min_budget', 'max_budget', 'target_department_id', 'target_user_id', 'priority', 'is_active'], array_keys($this->tenantInsertData()));
            $insertVals = array_merge([$data['name'] ?? '', $data['source_pattern'] ?? '*', $data['city_pattern'] ?? '*', $data['min_budget'] ?? 0, $data['max_budget'] ?? 0, $data['target_department_id'] ?? null, $data['target_user_id'] ?? null, $data['priority'] ?? 100, $data['is_active'] ?? 1], array_values($this->tenantInsertData()));
            $placeholders = implode(',', array_fill(0, count($insertCols), '?'));

            $this->db->query(
                "INSERT INTO crm_routing_rules (" . implode(', ', $insertCols) . ") VALUES (" . $placeholders . ")",
                $insertVals
            );
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateRule(int $id, array $data): array
    {
        try {
            $sets = [];
            $params = [];
            $allowed = ['name', 'source_pattern', 'city_pattern', 'min_budget', 'max_budget', 'target_department_id', 'target_user_id', 'priority', 'is_active'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) {
                    $sets[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($sets)) return ['success' => false, 'error' => 'No fields to update'];
            $sets[] = "updated_at = NOW()";
            $params[] = $id;

            $this->db->query("UPDATE crm_routing_rules SET " . implode(', ', $sets) . " " . $this->tenantSql() . " AND id = ?", array_merge($params, $this->tenantId() > 1 ? [$this->tenantId()] : []));
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteRule(int $id): array
    {
        try {
            $this->db->query("DELETE FROM crm_routing_rules WHERE id = ?" . $this->tenantSql(), array_merge([$id], $this->tenantId() > 1 ? [$this->tenantId()] : []));
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function routeLead(int $leadId): ?array
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?" . $this->tenantSql(), array_merge([$leadId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        if (!$lead) return null;

        $rules = $this->getActiveRules();
        if (empty($rules)) return null;

        foreach ($rules as $rule) {
            if ($this->matchesRule($lead, $rule)) {
                $this->applyRouting($leadId, $rule);
                return $rule;
            }
        }
        return null;
    }

    private function matchesRule(array $lead, array $rule): bool
    {
        $sourcePattern = strtolower(trim($rule['source_pattern'] ?? '*'));
        $leadSource = strtolower($lead['source'] ?? '');
        if ($sourcePattern !== '*' && $sourcePattern !== '') {
            $patterns = array_map('trim', explode(',', $sourcePattern));
            $matched = false;
            foreach ($patterns as $p) {
                if ($p === $leadSource || fnmatch($p, $leadSource, FNM_CASEFOLD)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) return false;
        }

        $cityPattern = strtolower(trim($rule['city_pattern'] ?? '*'));
        $leadCity = strtolower($lead['city'] ?? '');
        if ($cityPattern !== '*' && $cityPattern !== '') {
            $patterns = array_map('trim', explode(',', $cityPattern));
            $matched = false;
            foreach ($patterns as $p) {
                if ($p === $leadCity || fnmatch($p, $leadCity, FNM_CASEFOLD)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) return false;
        }

        $budget = (float)($lead['budget'] ?? 0);
        $minBudget = (float)($rule['min_budget'] ?? 0);
        $maxBudget = (float)($rule['max_budget'] ?? 0);
        if ($minBudget > 0 && $budget < $minBudget) return false;
        if ($maxBudget > 0 && $budget > $maxBudget) return false;

        return true;
    }

    private function applyRouting(int $leadId, array $rule): void
    {
        $targetUserId = $rule['target_user_id'] ?? null;

        if (!$targetUserId && !empty($rule['target_department_id'])) {
            $targetUserId = $this->getLeastLoadedUser((int)$rule['target_department_id']);
        }

        if ($targetUserId) {
            $this->assignLead($leadId, (int)$targetUserId);
        }

        try {
            $this->db->query(
                "INSERT INTO lead_routing_log (lead_id, rule_id, target_department_id, target_user_id, routed_at" . (count($this->tenantInsertData()) > 0 ? ', tenant_id' : '') . ")
                 VALUES (?, ?, ?, ?, NOW()" . (count($this->tenantInsertData()) > 0 ? ', ?' : '') . ")",
                array_merge([$leadId, $rule['id'], $rule['target_department_id'] ?? null, $targetUserId], array_values($this->tenantInsertData()))
            );
        } catch (\Throwable $e) {
            error_log('LeadService::applyRouting log error: ' . $e->getMessage());
        }
    }

    private function getLeastLoadedUser(int $departmentId): ?int
    {
        try {
            $row = $this->db->fetch(
                "SELECT u.id, COUNT(l.id) as lead_count
                 FROM users u
                 LEFT JOIN leads l ON l.assigned_to = u.id AND l.tenant_id = u.tenant_id AND l.deleted_at IS NULL
                 WHERE u.deleted_at IS NULL" . $this->tenantSqlForAlias('u') . "
                   AND EXISTS (SELECT 1 FROM employee_profiles emp JOIN departments d ON d.name = emp.department WHERE emp.user_id = u.id AND d.id = ? AND emp.tenant_id = u.tenant_id)
                 GROUP BY u.id
                 ORDER BY lead_count ASC
                 LIMIT 1",
                [$departmentId]
            );
            return $row ? (int)$row['id'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getRoutingStats(): array
    {
        try {
            $totalRules = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM crm_routing_rules" . $this->tenantSql())['cnt'];
            $activeRules = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM crm_routing_rules WHERE is_active = 1" . $this->tenantSql())['cnt'];
            $routedToday = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM lead_routing_log WHERE DATE(routed_at) = CURDATE()" . $this->tenantSql())['cnt'];
            $routedTotal = (int)$this->db->fetch("SELECT COUNT(*) as cnt FROM lead_routing_log" . $this->tenantSql())['cnt'];

            $topRules = $this->db->fetchAll(
                "SELECT rr.name, COUNT(rl.id) as route_count
                 FROM crm_routing_rules rr
                 LEFT JOIN lead_routing_log rl ON rl.rule_id = rr.id" . $this->tenantSqlForAlias('rr') . "
                 GROUP BY rr.id ORDER BY route_count DESC LIMIT 5"
            ) ?: [];

            return [
                'total_rules' => $totalRules,
                'active_rules' => $activeRules,
                'routed_today' => $routedToday,
                'routed_total' => $routedTotal,
                'top_rules' => $topRules,
            ];
        } catch (\Throwable $e) {
            return ['total_rules' => 0, 'active_rules' => 0, 'routed_today' => 0, 'routed_total' => 0, 'top_rules' => []];
        }
    }

    public function assignLeadSmart(int $leadId, array $options = []): array
    {
        try {
            $tid = (int)$this->tenantId();
            $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $strategy = $options['strategy'] ?? $this->getSetting('crm_lead_assignment_strategy', 'round_robin', $tid);
            $requireAttendance = $this->getSetting('crm_require_attendance', '0', $tid);

            $query = "SELECT u.id as user_id, u.role as user_role
                      FROM users u
                      WHERE u.role IN ('agent', 'telecaller', 'associate') AND u.status = 'active'";

            $params = [];
            if ($tid > 1) {
                $query .= " AND u.tenant_id = ?";
                $params[] = $tid;
            }

            $requireAttendance = (bool)$requireAttendance;
            if ($requireAttendance) {
                $today = date('Y-m-d');
                $query .= " AND EXISTS (
                                SELECT 1 FROM employee_attendance a
                                JOIN employee_profiles e ON a.employee_id = e.id
                                WHERE e.user_id = u.id
                                AND a.attendance_date = ?
                                AND a.check_in_time IS NOT NULL
                                AND a.check_out_time IS NULL
                            )";
                $params[] = $today;
            }

            $query .= " ORDER BY u.name";
            $assignees = $this->db->fetchAll($query, $params);

            if (empty($assignees)) {
                return ['success' => false, 'message' => 'No available agents/telecallers/associates found'];
            }

            $assignedUserId = null;

            if ($strategy === 'least_burdened') {
                $minLeads = null;
                foreach ($assignees as $a) {
                    $userId = $a['user_id'];
                    $q = "SELECT COUNT(*) as cnt FROM leads WHERE assigned_to = ? AND status NOT IN ('converted', 'dead', 'closed', 'lost')";
                    $p = [$userId];
                    if ($tid > 1) {
                        $q .= " AND tenant_id = ?";
                        $p[] = $tid;
                    }
                    $activeCount = (int)($this->db->fetchOne($q, $p)['cnt'] ?? 0);

                    if ($minLeads === null || $activeCount < $minLeads) {
                        $minLeads = $activeCount;
                        $assignedUserId = $userId;
                    }
                }
            } elseif ($strategy === 'smart_match') {
                $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?" . $this->tenantSql(), array_merge([$leadId], $this->tenantId() > 1 ? [$this->tenantId()] : []));

                $candidates = [];
                foreach ($assignees as $assignee) {
                    $score = $this->calculateSmartMatchScore($assignee, $lead);
                    $candidates[] = array_merge($assignee, ['match_score' => $score]);
                }

                usort($candidates, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
                $assignedUserId = $candidates[0]['user_id'] ?? null;
            } else {
                $oldestTime = null;
                foreach ($assignees as $a) {
                    $userId = $a['user_id'];
                    $q = "SELECT MAX(created_at) as last_assigned FROM crm_assignments WHERE assigned_to = ?";
                    $p = [$userId];
                    if ($tid > 1) {
                        $q .= " AND tenant_id = ?";
                        $p[] = $tid;
                    }
                    $lastAssigned = $this->db->fetchOne($q, $p)['last_assigned'] ?? '2000-01-01 00:00:00';

                    if ($oldestTime === null || strtotime($lastAssigned) < strtotime($oldestTime)) {
                        $oldestTime = $lastAssigned;
                        $assignedUserId = $userId;
                    }
                }
            }

            if ($assignedUserId) {
                $tid = $this->tenantId();
                $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
                $tenantParams = $tid > 1 ? [$tid] : [];

                $leadRow = $this->db->fetchOne("SELECT assigned_to FROM leads WHERE id = ?" . $tenantSql, array_merge([$leadId], $tenantParams));
                $fromUserId = $leadRow ? ($leadRow['assigned_to'] ?? null) : null;

                $q = "UPDATE leads SET assigned_to = ?, updated_at = NOW() WHERE id = ?";
                $p = [$assignedUserId, $leadId];
                if ($tid > 1) {
                    $q .= " AND tenant_id = ?";
                    $p[] = $tid;
                }
                $this->db->query($q, $p);

                try {
                    $cols = "lead_id, assigned_from, assigned_to, assigned_by, reason, is_active, tenant_id";
                    $vals = "?, ?, ?, ?, 'auto_assign', 1, ?";
                    $assignedBy = $fromUserId ?? $assignedUserId;
                    if (empty($assignedBy)) $assignedBy = $assignedUserId ?: 1;
                    $logParams = [$leadId, $fromUserId, $assignedUserId, $assignedBy, $tid > 1 ? $tid : 1];
                    $this->db->query("INSERT INTO crm_assignments ($cols) VALUES ($vals)", $logParams);
                } catch (\Throwable $e) {
                    error_log("Failed to log lead assignment: " . $e->getMessage());
                }

                return ['success' => true, 'assigned_to' => $assignedUserId, 'message' => 'Lead assigned successfully'];
            }

            return ['success' => false, 'message' => 'Failed to determine assignee'];

        } catch (\Throwable $e) {
            error_log("LeadService::assignLeadSmart error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal assignment error'];
        }
    }

    private function calculateSmartMatchScore(array $associate, array $leadData): float
    {
        $score = 0;

        $capacity = $associate['max_leads_capacity'] ?? 50;
        $current = $associate['current_leads_count'] ?? 0;
        $utilization = $capacity > 0 ? ($current / $capacity) : 0;
        $workloadScore = max(0, 100 - ($utilization * 100));
        $score += $workloadScore * 0.30;

        $specScore = $this->calculateSpecializationMatch($associate, $leadData);
        $score += $specScore * 0.25;

        $locationScore = $this->calculateLocationMatch($associate, $leadData);
        $score += $locationScore * 0.20;

        $budgetScore = $this->calculateBudgetMatch($associate, $leadData);
        $score += $budgetScore * 0.15;

        $perfScore = $associate['performance_score'] ?? 50;
        $score += ($perfScore / 100) * 100 * 0.10;

        return round($score, 1);
    }

    private function calculateSpecializationMatch(array $associate, array $leadData): float
    {
        $specs = $associate['specializations'] ?? '';
        if (empty($specs)) return 50;

        $specList = array_map('trim', explode(',', strtolower($specs)));
        $score = 0;
        $matches = 0;

        if (!empty($leadData['property_type'])) {
            $propType = strtolower($leadData['property_type']);
            foreach ($specList as $spec) {
                if (stripos($spec, $propType) !== false || stripos($propType, $spec) !== false) {
                    $matches++;
                    $score += 30;
                }
            }
        }

        if (!empty($leadData['budget']) || !empty($leadData['budget_max'])) {
            $budget = $leadData['budget'] ?? $leadData['budget_max'] ?? 0;
            foreach ($specList as $spec) {
                if (stripos($spec, 'luxury') !== false && $budget > 10000000) $matches += 30;
                if (stripos($spec, 'affordable') !== false && $budget <= 5000000) $matches += 30;
                if (stripos($spec, 'mid') !== false && $budget > 5000000 && $budget <= 10000000) $matches += 30;
            }
        }

        if (!empty($leadData['city']) || !empty($leadData['preferred_location'])) {
            $location = strtolower($leadData['city'] ?? $leadData['preferred_location'] ?? '');
            foreach ($specList as $spec) {
                if (stripos($spec, $location) !== false) {
                    $matches += 20;
                }
            }
        }

        return min(100, max(0, 50 + ($matches * 10)));
    }

    private function calculateLocationMatch(array $associate, array $leadData): float
    {
        $assocLocations = array_map('trim', explode(',', strtolower($associate['preferred_locations'] ?? '')));
        $leadLocation = strtolower($leadData['city'] ?? $leadData['location'] ?? '');

        if (empty($assocLocations) || empty($leadLocation)) return 50;

        if (in_array($leadLocation, $assocLocations)) return 100;

        foreach ($assocLocations as $loc) {
            if (strpos($leadLocation, $loc) !== false || strpos($loc, $leadLocation) !== false) {
                return 75;
            }
        }

        return 25;
    }

    private function calculateBudgetMatch(array $associate, array $leadData): float
    {
        if (empty($leadData['budget']) && empty($leadData['budget_max'])) return 50;

        $budget = $leadData['budget'] ?? $leadData['budget_max'] ?? 0;
        $prefMin = (float)($associate['preferred_budget_min'] ?? 0);
        $prefMax = (float)($associate['preferred_budget_max'] ?? 0);

        if ($prefMin === 0 && $prefMax === 0) return 50;

        if ($budget >= $prefMin && ($prefMax === 0 || $budget <= $prefMax)) {
            return 100;
        }

        $distance = 0;
        if ($budget < $prefMin) $distance = $prefMin - $budget;
        elseif ($prefMax > 0 && $budget > $prefMax) $distance = $budget - $prefMax;

        $range = max($prefMax - $prefMin, $prefMax, $budget);
        return max(0, 100 - ($distance / max($range, 1) * 100));
    }

    public function getAvailableAssignees(array $filters = []): array
    {
        $tid = (int)$this->tenantId();
        $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $query = "SELECT u.id as user_id, u.role as user_role, u.name, u.email, u.phone,
                         e.max_leads_capacity, e.current_leads_count, e.specializations,
                         e.preferred_locations, e.preferred_budget_min, e.preferred_budget_max,
                         e.performance_score, e.response_time_avg_hours
                    FROM users u
                    JOIN associate_profiles a ON a.user_id = u.id
                    WHERE u.role IN ('agent', 'telecaller', 'associate') AND u.status = 'active'
                    AND a.status = 'active'
                    AND (a.max_leads_capacity IS NULL OR a.current_leads_count < a.max_leads_capacity)";

        $params = [];
        if ($tid > 1) {
            $query .= " AND u.tenant_id = ?";
            $params[] = $tid;
        }

        $query .= " ORDER BY a.performance_score DESC, a.current_leads_count ASC";

        return $this->db->fetchAll($query, array_merge($tenantParams, $params));
    }

    public function getAssignmentStats(): array
    {
        $tid = (int)$this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.role,
                   COUNT(l.id) as active_leads,
                   e.max_leads_capacity
            FROM users u
            JOIN associate_profiles e ON e.user_id = u.id
            LEFT JOIN leads l ON l.assigned_to = u.id AND l.status NOT IN ('converted', 'dead', 'closed', 'lost') AND l.deleted_at IS NULL
            WHERE u.role IN ('agent', 'telecaller', 'associate') AND u.status = 'active'
            AND e.status = 'active' $tenantSql
            GROUP BY u.id
            ORDER BY active_leads ASC
        ");
        $stmt->execute($tenantParams);
        $assignees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalActive = array_sum(array_column($assignees, 'active_leads'));
        $totalCapacity = array_sum(array_map(fn($a) => $a['max_leads_capacity'] ?? 50, $assignees));
        $utilization = $totalCapacity > 0 ? round(($totalActive / $totalCapacity) * 100, 1) : 0;

        return [
            'assignees' => $assignees,
            'total_active_leads' => $totalActive,
            'total_capacity' => $totalCapacity,
            'utilization_pct' => $utilization,
            'available_assignees' => count(array_filter($assignees, fn($a) => ($a['max_leads_capacity'] ?? 50) > $a['active_leads'])),
        ];
    }

    // ========================================================================
    // LEAD WORKFLOW (from LeadWorkflowService)
    // ========================================================================

    public function handleWorkflow($trigger, $leadId, $userId, array $context = [])
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$userId, $trigger];
        if ($tid > 1) $params[] = $tid;
        $rules = $this->db->fetchAll(
            "SELECT * FROM workflow_rules WHERE user_id = ? AND `trigger` = ? AND is_active = 1{$tidSql} ORDER BY id ASC",
            $params
        ) ?: [];
        if (empty($rules)) return 0;

        $lead = $this->ownedLead($leadId, $userId, $tid, $tidSql);
        if (!$lead) return 0;

        $done = 0;
        foreach ($rules as $rule) {
            if (!$this->workflowConditionsMatch($rule, $lead, $context)) continue;
            if ($this->runWorkflowAction($rule, $lead, $userId, $tid, $context)) $done++;
            $lead = $this->ownedLead($leadId, $userId, $tid, $tidSql);
            if (!$lead) break;
        }
        return $done;
    }

    private function ownedLead($leadId, $userId, $tid, $tidSql)
    {
        $params = [$leadId, $userId, $userId];
        if ($tid > 1) $params[] = $tid;
        return $this->db->fetchOne(
            "SELECT * FROM leads WHERE id = ? AND (assigned_to = ? OR created_by = ?){$tidSql} LIMIT 1",
            $params
        );
    }

    private function workflowConditionsMatch($rule, $lead, $context)
    {
        $conds = json_decode($rule['conditions'] ?? '', true);
        if (!is_array($conds) || empty($conds)) return true;
        if (!empty($conds['status']) && !in_array($lead['status'] ?? '', (array)$conds['status'], true)) return false;
        if (!empty($conds['source']) && stripos((string)($lead['source'] ?? ''), (string)$conds['source']) === false) return false;
        if (!empty($conds['new_status']) && ($context['new_status'] ?? null) !== $conds['new_status']) return false;
        return true;
    }

    private function runWorkflowAction($rule, $lead, $userId, $tid, $context)
    {
        $params = json_decode($rule['action_params'] ?? '', true) ?: [];
        switch ($rule['action']) {
            case 'add_note':
                $text = trim((string)($params['text'] ?? ''));
                if ($text === '') return false;
                $this->db->insert('lead_notes', [
                    'lead_id' => $lead['id'],
                    'created_by' => $userId,
                    'note' => mb_substr($text, 0, 1000),
                    'content' => mb_substr($text, 0, 1000),
                    'created_at' => date('Y-m-d H:i:s'),
                    'tenant_id' => $tid,
                ]);
                return true;
            case 'set_status':
                $valid = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
                $to = $params['status'] ?? '';
                if (!in_array($to, $valid, true)) return false;
                $this->db->execute("UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?", [$to, $lead['id']]);
                $this->db->insert('lead_activities', [
                    'lead_id' => $lead['id'],
                    'activity_type' => 'status_change',
                    'description' => 'Workflow: ' . ($rule['name'] ?? 'rule'),
                    'old_value' => $lead['status'] ?? '',
                    'new_value' => $to,
                    'created_by' => $userId,
                    'tenant_id' => $tid,
                ]);
                return true;
            case 'create_followup':
                $days = max(1, min(90, (int)($params['days'] ?? 3)));
                $type = preg_replace('/[^a-z_]/', '', (string)($params['type'] ?? 'call')) ?: 'call';
                $this->db->insert('followups', [
                    'lead_id' => $lead['id'],
                    'assigned_to' => $userId,
                    'followup_type' => mb_substr($type, 0, 50),
                    'followup_date' => date('Y-m-d', strtotime("+$days days")),
                    'notes' => 'Workflow: ' . ($rule['name'] ?? 'rule'),
                    'status' => 'pending',
                    'tenant_id' => $tid,
                ]);
                return true;
            default:
                return false;
        }
    }

    // ========================================================================
    // LEAD FOLLOW-UP (from LeadFollowUpService)
    // ========================================================================

    public function sendFollowUpForIncompleteRegistrations()
    {
        try {
            $leads = $this->db->fetchAll("
                SELECT * FROM leads
                WHERE source = 'incomplete_registration'
                AND status = 'new'
                AND deleted_at IS NULL
                AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND (email IS NOT NULL OR phone IS NOT NULL)
                " . $this->tenantSql() . "
                ORDER BY created_at DESC
            ");

            foreach ($leads as $lead) {
                $this->sendLeadFollowUp($lead, 'incomplete_registration');
            }

            return ['success' => true, 'message' => 'Follow-ups sent'];
        } catch (\Throwable $e) {
            error_log("Follow-up error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendFollowUpForNewLeads()
    {
        try {
            $leads = $this->db->fetchAll("
                SELECT * FROM leads
                WHERE status = 'new'
                AND source != 'incomplete_registration'
                AND deleted_at IS NULL
                AND (email IS NOT NULL OR phone IS NOT NULL)
                " . $this->tenantSql() . "
                ORDER BY created_at DESC
            ");

            foreach ($leads as $lead) {
                $this->sendLeadFollowUp($lead, 'new_lead');
            }

            return ['success' => true, 'message' => 'Lead follow-ups sent'];
        } catch (\Throwable $e) {
            error_log("Lead follow-up error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sendLeadFollowUp($lead, $type = 'new_lead')
    {
        $email = $lead['email'] ?? null;
        $phone = $lead['phone'] ?? null;
        $name = $lead['name'] ?? 'Visitor';

        if ($email) {
            $this->sendLeadEmailFollowUp($email, $name, $type);
        }

        if ($phone) {
            $this->sendLeadSMSFollowUp($phone, $name, $type);
        }

        $this->db->query(
            "UPDATE leads SET
                 status = 'contacted',
                 updated_at = NOW()
             WHERE id = ?" . $this->tenantSql(),
            array_merge([$lead['id']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );
    }

    private function sendLeadEmailFollowUp($email, $name, $type = 'new_lead')
    {
        if ($type === 'incomplete_registration') {
            $subject = "Complete Your Registration - APS Dream Home";
            $baseUrl = rtrim(BASE_URL, '/');
            $message = "Hi $name,\n\nYou started the registration process but didn't complete it. We're here to help if you have any questions.\n\nComplete your registration at: $baseUrl/register";
        } else {
            $subject = "Thank You for Your Interest - APS Dream Home";
            $message = "Hi $name,\n\nThank you for your interest in APS Dream Home. Our team will contact you shortly.\n\nBest regards,\nAPS Dream Home Team";
        }

        error_log("Lead email sent to $email: $subject");

        try {
            $emailService = new \App\Services\Communication\EmailService();
            $emailService->send($email, $subject, nl2br($message));
        } catch (\Throwable $e) {
            error_log("Failed to send lead email to $email: " . $e->getMessage());
        }
    }

    private function sendLeadSMSFollowUp($phone, $name, $type = 'new_lead')
    {
        if ($type === 'incomplete_registration') {
            $message = "Hi $name, complete your APS Dream Home registration. Call 9277121112 for help.";
        } else {
            $message = "Hi $name, thanks for interest in APS Dream Home. Our team will contact you soon.";
        }

        error_log("Lead SMS sent to $phone: $message");

        try {
            if (class_exists('\\App\\Services\\Communication\\SmsService')) {
                $smsService = new \App\Services\Communication\SmsService();
                $smsService->send($phone, $message);
            }
        } catch (\Throwable $e) {
            error_log("Failed to send lead SMS to $phone: " . $e->getMessage());
        }
    }

    public function getFollowUpStats()
    {
        try {
            $stats = $this->db->fetchOne("
                SELECT
                    COUNT(*) as total_incomplete,
                    COUNT(CASE WHEN follow_up_sent = 1 THEN 1 END) as follow_ups_sent,
                    COUNT(CASE WHEN is_converted = 1 THEN 1 END) as converted
                FROM incomplete_registrations
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                " . $this->tenantSql()
            );

            return [
                'success' => true,
                'data' => [
                    'incomplete_registrations' => $stats,
                ]
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ========================================================================
    // INQUIRY TO LEAD (from InquiryToLeadService)
    // ========================================================================

    public static function wireFromInquiry(array $data): ?int
    {
        try {
            $db = Database::getInstance();
            $tid = class_exists('\App\Core\Middleware\TenantContext') ? (int)TenantContext::getId() : 1;
            $phone = trim($data['phone'] ?? '');
            $email = trim($data['email'] ?? '');
            $name = trim($data['name'] ?? '');

            if (empty($phone) && empty($email)) return null;

            $sourceMap = [
                'property_inquiry' => 'website',
                'project' => 'website',
                'contact' => 'website',
                'callback' => 'call_in',
                'whatsapp' => 'whatsapp',
                'walk_in' => 'walk_in',
                'referral' => 'referral',
                'associate' => 'associate',
            ];
            $inquiryType = $data['type'] ?? 'property_inquiry';
            $source = $sourceMap[$inquiryType] ?? 'website';

            $existingLead = null;
            if (!empty($phone)) {
                $existingLead = $db->fetchOne("SELECT id FROM leads WHERE phone = ? AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 1", $tid > 1 ? [$phone, $tid] : [$phone]);
            }
            if (!$existingLead && !empty($email)) {
                $existingLead = $db->fetchOne("SELECT id FROM leads WHERE email = ? AND deleted_at IS NULL" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY created_at DESC LIMIT 1", $tid > 1 ? [$email, $tid] : [$email]);
            }

            if ($existingLead) {
                $leadId = $existingLead['id'];
                $updates = [];
                $params = [];

                if (!empty($name)) {
                    $updates[] = "name = COALESCE(NULLIF(?, ''), name)";
                    $params[] = $name;
                }
                if (!empty($email)) {
                    $updates[] = "email = COALESCE(NULLIF(?, ''), email)";
                    $params[] = $email;
                }
                if (!empty($data['property_id']) || !empty($data['property_interest'])) {
                    $interest = $data['property_interest'] ?? $data['property_name'] ?? '';
                    if ($interest) {
                        $updates[] = "property_interest = COALESCE(NULLIF(?, ''), property_interest)";
                        $params[] = $interest;
                    }
                }
                if (!empty($data['message'])) {
                    $updates[] = "notes = CONCAT(COALESCE(notes,''), CHAR(10), CHAR(10), '[', NOW(), '] Inquiry: ', ?)";
                    $params[] = mb_substr($data['message'], 0, 500);
                }
                $updates[] = "last_activity_date = NOW()";
                $updates[] = "updated_at = NOW()";

                if (!empty($updates)) {
                    $params[] = $leadId;
                    $db->query("UPDATE leads SET " . implode(', ', $updates) . " WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? $params : $params);
                }

                try {
                    $db->query(
                        "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, 'inquiry', ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                        $tid > 1 ? [$leadId, "New {$inquiryType} inquiry received", $data['created_by'] ?? null, $tid] : [$leadId, "New {$inquiryType} inquiry received", $data['created_by'] ?? null]
                    );
                } catch (\Throwable $e) { error_log($e->getMessage()); }

                return $leadId;
            }

            $leadNumber = 'CR-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $db->query(
                "INSERT INTO leads (lead_number, name, email, phone, source, status, priority, property_interest,
                    location_preference, notes, budget, budget_range, assigned_to, created_by, created_at, updated_at" . ($tid > 1 ? ", tenant_id" : "") . ")
                 VALUES (?, ?, ?, ?, ?, 'new', 'medium', ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                $tid > 1
                    ? [
                        $leadNumber, $name, $email ?: null, $phone, $source,
                        $data['property_interest'] ?? $data['property_name'] ?? null,
                        $data['location_preference'] ?? $data['location'] ?? null,
                        mb_substr($data['message'] ?? '', 0, 500),
                        $data['budget'] ?? null,
                        $data['budget_range'] ?? null,
                        $data['assigned_to'] ?? null,
                        $data['created_by'] ?? null,
                        $tid,
                    ]
                    : [
                        $leadNumber, $name, $email ?: null, $phone, $source,
                        $data['property_interest'] ?? $data['property_name'] ?? null,
                        $data['location_preference'] ?? $data['location'] ?? null,
                        mb_substr($data['message'] ?? '', 0, 500),
                        $data['budget'] ?? null,
                        $data['budget_range'] ?? null,
                        $data['assigned_to'] ?? null,
                        $data['created_by'] ?? null,
                    ]
            );
            $leadId = $db->lastInsertId();

            try {
                $db->query(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at" . ($tid > 1 ? ", tenant_id" : "") . ") VALUES (?, 'created', ?, ?, NOW()" . ($tid > 1 ? ", ?" : "") . ")",
                    $tid > 1 ? [$leadId, "Lead auto-created from {$inquiryType} inquiry", $data['created_by'] ?? null, $tid] : [$leadId, "Lead auto-created from {$inquiryType} inquiry", $data['created_by'] ?? null]
                );
            } catch (\Throwable $e) { error_log($e->getMessage()); }

            return $leadId;
        } catch (\Throwable $e) {
            error_log('LeadService::wireFromInquiry error: ' . $e->getMessage());
            return null;
        }
    }

    // ========================================================================
    // LEAD LIFECYCLE HOOKS (from LeadScoringAutoHook)
    // ========================================================================

    public static function onLeadCreated(int $leadId): void
    {
        if ($leadId <= 0) return;
        try {
            $service = new self();
            $service->calculateScore($leadId);
        } catch (\Throwable $e) {
            error_log("LeadService::onLeadCreated($leadId) failed: " . $e->getMessage());
        }
    }

    public static function onLeadUpdated(int $leadId): void
    {
        if ($leadId <= 0) return;
        try {
            $service = new self();
            $service->calculateScore($leadId);
        } catch (\Throwable $e) {
            error_log("LeadService::onLeadUpdated($leadId) failed: " . $e->getMessage());
        }
    }

    public static function onStatusChanged(int $leadId, string $newStatus): void
    {
        if ($leadId <= 0) return;
        try {
            $service = new self();
            $service->calculateScore($leadId);
        } catch (\Throwable $e) {
            error_log("LeadService::onStatusChanged($leadId, $newStatus) failed: " . $e->getMessage());
        }
    }

    public static function onPaymentRecorded(int $leadId, float $amount): void
    {
        if ($leadId <= 0) return;
        try {
            $service = new self();
            $service->calculateScore($leadId);
        } catch (\Throwable $e) {
            error_log("LeadService::onPaymentRecorded($leadId, $amount) failed: " . $e->getMessage());
        }
    }

    public static function onSiteVisitCompleted(int $leadId): void
    {
        if ($leadId <= 0) return;
        try {
            $service = new self();
            $service->calculateScore($leadId);
        } catch (\Throwable $e) {
            error_log("LeadService::onSiteVisitCompleted($leadId) failed: " . $e->getMessage());
        }
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    private function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable) {
            return 1;
        }
    }

    private function tenantSql(): string
    {
        return $this->tenantId() > 1 ? " AND tenant_id = ?" : "";
    }

    private function tenantSqlForAlias(string $alias): string
    {
        return $this->tenantId() > 1 ? " AND {$alias}.tenant_id = ?" : "";
    }

    private function tenantInsertData(): array
    {
        $tid = $this->tenantId();
        return $tid > 1 ? ['tenant_id' => $tid] : [];
    }

    private function getSetting(string $key, string $default, int $tid): string
    {
        try {
            $row = $this->db->fetch("SELECT setting_value FROM service_configs WHERE setting_key = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$key, $tid] : [$key]);
            return $row ? $row['setting_value'] : $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
