<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use Exception;

class MarketplaceService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct($db = null)
    {
        $this->db = $db ?? Database::getInstance();
        if (is_object($this->db) && method_exists($this->db, "getPdo")) {
            $this->pdo = $this->db->getPdo();
        } elseif ($this->db instanceof \PDO) {
            $this->pdo = $this->db;
        } else {
            $this->pdo = $this->db;
        }
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
     * Track buyer interest signal (view, save, share, inquire, etc.)
     */
    public function trackInterest(array $data): array
    {
        $tid = $this->getTenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';

        $stmt = $this->pdo->prepare("
            INSERT INTO property_interest_logs 
            (property_id, listing_type, user_id, session_id, interest_type, referral_code, metadata, ip_address, user_agent, created_at{$extraCol})
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(){$extraVal})
        ");

        $params = [
            $data['property_id'],
            $data['listing_type'] ?? 'user',
            $data['user_id'] ?? null,
            $data['session_id'] ?? null,
            $data['interest_type'],
            $data['referral_code'] ?? null,
            isset($data['metadata']) ? json_encode($data['metadata']) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
        if ($tid > 1) $params[] = $tid;

        $stmt->execute($params);

        // Update property view/inquiry counts
        if ($data['interest_type'] === 'view') {
            $this->pdo->prepare("UPDATE user_properties SET views = views + 1 WHERE id = ?")->execute([$data['property_id']]);
        } elseif ($data['interest_type'] === 'inquire') {
            $this->pdo->prepare("UPDATE user_properties SET inquiries = inquiries + 1 WHERE id = ?")->execute([$data['property_id']]);
        }

        return ['success' => true, 'id' => (int)$this->pdo->lastInsertId()];
    }

    /**
     * Save/unsave property to user's shortlist
     */
    public function toggleSaveProperty(int $userId, int $propertyId, string $listingType = 'user', ?string $notes = null): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        // Check if already saved
        $checkStmt = $this->pdo->prepare("SELECT id FROM user_saved_properties WHERE user_id = ? AND property_id = ? AND listing_type = ?{$tenantWhere}");
        $checkStmt->execute(array_merge([$userId, $propertyId, $listingType], $tenantParams));
        $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            // Unsave
            $delStmt = $this->pdo->prepare("DELETE FROM user_saved_properties WHERE id = ?");
            $delStmt->execute([$existing['id']]);
            return ['success' => true, 'action' => 'removed'];
        }

        // Save
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $insStmt = $this->pdo->prepare("
            INSERT INTO user_saved_properties (user_id, property_id, listing_type, notes, saved_at{$extraCol})
            VALUES (?, ?, ?, ?, NOW(){$extraVal})
        ");
        $params = [$userId, $propertyId, $listingType, $notes];
        if ($tid > 1) $params[] = $tid;
        $insStmt->execute($params);

        return ['success' => true, 'action' => 'saved'];
    }

    /**
     * Get user's saved properties
     */
    public function getSavedProperties(int $userId, string $listingType = ''): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND usp.tenant_id = ?" : "";
        $params = [$userId];
        if ($tid > 1) $params[] = $tid;

        $typeFilter = '';
        if ($listingType) {
            $typeFilter = " AND usp.listing_type = ?";
            $params[] = $listingType;
        }

        $sql = "SELECT usp.*, 
                CASE 
                    WHEN usp.listing_type = 'user' THEN up.name
                    WHEN usp.listing_type = 'resell' THEN rp.title
                    ELSE p.title
                END as title,
                CASE 
                    WHEN usp.listing_type = 'user' THEN up.price
                    WHEN usp.listing_type = 'resell' THEN rp.asking_price
                    ELSE p.price
                END as price,
                CASE 
                    WHEN usp.listing_type = 'user' THEN up.address
                    WHEN usp.listing_type = 'resell' THEN rp.location
                    ELSE p.location
                END as location,
                CASE 
                    WHEN usp.listing_type = 'user' THEN up.property_type
                    WHEN usp.listing_type = 'resell' THEN rp.property_type
                    ELSE p.type
                END as property_type,
                CASE 
                    WHEN usp.listing_type = 'user' THEN up.status
                    WHEN usp.listing_type = 'resell' THEN rp.status
                    ELSE p.status
                END as status
        FROM user_saved_properties usp
        LEFT JOIN user_properties up ON usp.property_id = up.id AND usp.listing_type = 'user'
        LEFT JOIN resell_properties rp ON usp.property_id = rp.id AND usp.listing_type = 'resell'
        LEFT JOIN properties p ON usp.property_id = p.id AND usp.listing_type = 'company'
        WHERE usp.user_id = ?{$tenantWhere}{$typeFilter}
        ORDER BY usp.saved_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Capture lead from property inquiry
     */
    public function captureLeadFromInquiry(array $data): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        // Check if lead already exists for this phone/email
        $checkStmt = $this->pdo->prepare("SELECT id FROM leads WHERE (phone = ? OR email = ?) AND status NOT IN ('converted', 'closed_lost'){$tenantWhere} LIMIT 1");
        $checkStmt->execute(array_merge([$data['phone'], $data['email']], $tenantParams));
        $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing lead with new interest
            $updStmt = $this->pdo->prepare("
                UPDATE leads 
                SET property_interest = ?, budget_range = ?, last_message = ?, updated_at = NOW(),
                    last_activity_at = NOW(), last_activity_date = CURDATE()
                WHERE id = ?
            ");
            $updStmt->execute([
                $data['property_name'] ?? '',
                $data['budget'] ?? '',
                $data['message'] ?? '',
                $existing['id']
            ]);

            // Log activity
            $this->logLeadActivity($existing['id'], 'new_inquiry', "New inquiry for property: " . ($data['property_name'] ?? ''));

            return ['success' => true, 'lead_id' => $existing['id'], 'action' => 'updated'];
        }

        // Create new lead
        $leadNumber = 'LEAD-' . date('Y') . '-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';

        $insStmt = $this->pdo->prepare("
            INSERT INTO leads 
            (lead_number, name, email, phone, property_interest, budget_range, budget, source, source_detail, 
             status, message, last_message, assigned_to, created_by, created_at, last_activity_at, last_activity_date{$extraCol})
            VALUES (?, ?, ?, ?, ?, ?, ?, 'website', ?, 'new', ?, ?, ?, ?, NOW(), CURDATE(){$extraVal})
        ");

        $params = [
            $leadNumber,
            $data['name'],
            $data['email'] ?? '',
            $data['phone'],
            $data['property_name'] ?? '',
            $data['budget_range'] ?? '',
            $data['budget'] ?? null,
            $data['listing_type'] ?? 'user_property',
            $data['message'] ?? '',
            $data['message'] ?? '',
            $data['assigned_to'] ?? null,
            $data['created_by'] ?? 0,
        ];
        if ($tid > 1) $params[] = $tid;

        $insStmt->execute($params);
        $leadId = (int)$this->pdo->lastInsertId();

        // Log activity
        $this->logLeadActivity($leadId, 'created', "Lead created from property inquiry");

        // Auto-assign to associate/agent if referral code present
        if (!empty($data['referral_code'])) {
            $this->autoAssignLead($leadId, $data['referral_code']);
        }

        // Create follow-up schedule
        $this->createInitialFollowup($leadId, $data);

        return ['success' => true, 'lead_id' => $leadId, 'action' => 'created', 'lead_number' => $leadNumber];
    }

    /**
     * Auto-assign lead to associate/agent based on referral code
     */
    private function autoAssignLead(int $leadId, string $referralCode): void
    {
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $tenantParams = $tid > 1 ? [$tid] : [];

            $stmt = $this->pdo->prepare("SELECT id, role FROM users WHERE referral_code = ?{$tenantWhere} LIMIT 1");
            $stmt->execute(array_merge([$referralCode], $tenantParams));
            $referrer = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($referrer && in_array($referrer['role'], ['associate', 'agent'], true)) {
                $updStmt = $this->pdo->prepare("UPDATE leads SET assigned_to = ?, referral_source = ? WHERE id = ?");
                $updStmt->execute([$referrer['id'], $referralCode, $leadId]);
            }
        } catch (\Throwable $e) {
            error_log("MarketplaceService::autoAssignLead: " . $e->getMessage());
        }
    }

    /**
     * Create initial follow-up schedule for new lead
     * Supports smart sequences based on lead score, property type, and budget
     */
    private function createInitialFollowup(int $leadId, array $data): void
    {
        try {
            $tid = $this->getTenantId();
            $extraCol = $tid > 1 ? ', tenant_id' : '';
            $extraVal = $tid > 1 ? ', ?' : '';

            // Get lead details for smart sequencing
            $lead = $this->getLeadDetails($leadId);
            $sequence = $this->determineFollowupSequence($lead, $data);

            foreach ($sequence as $index => $step) {
                $delay = $step['delay_hours'] ?? 0;
                $scheduled = $delay > 0 ? "DATE_ADD(NOW(), INTERVAL {$delay} HOUR)" : "NOW()";
                
                $insStmt = $this->pdo->prepare("
                    INSERT INTO followup_schedules 
                    (lead_id, property_id, listing_type, buyer_id, scheduled_for, followup_type, priority, status, notes, created_by, created_at{$extraCol})
                    VALUES (?, ?, ?, ?, {$scheduled}, ?, ?, 'pending', ?, ?, NOW(){$extraVal})
                ");

                $params = [
                    $leadId,
                    $data['property_id'] ?? null,
                    $data['listing_type'] ?? 'user',
                    $data['user_id'] ?? null,
                    $step['type'],
                    $step['priority'] ?? 'medium',
                    $step['notes'] ?? '',
                    $data['created_by'] ?? 0,
                ];
                if ($tid > 1) $params[] = $tid;
                $insStmt->execute($params);
            }

        } catch (\Throwable $e) {
            error_log("MarketplaceService::createInitialFollowup: " . $e->getMessage());
        }
    }

    /**
     * Determine smart follow-up sequence based on lead profile
     */
    private function determineFollowupSequence(array $lead, array $data): array
    {
        $budget = (float)($lead['budget'] ?? $data['budget'] ?? 0);
        $leadScore = (int)($lead['lead_score'] ?? 0);
        $propertyType = $data['listing_type'] ?? 'user';
        $isHot = $leadScore >= 70 || $budget >= 5000000;
        $isHighValue = $budget >= 10000000;

        $sequence = [];

        // Step 1: Immediate call (within 2 hours) - always
        $sequence[] = [
            'type' => 'call',
            'delay_hours' => 2,
            'priority' => 'high',
            'notes' => 'Initial contact - introduce APS Dream Home, understand requirements',
        ];

        // Step 2: WhatsApp follow-up (24 hours) - always
        $sequence[] = [
            'type' => 'whatsapp',
            'delay_hours' => 24,
            'priority' => 'high',
            'notes' => 'Send property options via WhatsApp with images & pricing',
        ];

        // Hot leads: aggressive follow-up
        if ($isHot) {
            $sequence[] = [
                'type' => 'call',
                'delay_hours' => 48,
                'priority' => 'high',
                'notes' => 'Hot lead - schedule site visit, discuss financing options',
            ];
            $sequence[] = [
                'type' => 'whatsapp',
                'delay_hours' => 72,
                'priority' => 'medium',
                'notes' => 'Send property brochure, payment plan options',
            ];
            $sequence[] = [
                'type' => 'site_visit',
                'delay_hours' => 96,
                'priority' => 'high',
                'notes' => 'Schedule site visit, involve senior sales if needed',
            ];
        } else {
            // Warm/Cold leads: nurturing sequence
            $sequence[] = [
                'type' => 'whatsapp',
                'delay_hours' => 72,
                'priority' => 'medium',
                'notes' => 'Send property catalog, EMI calculator link',
            ];
            $sequence[] = [
                'type' => 'email',
                'delay_hours' => 120,
                'priority' => 'low',
                'notes' => 'Send market report, similar properties, investment guide',
            ];
            $sequence[] = [
                'type' => 'call',
                'delay_hours' => 168,
                'priority' => 'medium',
                'notes' => 'Check interest, address objections, offer site visit',
            ];
        }

        // High-value properties: add premium touches
        if ($isHighValue) {
            $sequence[] = [
                'type' => 'meeting',
                'delay_hours' => 24,
                'priority' => 'urgent',
                'notes' => 'VIP treatment - arrange manager call, premium brochure',
            ];
        }

        return $sequence;
    }

    /**
     * Get lead details for smart sequencing
     */
    private function getLeadDetails(int $leadId): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$leadId];
        if ($tid > 1) $params[] = $tid;

        $stmt = $this->pdo->prepare("
            SELECT id, name, phone, email, budget, lead_score, status, property_interest, 
                   location_preference, lead_score, priority, source
            FROM leads WHERE id = ?{$tenantWhere} LIMIT 1
        ");
        $stmt->execute(array_merge([$leadId], $tid > 1 ? [$this->getTenantId()] : []));
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Log lead activity
     */
    public function logLeadActivity(int $leadId, string $type, string $description, ?string $oldValue = null, ?string $newValue = null): void
    {
        try {
            $tid = $this->getTenantId();
            $extraCol = $tid > 1 ? ', tenant_id' : '';
            $extraVal = $tid > 1 ? ', ?' : '';
            $stmt = $this->pdo->prepare("
                INSERT INTO lead_activities (lead_id, activity_type, description, old_value, new_value, created_at, activity_date, created_by{$extraCol})
                VALUES (?, ?, ?, ?, ?, NOW(), CURDATE(), ?{$extraVal})
            ");
            $params = [$leadId, $type, $description, $oldValue, $newValue, $_SESSION['user_id'] ?? 0];
            if ($tid > 1) $params[] = $tid;
            $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log("MarketplaceService::logLeadActivity: " . $e->getMessage());
        }
    }

    /**
     * Get leads with filters for admin/agent/associate
     */
    public function getLeads(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $tid = $this->getTenantId();
        $where = ["1=1"];
        $params = [];

        if ($tid > 1) {
            $where[] = "l.tenant_id = ?";
            $params[] = $tid;
        }

        if (!empty($filters['status'])) {
            $where[] = "l.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['assigned_to'])) {
            $where[] = "l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        if (!empty($filters['source'])) {
            $where[] = "l.source = ?";
            $params[] = $filters['source'];
        }
        if (!empty($filters['property_interest'])) {
            $where[] = "l.property_interest LIKE ?";
            $params[] = '%' . $filters['property_interest'] . '%';
        }
        if (!empty($filters['search'])) {
            $where[] = "(l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ? OR l.lead_number LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM leads l WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        $sql = "SELECT l.*, u.name as assigned_name
        FROM leads l
        LEFT JOIN users u ON l.assigned_to = u.id
        WHERE {$whereClause}
        ORDER BY l.created_at DESC
        LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $leads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'leads' => $leads,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Get follow-up schedule for today
     */
    public function getTodayFollowups(int $userId = 0): array
    {
        $tid = $this->getTenantId();
        $where = ["DATE(fs.scheduled_for) = CURDATE()", "fs.status = 'pending'"];
        $params = [];

        if ($tid > 1) {
            $where[] = "fs.tenant_id = ?";
            $params[] = $tid;
        }

        if ($userId > 0) {
            $where[] = "fs.assigned_to = ?";
            $params[] = $userId;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT fs.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                l.property_interest, l.budget_range
        FROM followup_schedules fs
        LEFT JOIN leads l ON fs.lead_id = l.id
        WHERE {$whereClause}
        ORDER BY fs.scheduled_for ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get overdue follow-ups
     */
    public function getOverdueFollowups(int $userId = 0): array
    {
        $tid = $this->getTenantId();
        $where = ["fs.scheduled_for < NOW()", "fs.status = 'pending'"];
        $params = [];

        if ($tid > 1) {
            $where[] = "fs.tenant_id = ?";
            $params[] = $tid;
        }

        if ($userId > 0) {
            $where[] = "fs.assigned_to = ?";
            $params[] = $userId;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT fs.*, l.name as lead_name, l.phone as lead_phone
        FROM followup_schedules fs
        LEFT JOIN leads l ON fs.lead_id = l.id
        WHERE {$whereClause}
        ORDER BY fs.scheduled_for ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Complete a follow-up
     */
    public function completeFollowup(int $followupId, string $outcome, string $notes = '', int $userId = 0): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $stmt = $this->pdo->prepare("
            UPDATE followup_schedules 
            SET status = 'completed', outcome = ?, notes = ?, completed_at = NOW(), completed_by = ?
            WHERE id = ?{$tenantWhere}
        ");
        $stmt->execute(array_merge([$outcome, $notes, $userId, $followupId], $tenantParams));

        // Log activity on lead
        $fuStmt = $this->pdo->prepare("SELECT lead_id FROM followup_schedules WHERE id = ?");
        $fuStmt->execute([$followupId]);
        $fu = $fuStmt->fetch(\PDO::FETCH_ASSOC);
        if ($fu) {
            $this->logLeadActivity($fu['lead_id'], 'followup_completed', "Follow-up completed: {$outcome}. Notes: {$notes}");
        }

        return ['success' => true];
    }

    /**
     * Get property verification status
     */
    public function getVerificationStatus(int $propertyId, string $listingType = 'user'): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        $stmt = $this->pdo->prepare("
            SELECT * FROM property_verification_logs 
            WHERE property_id = ? AND listing_type = ?{$tenantWhere}
            ORDER BY created_at DESC
        ");
        $stmt->execute(array_merge([$propertyId, $listingType], $tenantParams));
        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $statuses = [];
        foreach ($logs as $log) {
            $statuses[$log['verification_type']] = $log['status'];
        }

        return [
            'property_id' => $propertyId,
            'listing_type' => $listingType,
            'verifications' => $statuses,
            'logs' => $logs,
            'all_verified' => !empty($statuses) && count(array_unique(array_values($statuses))) === 1 && $statuses[array_key_first($statuses)] === 'verified',
        ];
    }

    /**
     * Start verification process
     */
    public function startVerification(int $propertyId, string $listingType, string $type, int $userId): array
    {
        $tid = $this->getTenantId();
        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';

        $stmt = $this->pdo->prepare("
            INSERT INTO property_verification_logs 
            (property_id, listing_type, verification_type, status, initiated_by, started_at, created_at{$extraCol})
            VALUES (?, ?, ?, 'in_progress', ?, NOW(), NOW(){$extraVal})
            ON DUPLICATE KEY UPDATE status = 'in_progress', initiated_by = ?, started_at = NOW()
        ");

        $params = [$propertyId, $listingType, $type, $userId, $userId];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return ['success' => true];
    }

    /**
     * Get marketplace analytics
     */
    public function getMarketplaceAnalytics(int $days = 30): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        // Total listings by status
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM user_properties WHERE 1=1{$tenantWhere} GROUP BY status");
        $stmt->execute($tenantParams);
        $listingsByStatus = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Total leads
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY){$tenantWhere}");
        $stmt->execute(array_merge([$days], $tenantParams));
        $totalLeads = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // Leads by status
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM leads WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY){$tenantWhere} GROUP BY status");
        $stmt->execute(array_merge([$days], $tenantParams));
        $leadsByStatus = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Interest signals
        $stmt = $this->pdo->prepare("SELECT interest_type, COUNT(*) as count FROM property_interest_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY){$tenantWhere} GROUP BY interest_type");
        $stmt->execute(array_merge([$days], $tenantParams));
        $interestSignals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Platform revenue
        $stmt = $this->pdo->prepare("SELECT source_type, SUM(amount) as total FROM platform_revenue WHERE recorded_at > DATE_SUB(NOW(), INTERVAL ? DAY){$tenantWhere} GROUP BY source_type");
        $stmt->execute(array_merge([$days], $tenantParams));
        $revenue = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Follow-up stats
        $stmt = $this->pdo->prepare("SELECT status, COUNT(*) as count FROM followup_schedules WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY){$tenantWhere} GROUP BY status");
        $stmt->execute(array_merge([$days], $tenantParams));
        $followupStats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'listings_by_status' => $listingsByStatus,
            'total_leads' => $totalLeads,
            'leads_by_status' => $leadsByStatus,
            'interest_signals' => $interestSignals,
            'revenue' => $revenue,
            'followup_stats' => $followupStats,
        ];
    }

    /**
     * Get boost amount for a given boost type.
     * Centralized to avoid duplication across controllers.
     */
    public static function getBoostAmount(string $boostType): int
    {
        return match($boostType) {
            'featured' => 499,
            'urgent' => 299,
            'premium' => 999,
            default => 499,
        };
    }

    /**
     * Apply boost to a property after successful payment.
     * Shared by MarketplaceController::verifyBoostPayment() and
     * MobileUserApiController::verifyBoostPayment() to avoid duplication.
     *
     * @param int $propertyId
     * @param int $userId Owner user ID
     * @param string $boostType featured|urgent|premium
     * @param int $duration Days
     * @param string $paymentId Razorpay payment ID
     * @param string $orderId Razorpay order ID
     * @return array ['success' => bool, 'message' => string, 'payment_id' => ?string]
     */
    public function applyBoostAfterPayment(int $propertyId, int $userId, string $boostType, int $duration, string $paymentId, string $orderId): array
    {
        $tid = $this->getTenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $tenantParams = $tid > 1 ? [$tid] : [];

        if ($propertyId <= 0 || $userId <= 0) {
            return ['success' => false, 'message' => 'Invalid property or user'];
        }

        // Verify property ownership
        $stmt = $this->pdo->prepare("SELECT id FROM user_properties WHERE id = ? AND user_id = ?{$tenantWhere}");
        $stmt->execute(array_merge([$propertyId, $userId], $tenantParams));
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Property not found or not owned by you'];
        }

        $boostAmount = self::getBoostAmount($boostType);
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$duration} days"));

        try {
            // Apply boost
            $stmt = $this->pdo->prepare("
                UPDATE user_properties
                SET is_featured = CASE WHEN ? = 'featured' THEN 1 ELSE is_featured END,
                    is_urgent = CASE WHEN ? = 'urgent' THEN 1 ELSE is_urgent END,
                    is_premium = CASE WHEN ? = 'premium' THEN 1 ELSE is_premium END,
                    boosted_at = NOW(),
                    boost_expires_at = ?,
                    boost_amount = ?,
                    promoted_until = ?,
                    updated_at = NOW()
                WHERE id = ? AND user_id = ?{$tenantWhere}
            ");
            $stmt->execute(array_merge([$boostType, $boostType, $boostType, $expiresAt, $boostAmount, $expiresAt, $propertyId, $userId], $tenantParams));

            if ($stmt->rowCount() <= 0) {
                return ['success' => false, 'message' => 'Failed to apply boost'];
            }

            // Track revenue
            $this->pdo->prepare("
                INSERT INTO platform_revenue (source_type, source_id, amount, description, recorded_at)
                VALUES ('boost', ?, ?, ?, NOW())
            ")->execute([$propertyId, $boostAmount, "Property boost: {$boostType} for {$duration} days"]);

            // Record payment in payments table
            $boostAmountFloat = (float)$boostAmount;
            $this->pdo->prepare("
                INSERT INTO payments (booking_id, user_id, amount, payment_method, transaction_id, order_id, status, payment_date, created_at)
                VALUES (?, ?, ?, 'razorpay', ?, ?, 'completed', CURDATE(), NOW())
            ")->execute([$propertyId, $userId, $boostAmountFloat, $paymentId, $orderId]);

            return ['success' => true, 'message' => 'Property boosted successfully!', 'payment_id' => $paymentId];

        } catch (\Throwable $e) {
            error_log("MarketplaceService::applyBoostAfterPayment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to apply boost'];
        }
    }
}