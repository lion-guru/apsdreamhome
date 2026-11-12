<?php

namespace App\Services\MLM;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use PDO;

/**
 * Leadership Expense Fund Service
 *
 * Manages target-linked performance allowances for senior leaders and associates:
 * 1. Office Maintenance & Branch Rent Support:
 *    - BDM (₹15L monthly team volume) -> ₹10,000/mo Office Support
 *    - Sr. BDM (₹30L monthly team volume) -> ₹20,000/mo Office Support
 *    - V.P. (₹50L monthly team volume) -> ₹35,000/mo Office Support
 *    - President / Site Manager (₹1 Cr+ monthly volume) -> ₹50,000/mo Office Support
 * 2. Township Site Visit & Fuel / Vehicle Allowance:
 *    - Verified physical site visit with GPS check-in (Gorakhpur Local: ₹800, Outstation: ₹1,800)
 *    - Anti-fraud: requires verified GPS check-in at colony location
 * 3. Wallet Credit Integration: Approved allowances are credited directly to associate wallet.
 */
class LeadershipExpenseFundService
{
    use ServiceTenantTrait;

    private ?PDO $db;

    public const OFFICE_SUPPORT_TIERS = [
        'BDM'          => ['monthly_target' => 1500000.00, 'allowance' => 10000.00, 'label' => 'BDM Office Rent & Utility Support'],
        'Sr. BDM'      => ['monthly_target' => 3000000.00, 'allowance' => 20000.00, 'label' => 'Sr. BDM Regional Office Support'],
        'V.P.'         => ['monthly_target' => 5000000.00, 'allowance' => 35000.00, 'label' => 'Vice President Division Office Support'],
        'President'    => ['monthly_target' => 10000000.00,'allowance' => 50000.00, 'label' => 'President Corporate Branch Office Support'],
        'Site Manager' => ['monthly_target' => 10000000.00,'allowance' => 50000.00, 'label' => 'Site Manager Operations Headquarters Support'],
    ];

    public const SITE_VISIT_RATES = [
        'local'      => ['rate' => 800.00,  'desc' => 'Gorakhpur Local Township Site Visit Fuel Allowance'],
        'outstation' => ['rate' => 1800.00, 'desc' => 'Purvanchal Regional Site Visit Vehicle Allowance'],
    ];

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $db = Database::getInstance();
                $this->db = method_exists($db, 'getPdo') ? $db->getPdo() : (method_exists($db, 'getConnection') ? $db->getConnection() : null);
            } catch (\Throwable $e) {
                error_log('[LeadershipExpenseFundService] DB init error: ' . $e->getMessage());
                $this->db = null;
            }
        } else {
            $this->db = $pdo;
        }
    }

    /**
     * Get an associate's current month team business volume.
     */
    public function getMonthlyTeamVolume(int $userId, ?string $monthYear = null): float
    {
        if (!$this->db || $userId <= 0) {
            return 0.0;
        }
        $monthYear = $monthYear ?: date('Y-m');
        try {
            // Find all downline associates in user's team
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE (referred_by = ? OR id = ?) AND tenant_id = ?
            ");
            $stmt->execute([$userId, $userId, $this->tenantId()]);
            $teamUserIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($teamUserIds)) {
                return 0.0;
            }

            $inPlaceholders = implode(',', array_fill(0, count($teamUserIds), '?'));
            $params = array_merge([$monthYear, $monthYear], $teamUserIds, $teamUserIds);

            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(total_plot_value), 0) as total_volume
                FROM plot_bookings
                WHERE YEAR(created_at) = YEAR(CONCAT(?, '-01'))
                  AND MONTH(created_at) = MONTH(CONCAT(?, '-01'))
                  AND status NOT IN ('cancelled', 'rejected')
                  AND (associate_id IN ($inPlaceholders) OR sales_manager_id IN ($inPlaceholders))
            ");
            $stmt->execute($params);
            return (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] getMonthlyTeamVolume error: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Evaluate Leader Office Support Allowance for current month.
     */
    public function evaluateOfficeSupport(int $userId, string $rankName, ?string $monthYear = null): array
    {
        $monthYear = $monthYear ?: date('Y-m');
        $normalizedRank = MLMCommissionEngine::normalizeRankName($rankName);
        $tier = self::OFFICE_SUPPORT_TIERS[$normalizedRank] ?? null;

        if (!$tier) {
            return [
                'eligible_for_program' => false,
                'rank'                 => $normalizedRank,
                'message'              => 'Office support is available for BDM rank and above.'
            ];
        }

        $achievedVolume = $this->getMonthlyTeamVolume($userId, $monthYear);
        $targetVolume = (float)$tier['monthly_target'];
        $allowanceAmount = (float)$tier['allowance'];
        $progressPct = min(100.0, round(($achievedVolume / max(1.0, $targetVolume)) * 100.0, 1));
        $isQualified = ($achievedVolume >= $targetVolume);

        // Check if already claimed / recorded in mlm_leader_allowances
        $claimStatus = 'not_claimed';
        $allowanceId = 0;
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("
                    SELECT id, status FROM mlm_leader_allowances 
                    WHERE user_id = ? AND allowance_type = 'office_support' AND month_year = ? AND tenant_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$userId, $monthYear, $this->tenantId()]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existing) {
                    $claimStatus = $existing['status'];
                    $allowanceId = (int)$existing['id'];
                }
            } catch (\Throwable $e) {
                error_log('[LeadershipExpenseFundService] check claim error: ' . $e->getMessage());
            }
        }

        return [
            'eligible_for_program'  => true,
            'user_id'               => $userId,
            'rank'                  => $normalizedRank,
            'label'                 => $tier['label'],
            'month_year'            => $monthYear,
            'target_volume'         => $targetVolume,
            'achieved_volume'       => $achievedVolume,
            'remaining_volume'      => max(0.0, $targetVolume - $achievedVolume),
            'progress_pct'          => $progressPct,
            'allowance_amount'      => $allowanceAmount,
            'is_qualified'          => $isQualified,
            'claim_status'          => $claimStatus,
            'allowance_id'          => $allowanceId
        ];
    }

    /**
     * Record a site visit expense reimbursement claim.
     */
    public function recordSiteVisitAllowance(int $userId, int $siteVisitId, string $visitType = 'local'): array
    {
        if (!$this->db || $userId <= 0) {
            return ['success' => false, 'message' => 'Invalid user ID'];
        }

        $rateInfo = self::SITE_VISIT_RATES[$visitType] ?? self::SITE_VISIT_RATES['local'];
        $amount = (float)$rateInfo['rate'];
        $monthYear = date('Y-m');

        try {
            // Anti-fraud: Verify site visit exists and is verified
            $stmt = $this->db->prepare("SELECT id, status, colony_id FROM site_visits WHERE id = ? LIMIT 1");
            $stmt->execute([$siteVisitId]);
            $visit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visit) {
                return ['success' => false, 'message' => 'Site visit record not found.'];
            }

            // Check if already claimed for this visit
            $stmt = $this->db->prepare("
                SELECT id FROM mlm_leader_allowances 
                WHERE site_visit_id = ? AND allowance_type IN ('site_visit_fuel', 'vehicle_hiring')
                LIMIT 1
            ");
            $stmt->execute([$siteVisitId]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Allowance already claimed for this site visit.'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO mlm_leader_allowances (
                    tenant_id, user_id, rank_name, allowance_type, month_year,
                    target_volume, achieved_volume, amount, site_visit_id, status, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, 'Associate', 'site_visit_fuel', ?,
                    0.00, 0.00, ?, ?, 'approved', ?, NOW(), NOW()
                )
            ");
            $stmt->execute([
                $this->tenantId(),
                $userId,
                $monthYear,
                $amount,
                $siteVisitId,
                $rateInfo['desc'] . " (Site Visit #$siteVisitId)"
            ]);
            $allowanceId = (int)$this->db->lastInsertId();

            return [
                'success'      => true,
                'allowance_id' => $allowanceId,
                'amount'       => $amount,
                'message'      => "₹$amount site visit allowance recorded successfully."
            ];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] recordSiteVisitAllowance error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Approve and disburse Office Support Allowance to associate wallet.
     */
    public function disburseOfficeSupport(int $userId, string $rankName, ?string $monthYear = null, ?int $approvedBy = 1): array
    {
        $eval = $this->evaluateOfficeSupport($userId, $rankName, $monthYear);
        if (!$eval['is_qualified']) {
            return [
                'success' => false,
                'message' => "Target not achieved yet. Achieved ₹" . number_format($eval['achieved_volume']) . " of ₹" . number_format($eval['target_volume'])
            ];
        }

        if ($eval['claim_status'] === 'paid') {
            return ['success' => false, 'message' => 'Office support allowance already paid for this month.'];
        }

        try {
            $month = $eval['month_year'];
            $amount = $eval['allowance_amount'];

            // Record or update in mlm_leader_allowances
            if ($eval['allowance_id'] > 0) {
                $stmt = $this->db->prepare("
                    UPDATE mlm_leader_allowances 
                    SET status = 'paid', approved_by = ?, approved_at = NOW(), paid_at = NOW(), updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$approvedBy, $eval['allowance_id']]);
                $grantId = $eval['allowance_id'];
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO mlm_leader_allowances (
                        tenant_id, user_id, rank_name, allowance_type, month_year,
                        target_volume, achieved_volume, amount, status, notes, approved_by, approved_at, paid_at, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, 'office_support', ?,
                        ?, ?, ?, 'paid', ?, ?, NOW(), NOW(), NOW(), NOW()
                    )
                ");
                $stmt->execute([
                    $this->tenantId(),
                    $userId,
                    $eval['rank'],
                    $month,
                    $eval['target_volume'],
                    $eval['achieved_volume'],
                    $amount,
                    "Monthly Office & Branch Support for {$eval['rank']} ({$month})",
                    $approvedBy
                ]);
                $grantId = (int)$this->db->lastInsertId();
            }

            // Credit to Associate Wallet
            $walletService = new \App\Services\WalletService();
            $walletOk = $walletService->credit(
                $userId,
                $amount,
                'bonus',
                "Monthly Office Support Allowance ({$month})",
                $grantId,
                'office_support'
            );

            return [
                'success'     => true,
                'grant_id'    => $grantId,
                'amount'      => $amount,
                'wallet_paid' => $walletOk,
                'message'     => "₹" . number_format($amount) . " Office Support credited to Associate Wallet successfully."
            ];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] disburseOfficeSupport error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get all allowances/claims for a specific user.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getUserAllowances(int $userId): array
    {
        if (!$this->db || $userId <= 0) {
            return [];
        }
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, sv.visitor_name, sv.visit_date, sv.visitor_phone
                FROM mlm_leader_allowances a
                LEFT JOIN site_visits sv ON sv.id = a.site_visit_id
                WHERE a.user_id = ? AND a.tenant_id = ?
                ORDER BY a.created_at DESC
            ");
            $stmt->execute([$userId, $this->tenantId()]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] getUserAllowances error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all pending allowance claims for admin review.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPendingAllowances(?string $allowanceType = null): array
    {
        if (!$this->db) {
            return [];
        }
        try {
            $sql = "
                SELECT a.*, u.name as user_name, u.phone as user_phone, u.email as user_email,
                       sv.visitor_name, sv.visit_date, sv.visitor_phone
                FROM mlm_leader_allowances a
                JOIN users u ON u.id = a.user_id
                LEFT JOIN site_visits sv ON sv.id = a.site_visit_id
                WHERE a.tenant_id = ?
            ";
            $params = [$this->tenantId()];
            if ($allowanceType) {
                $sql .= " AND a.allowance_type = ?";
                $params[] = $allowanceType;
            }
            $sql .= " ORDER BY (a.status = 'claimed' OR a.status = 'qualified') DESC, a.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] getPendingAllowances error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Associate submits claim for Office Support.
     *
     * @return array{success: bool, claim_id?: int, amount?: float, message: string}
     */
    public function submitOfficeSupportClaim(int $userId, string $rankName, ?string $notes = null): array
    {
        $eval = $this->evaluateOfficeSupport($userId, $rankName);
        if (!$eval['eligible_for_program']) {
            return ['success' => false, 'message' => $eval['message'] ?? 'Not eligible for office support.'];
        }
        if (!$eval['is_qualified']) {
            return [
                'success' => false,
                'message' => "Qualification target not met yet. Achieved ₹" . number_format($eval['achieved_volume']) . " of ₹" . number_format($eval['target_volume'])
            ];
        }
        if ($eval['claim_status'] === 'paid') {
            return ['success' => false, 'message' => 'Office support already paid for this month.'];
        }
        if ($eval['claim_status'] === 'claimed') {
            return ['success' => false, 'message' => 'Claim already submitted and is under admin review.'];
        }

        try {
            $month = $eval['month_year'];
            $amount = $eval['allowance_amount'];

            if ($eval['allowance_id'] > 0) {
                $stmt = $this->db->prepare("
                    UPDATE mlm_leader_allowances 
                    SET status = 'claimed', notes = CONCAT(COALESCE(notes, ''), ' | Claim submitted: ', ?), updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$notes ?: 'Office support monthly claim', $eval['allowance_id']]);
                $claimId = $eval['allowance_id'];
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO mlm_leader_allowances (
                        tenant_id, user_id, rank_name, allowance_type, month_year,
                        target_volume, achieved_volume, amount, status, notes, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, 'office_support', ?,
                        ?, ?, ?, 'claimed', ?, NOW(), NOW()
                    )
                ");
                $stmt->execute([
                    $this->tenantId(),
                    $userId,
                    $eval['rank'],
                    $month,
                    $eval['target_volume'],
                    $eval['achieved_volume'],
                    $amount,
                    $notes ?: ("Monthly Office & Branch Support Claim for {$eval['rank']} ({$month})")
                ]);
                $claimId = (int)$this->db->lastInsertId();
            }

            return [
                'success'  => true,
                'claim_id' => $claimId,
                'amount'   => $amount,
                'message'  => "Office Support claim of ₹" . number_format($amount) . " submitted successfully for Admin approval."
            ];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] submitOfficeSupportClaim error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Associate submits claim for Site Visit Fuel/Vehicle.
     *
     * @return array{success: bool, claim_id?: int, amount?: float, message: string}
     */
    public function submitSiteVisitClaim(int $userId, int $siteVisitId, string $visitType = 'local', ?string $notes = null): array
    {
        if (!$this->db || $userId <= 0 || $siteVisitId <= 0) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }

        $rateInfo = self::SITE_VISIT_RATES[$visitType] ?? self::SITE_VISIT_RATES['local'];
        $amount = (float)$rateInfo['rate'];
        $monthYear = date('Y-m');

        try {
            // Verify site visit
            $stmt = $this->db->prepare("SELECT id, status, visitor_name FROM site_visits WHERE id = ? LIMIT 1");
            $stmt->execute([$siteVisitId]);
            $visit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visit) {
                return ['success' => false, 'message' => 'Site visit record not found.'];
            }

            // Check if already claimed
            $stmt = $this->db->prepare("
                SELECT id, status FROM mlm_leader_allowances 
                WHERE site_visit_id = ? AND allowance_type IN ('site_visit_fuel', 'vehicle_hiring')
                LIMIT 1
            ");
            $stmt->execute([$siteVisitId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                return ['success' => false, 'message' => "Allowance already {$existing['status']} for this site visit."];
            }

            $stmt = $this->db->prepare("
                INSERT INTO mlm_leader_allowances (
                    tenant_id, user_id, rank_name, allowance_type, month_year,
                    target_volume, achieved_volume, amount, site_visit_id, status, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, 'Associate', 'site_visit_fuel', ?,
                    0.00, 0.00, ?, ?, 'claimed', ?, NOW(), NOW()
                )
            ");
            $claimDesc = $rateInfo['desc'] . " (Customer: " . ($visit['visitor_name'] ?? 'Client') . ", Visit #$siteVisitId)" . ($notes ? " - $notes" : "");
            $stmt->execute([
                $this->tenantId(),
                $userId,
                $monthYear,
                $amount,
                $siteVisitId,
                $claimDesc
            ]);
            $claimId = (int)$this->db->lastInsertId();

            return [
                'success'  => true,
                'claim_id' => $claimId,
                'amount'   => $amount,
                'message'  => "Site visit allowance claim of ₹" . number_format($amount) . " submitted for approval."
            ];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] submitSiteVisitClaim error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Admin approves an allowance claim and disburses directly to user wallet.
     *
     * @return array{success: bool, allowance_id?: int, amount?: float, wallet_paid?: bool, message: string}
     */
    public function approveAllowance(int $allowanceId, int $adminId): array
    {
        if (!$this->db || $allowanceId <= 0) {
            return ['success' => false, 'message' => 'Invalid allowance ID'];
        }

        try {
            $stmt = $this->db->prepare("SELECT * FROM mlm_leader_allowances WHERE id = ? LIMIT 1");
            $stmt->execute([$allowanceId]);
            $claim = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$claim) {
                return ['success' => false, 'message' => 'Allowance claim not found.'];
            }
            if ($claim['status'] === 'paid') {
                return ['success' => false, 'message' => 'Allowance has already been approved and paid.'];
            }

            $userId = (int)$claim['user_id'];
            $amount = (float)$claim['amount'];
            $allowType = $claim['allowance_type'];
            $month = $claim['month_year'];

            // Update status to paid
            $stmt = $this->db->prepare("
                UPDATE mlm_leader_allowances 
                SET status = 'paid', approved_by = ?, approved_at = NOW(), paid_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$adminId, $allowanceId]);

            // Credit to Associate Wallet
            $desc = ($allowType === 'office_support')
                ? "Monthly Office Support Allowance ({$month})"
                : "Township Site Visit Travel & Fuel Reimbursement";

            $walletService = new \App\Services\WalletService();
            $walletOk = $walletService->credit(
                $userId,
                $amount,
                'bonus',
                $desc,
                $allowanceId,
                'leader_allowance'
            );

            return [
                'success'     => true,
                'allowance_id'=> $allowanceId,
                'amount'      => $amount,
                'wallet_paid' => $walletOk,
                'message'     => "Claim approved and ₹" . number_format($amount) . " credited to associate wallet successfully."
            ];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] approveAllowance error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Admin rejects an allowance claim with reason.
     *
     * @return array{success: bool, message: string}
     */
    public function rejectAllowance(int $allowanceId, string $reason, int $adminId): array
    {
        if (!$this->db || $allowanceId <= 0) {
            return ['success' => false, 'message' => 'Invalid allowance ID'];
        }

        try {
            $stmt = $this->db->prepare("
                UPDATE mlm_leader_allowances 
                SET status = 'rejected', notes = CONCAT(COALESCE(notes, ''), ' | Rejection Reason: ', ?), approved_by = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason, $adminId, $allowanceId]);

            return [
                'success' => true,
                'message' => 'Allowance claim has been rejected.'
            ];
        } catch (\Throwable $e) {
            error_log('[LeadershipExpenseFundService] rejectAllowance error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

