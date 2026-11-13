<?php
namespace App\Services\MLM;

use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class LeadershipSalaryService
{
    use ServiceTenantTrait;
    private $db;
    
    const TARGET_1_VOLUME = 1500000.00;
    const TARGET_1_DAYS = 60;
    const TARGET_1_PAYOUT = 5000.00;
    const TARGET_1_DURATION = 6;
    
    const TARGET_2_VOLUME = 3000000.00;
    const TARGET_2_DAYS = 100;
    const TARGET_2_PAYOUT = 5000.00;
    const TARGET_2_DURATION = 12;

    const TARGET_3_VOLUME = 5000000.00;
    const TARGET_3_DAYS = 150;
    const TARGET_3_PAYOUT = 8000.00;
    const TARGET_3_DURATION = 12;

    const TARGET_4_VOLUME = 7500000.00;
    const TARGET_4_DAYS = 200;
    const TARGET_4_PAYOUT = 12000.00;
    const TARGET_4_DURATION = 12;

    const TARGET_5_VOLUME = 10000000.00;
    const TARGET_5_DAYS = 300;
    const TARGET_5_PAYOUT = 20000.00;
    const TARGET_5_DURATION = 12;

    /** Official Diwali Dhamaka Salary Targets */
    public const DIWALI_DHAMAKA_TARGETS = [
        ['name' => 'Target 1', 'target_volume' => self::TARGET_1_VOLUME, 'days' => self::TARGET_1_DAYS, 'monthly_payout' => self::TARGET_1_PAYOUT, 'duration_months' => self::TARGET_1_DURATION, 'total_grant' => 30000.00],
        ['name' => 'Target 2', 'target_volume' => self::TARGET_2_VOLUME, 'days' => self::TARGET_2_DAYS, 'monthly_payout' => self::TARGET_2_PAYOUT, 'duration_months' => self::TARGET_2_DURATION, 'total_grant' => 60000.00],
        ['name' => 'Target 3', 'target_volume' => self::TARGET_3_VOLUME, 'days' => self::TARGET_3_DAYS, 'monthly_payout' => self::TARGET_3_PAYOUT, 'duration_months' => self::TARGET_3_DURATION, 'total_grant' => 96000.00],
        ['name' => 'Target 4', 'target_volume' => self::TARGET_4_VOLUME, 'days' => self::TARGET_4_DAYS, 'monthly_payout' => self::TARGET_4_PAYOUT, 'duration_months' => self::TARGET_4_DURATION, 'total_grant' => 144000.00],
        ['name' => 'Target 5', 'target_volume' => self::TARGET_5_VOLUME, 'days' => self::TARGET_5_DAYS, 'monthly_payout' => self::TARGET_5_PAYOUT, 'duration_months' => self::TARGET_5_DURATION, 'total_grant' => 240000.00],
    ];

    /** Monthly sales volume required to qualify for salary payout */
    const MONTHLY_TARGET_STARTER = 1500000.00;   // ₹15L
    const MONTHLY_TARGET_PRO = 5000000.00;        // ₹50L
    
    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance()->getConnection();
    }
    
    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function getActivePlanSnapshot(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM mlm_commission_plans WHERE status = 'active' LIMIT 1");
            $plan = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($plan) {
                return [
                    'plan_id' => $plan['id'],
                    'plan_version' => $plan['version'] ?? 1,
                    'rates' => [
                        'direct_sale_pct' => $plan['direct_sale_pct'] ?? null,
                        'override_l1_pct' => $plan['l1_pct'] ?? null,
                        'override_l2_pct' => $plan['l2_pct'] ?? null,
                        'override_l3_pct' => $plan['l3_pct'] ?? null,
                        'global_cap_pct' => $plan['global_cap_pct'] ?? null,
                    ],
                    'snapshot_at' => date('Y-m-d H:i:s'),
                ];
            }
        } catch (\Throwable $e) {
            error_log('getActivePlanSnapshot error: ' . $e->getMessage());
        }
        return ['plan_id' => null, 'plan_version' => 1, 'rates' => [], 'snapshot_at' => date('Y-m-d H:i:s')];
    }

    /**
     * Evaluate a user's eligibility for leadership salary targets
     * Called after a booking/commission event
     * S457: Evaluates 40:60 Leg-Balanced volume to prevent free-riding off a single junior
     */
    public function evaluateTargets(int $userId): array
    {
        $results = [];
        $registeredAt = $this->getUserRegisteredDate($userId);
        if (!$registeredAt) return $results;
        
        $daysSinceRegistration = (new \DateTime())->diff(new \DateTime($registeredAt))->days;
        
        // S457: Use 40:60 Leg-Balanced volume to prevent lazy single-leg exploitation
        $balanceData = $this->getBalancedTargetVolume($userId, 0.60);
        $totalVolume = (float)$balanceData['balanced_volume'];
        
        // Target 1: ₹15L in 60 days -> ₹5,000/mo for 6 months
        if ($totalVolume >= self::TARGET_1_VOLUME && $daysSinceRegistration <= self::TARGET_1_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_1_VOLUME, $daysSinceRegistration, self::TARGET_1_PAYOUT, self::TARGET_1_DURATION);
        }
        
        // Target 2: ₹30L in 100 days -> ₹5,000/mo for 12 months
        if ($totalVolume >= self::TARGET_2_VOLUME && $daysSinceRegistration <= self::TARGET_2_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_2_VOLUME, $daysSinceRegistration, self::TARGET_2_PAYOUT, self::TARGET_2_DURATION);
        }

        // Target 3: ₹50L in 150 days -> ₹8,000/mo for 12 months
        if ($totalVolume >= self::TARGET_3_VOLUME && $daysSinceRegistration <= self::TARGET_3_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_3_VOLUME, $daysSinceRegistration, self::TARGET_3_PAYOUT, self::TARGET_3_DURATION);
        }

        // Target 4: ₹75L in 200 days -> ₹12,000/mo for 12 months
        if ($totalVolume >= self::TARGET_4_VOLUME && $daysSinceRegistration <= self::TARGET_4_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_4_VOLUME, $daysSinceRegistration, self::TARGET_4_PAYOUT, self::TARGET_4_DURATION);
        }

        // Target 5: ₹1 Crore in 300 days -> ₹20,000/mo for 12 months
        if ($totalVolume >= self::TARGET_5_VOLUME && $daysSinceRegistration <= self::TARGET_5_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_5_VOLUME, $daysSinceRegistration, self::TARGET_5_PAYOUT, self::TARGET_5_DURATION);
        }
        
        return $results;
    }
    
    private function createSalaryTarget(int $userId, float $targetVolume, int $achievedDays, float $monthlyPayout, int $durationMonths): array
    {
        // Check if already achieved this target
        $stmt = $this->db->prepare("SELECT id FROM salary_tracker WHERE user_id = ? AND target_volume = ? AND status = 'active'" . $this->tenantSql());
        $stmt->execute([$userId, $targetVolume]);
        if ($stmt->fetch()) {
            return ['target' => $targetVolume, 'status' => 'already_active'];
        }
        
        $startDate = date('Y-m-d', strtotime('+1 month'));
        $endDate = date('Y-m-d', strtotime("+$durationMonths months"));
        
        $insertData = $this->tenantInsertData();
        $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
        $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
        $stmt = $this->db->prepare("INSERT INTO salary_tracker (user_id, target_volume, achieved_in_days, achieved_date, monthly_payout, duration_months, start_date, end_date, status{$extraCols}) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, 'active'{$extraVals})");
        $stmt->execute(array_merge([$userId, $targetVolume, $achievedDays, $monthlyPayout, $durationMonths, $startDate, $endDate], array_values($insertData)));
        
        $targetId = $this->db->lastInsertId();
        
        // Check for overlap with existing active targets
        $stmt = $this->db->prepare("SELECT id, monthly_payout, start_date, end_date FROM salary_tracker WHERE user_id = ? AND status = 'active' AND id != ?" . $this->tenantSql());
        $stmt->execute([$userId, $targetId]);
        $overlapping = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $combinedPayout = $monthlyPayout;
        foreach ($overlapping as $existing) {
            if ($this->datesOverlap($startDate, $endDate, $existing['start_date'], $existing['end_date'])) {
                $combinedPayout += (float)$existing['monthly_payout'];
            }
        }
        
        return [
            'target' => $targetVolume,
            'payout' => $monthlyPayout,
            'combined_payout' => $combinedPayout,
            'duration' => $durationMonths,
            'status' => 'created',
            'overlap_count' => count($overlapping),
            'overlap_combined' => $combinedPayout > $monthlyPayout,
        ];
    }
    
    /**
     * Process monthly salary payouts (called by cron)
     * Implements dynamic overlap: if multiple targets overlap, pay combined sum
     */
    public function processMonthlyPayouts(): array
    {
        $processed = 0;
        $totalAmount = 0;
        
        // Get all active salary trackers whose start_date <= today AND end_date >= today
        $stmt = $this->db->query("SELECT * FROM salary_tracker WHERE status = 'active' AND start_date <= CURDATE() AND end_date >= CURDATE()" . $this->tenantSql());
        $activeTargets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Group by user for overlap calculation
        $userTargets = [];
        foreach ($activeTargets as $target) {
            $userTargets[$target['user_id']][] = $target;
        }
        
        foreach ($userTargets as $userId => $targets) {
            $totalMonthlyPayout = 0;
            
            // SUM all overlapping target payouts (cumulative, not overwrite)
            foreach ($targets as $t) {
                $totalMonthlyPayout += (float)$t['monthly_payout'];
            }
            
            if ($totalMonthlyPayout <= 0) continue;
            
            // Check if already paid this month
            $stmt = $this->db->prepare("SELECT COUNT(*) as paid FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'performance_bonus' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') AND notes LIKE 'Leadership Salary%'" . $this->tenantSql());
            $stmt->execute([$userId]);
            if ($stmt->fetch(\PDO::FETCH_ASSOC)['paid'] > 0) continue;

            // --- S457: PERFORMANCE & ACTIVITY-LINKED MAINTENANCE CHECK ---
            // S457: Salary requires minimum active field engagement (sales volume OR verified site visits)
            $activity = $this->getMonthlyActivitySummary($userId);
            $threshold = $this->getMaintenanceThreshold($totalMonthlyPayout);

            $isMaintenanceMet = ($activity['sales_volume'] >= $threshold['min_volume'] || $activity['site_visits'] >= $threshold['min_visits']);

            if (!$isMaintenanceMet) {
                // Withhold salary — activity requirement not met
                $reason = "Monthly activity requirement not met: required min ₹" . number_format($threshold['min_volume']) . " sales OR {$threshold['min_visits']} verified site visits (Achieved: ₹" . number_format($activity['sales_volume']) . " sales, {$activity['site_visits']} visits)";
                
                $this->db->prepare("
                    UPDATE salary_tracker 
                    SET consecutive_inactive_months = consecutive_inactive_months + 1,
                        paused_reason = ?,
                        status = CASE WHEN consecutive_inactive_months >= 2 THEN 'paused' ELSE status END
                    WHERE user_id = ? AND status = 'active'
                ")->execute([$reason, $userId]);

                $snapshot = $this->getActivePlanSnapshot();
                $insertData = $this->tenantInsertData();
                $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
                $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
                $this->db->prepare(
                    "INSERT INTO mlm_commission_ledger 
                     (beneficiary_user_id, source_user_id, commission_type, amount, level, status, notes, created_at, plan_id, plan_version, plan_snapshot, calculation_engine{$extraCols})
                     VALUES (?, 1, 'salary_withheld', 0, 0, 'missed', ?, NOW(), ?, ?, ?, 'leadership_salary_service'{$extraVals})"
                )->execute(array_merge([$userId, "Leadership Salary WITHHELD — " . $reason, $snapshot['plan_id'], $snapshot['plan_version'], json_encode($snapshot)], array_values($insertData)));
                
                error_log("LeadershipSalary: WITHHELD user #$userId — $reason");
                continue;
            }

            // Activity is verified! Reset consecutive inactive months and update verification timestamp
            $this->db->prepare("
                UPDATE salary_tracker 
                SET consecutive_inactive_months = 0,
                    last_activity_verified_at = NOW(),
                    paused_reason = NULL,
                    status = 'active'
                WHERE user_id = ? AND status IN ('active', 'paused')
            ")->execute([$userId]);
            
            try {
                $this->db->beginTransaction();
                
                // Credit canonical wallet via WalletService (with fallback to user_wallets)
                try {
                    $walletService = new \App\Services\WalletService();
                    $walletService->credit($userId, $totalMonthlyPayout, 'bonus', "Monthly Leadership Salary payout (Corporate Performance Plan) — {$activity['month']}");
                } catch (\Throwable $wEx) {
                    error_log("LeadershipSalary: WalletService credit fallback: " . $wEx->getMessage());
                }

                // Maintain backwards compatibility with legacy user_wallets if table present
                try {
                    $stmt = $this->db->prepare("UPDATE user_wallets SET balance = balance + ?, total_credited = total_credited + ? WHERE user_id = ?" . $this->tenantSql());
                    $stmt->execute([$totalMonthlyPayout, $totalMonthlyPayout, $userId]);
                } catch (\Throwable $wEx2) {}
                
                // Create monthly commission entry for EACH source target (so accounting is transparent)
                $targetIds = array_column($targets, 'id');
                $snapshot = $this->getActivePlanSnapshot();
                $insertData = $this->tenantInsertData();
                $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
                $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
                $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, level, status, notes, created_at, plan_id, plan_version, plan_snapshot, calculation_engine{$extraCols}) VALUES (?, ?, 'performance_bonus', ?, 1, 'approved', CONCAT('Leadership Salary [Targets: ', ?, '] - Monthly payout with overlap aggregation'), NOW(), ?, ?, ?, 'leadership_salary_service'{$extraVals})");
                $stmt->execute(array_merge([$userId, 1, $totalMonthlyPayout, implode(',', $targetIds), $snapshot['plan_id'], $snapshot['plan_version'], json_encode($snapshot)], array_values($insertData)));
                
                $this->db->commit();
                $processed++;
                $totalAmount += $totalMonthlyPayout;
                
                error_log("LeadershipSalary: Paid user #$userId total ₹$totalMonthlyPayout for " . count($targets) . " overlapping target(s)");
                
            } catch (\Throwable $e) {
                $this->db->rollBack();
                error_log("LeadershipSalary: Failed to pay user #$userId: " . $e->getMessage());
            }
        }
        
        // Check for completed targets (end_date passed)
        $stmt = $this->db->query("UPDATE salary_tracker SET status = 'completed' WHERE status = 'active' AND end_date < CURDATE()" . $this->tenantSql());
        
        return ['processed' => $processed, 'total_amount' => $totalAmount];
    }
    
    private function getUserRegisteredDate(int $userId): ?string
    {
        $stmt = $this->db->prepare("SELECT created_at FROM users WHERE id = ?" . $this->tenantSql());
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ? $user['created_at'] : null;
    }
    
    private function getUserTotalVolume(int $userId): float
    {
        $tid = $this->tenantId();
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(pb.total_plot_value), 0) as total 
            FROM plot_bookings pb
            JOIN associate_profiles ap ON ap.legacy_id = pb.associate_id OR ap.user_id = pb.associate_id OR ap.id = pb.associate_id
            WHERE ap.user_id = ? 
              AND pb.status NOT IN ('cancelled', 'defaulted')" . ($tid > 1 ? " AND pb.tenant_id = ?" : "") . "
        ");
        $params = [$userId];
        if ($tid > 1) { $params[] = $tid; }
        $stmt->execute($params);
        $plotTotal = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // Also include legacy bookings table (CRM-style bookings)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(b.total_amount), 0) as total 
            FROM bookings b
            WHERE b.associate_id = ? 
              AND b.status IN ('confirmed', 'completed')" . $this->tenantSql() . "
        ");
        $stmt->execute([$userId]);
        $bookingTotal = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        return $plotTotal + $bookingTotal;
    }

    /**
     * Get the associate's total sales volume in the current calendar month.
     * Uses mlm_commission_ledger for direct_sale entries (authoritative source).
     */
    private function getMonthlySalesVolume(int $userId): float
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(sale_amount), 0) as volume 
            FROM mlm_commission_ledger 
            WHERE beneficiary_user_id = ? 
              AND commission_type = 'direct_sale' 
              AND status = 'approved'
              AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')" . $this->tenantSql() . "
        ");
        $stmt->execute([$userId]);
        return (float)$stmt->fetch(\PDO::FETCH_ASSOC)['volume'];
    }
    
    private function datesOverlap(string $start1, string $end1, ?string $start2, ?string $end2): bool
    {
        if (!$start2 || !$end2) return false;
        return $start1 <= $end2 && $end1 >= $start2;
    }
    
    public function getMaintenanceThreshold(float $monthlyPayout): array
    {
        if ($monthlyPayout <= 5000.0) {
            return ['min_volume' => 50000.0, 'min_visits' => 2];
        } elseif ($monthlyPayout <= 8000.0) {
            return ['min_volume' => 100000.0, 'min_visits' => 4];
        } elseif ($monthlyPayout <= 12000.0) {
            return ['min_volume' => 150000.0, 'min_visits' => 5];
        } else {
            return ['min_volume' => 200000.0, 'min_visits' => 6];
        }
    }

    public function getMonthlyActivitySummary(int $userId, string $monthYear = ''): array
    {
        if (empty($monthYear)) {
            $monthYear = date('Y-m');
        }

        $salesVolume = $this->getMonthlySalesVolume($userId);

        // Also check direct plot bookings volume
        $bookingVol = 0.0;
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(pb.booking_amount), 0) AS direct_vol
                FROM plot_bookings pb
                WHERE pb.associate_id = ?
                  AND DATE_FORMAT(pb.created_at, '%Y-%m') = ?
                  AND pb.status NOT IN ('cancelled', 'defaulted', 'transferred')
            ");
            $stmt->execute([$userId, $monthYear]);
            $bookingVol = (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        $totalVol = max($salesVolume, $bookingVol);

        // Verified site visits in the month
        $visitCount = 0;
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS cnt
                FROM site_visits
                WHERE (user_id = ? OR agent_id = ? OR assigned_to = ?)
                  AND DATE_FORMAT(COALESCE(completed_at, visit_date, created_at), '%Y-%m') = ?
                  AND status IN ('completed', 'confirmed', 'verified')
            ");
            $stmt->execute([$userId, $userId, $userId, $monthYear]);
            $visitCount = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {}

        return [
            'month'        => $monthYear,
            'sales_volume' => $totalVol,
            'site_visits'  => $visitCount,
        ];
    }

    public function getBalancedTargetVolume(int $userId, float $maxSingleLegRatio = 0.60): array
    {
        $personalVol = $this->getUserPersonalVolume($userId);
        $legs = $this->getDirectLegVolumes($userId);

        if (empty($legs)) {
            return [
                'total_raw_volume'  => $personalVol,
                'balanced_volume'   => $personalVol,
                'personal_volume'   => $personalVol,
                'strongest_leg_vol' => 0.0,
                'other_legs_vol'    => $personalVol,
                'legs_count'        => 0,
                'balanced'          => true,
            ];
        }

        $totalRaw = $personalVol;
        foreach ($legs as $lVol) {
            $totalRaw += $lVol;
        }

        $legValues = array_values($legs);
        rsort($legValues);
        $strongestLegVol = $legValues[0] ?? 0.0;
        $otherLegsVol = $personalVol;
        for ($i = 1; $i < count($legValues); $i++) {
            $otherLegsVol += $legValues[$i];
        }

        // Capped strongest leg based on 40:60 rule
        $maxAllowedFromStrongest = $otherLegsVol > 0 ? ($otherLegsVol * 1.5) : ($personalVol > 0 ? ($personalVol * 1.5) : 0.0);
        $cappedStrongest = min($strongestLegVol, $maxAllowedFromStrongest > 0 ? $maxAllowedFromStrongest : $strongestLegVol);
        $balancedVolume = $otherLegsVol + $cappedStrongest;

        return [
            'total_raw_volume'  => $totalRaw,
            'balanced_volume'   => round($balancedVolume, 2),
            'personal_volume'   => $personalVol,
            'strongest_leg_vol' => $strongestLegVol,
            'other_legs_vol'    => $otherLegsVol,
            'legs_count'        => count($legs),
            'balanced'          => ($strongestLegVol <= ($totalRaw * $maxSingleLegRatio)),
        ];
    }

    public function getUserPersonalVolume(int $userId): float
    {
        $tid = $this->tenantId();
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(pb.total_plot_value), 0) as total 
                FROM plot_bookings pb
                WHERE pb.associate_id = ? 
                  AND pb.status NOT IN ('cancelled', 'defaulted')" . ($tid > 1 ? " AND pb.tenant_id = ?" : "") . "
            ");
            $params = [$userId];
            if ($tid > 1) { $params[] = $tid; }
            $stmt->execute($params);
            return (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getDirectLegVolumes(int $userId): array
    {
        $tid = $this->tenantId();
        $legVolumes = [];
        try {
            $stmt = $this->db->prepare("
                SELECT id 
                FROM users 
                WHERE referred_by = ? AND status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
            ");
            $params = [$userId];
            if ($tid > 1) { $params[] = $tid; }
            $stmt->execute($params);
            $children = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($children as $childId) {
                $legVolumes[(int)$childId] = $this->getSubtreeVolume((int)$childId);
            }
        } catch (\Throwable $e) {
            error_log("LeadershipSalary: getDirectLegVolumes error: " . $e->getMessage());
        }
        return $legVolumes;
    }

    public function getSubtreeVolume(int $rootUserId): float
    {
        $total = $this->getUserTotalVolume($rootUserId);
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM users WHERE referred_by = ? AND status = 'active'
            ");
            $stmt->execute([$rootUserId]);
            $descendants = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($descendants as $dId) {
                $total += $this->getSubtreeVolume((int)$dId);
            }
        } catch (\Throwable $e) {}
        return $total;
    }

    /**
     * Get salary status for a user
     */
    public function getUserSalaryStatus(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM salary_tracker WHERE user_id = ? ORDER BY created_at DESC" . $this->tenantSql());
        $stmt->execute([$userId]);
        $targets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total_salary_paid FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'performance_bonus' AND notes LIKE 'Leadership Salary%'" . $this->tenantSql());
        $stmt->execute([$userId]);
        $totalPaid = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['total_salary_paid'];
        
        return [
            'targets' => $targets,
            'total_salary_paid' => $totalPaid,
            'active_targets' => array_filter($targets, fn($t) => $t['status'] === 'active'),
        ];
    }

    /**
     * Get complete Salary & Incentive Dashboard data for Associate Portal
     *
     * @return array<string, mixed>
     */
    public function getAssociateSalaryDashboardData(int $userId): array
    {
        // 1. User details & Registration timeframe
        $regDateStr = $this->getUserRegisteredDate($userId) ?: date('Y-m-d');
        $regDate = new \DateTime($regDateStr);
        $now = new \DateTime();
        $daysSinceReg = $now->diff($regDate)->days;

        // 2. 40:60 Leg Balance Volume
        $legBal = $this->getBalancedTargetVolume($userId, 0.60);
        $balancedVolume = (float)($legBal['balanced_volume'] ?? 0.0);
        $personalVolume = (float)($legBal['personal_volume'] ?? 0.0);

        // 3. Targets Evaluation
        $targetsStatus = [];
        $currentTarget = null;
        $activeTargetFound = false;

        foreach (self::DIWALI_DHAMAKA_TARGETS as $idx => $t) {
            $tVol = (float)$t['target_volume'];
            $tDays = (int)$t['days'];
            $daysLeft = max(0, $tDays - $daysSinceReg);
            $isTimeExpired = ($daysSinceReg > $tDays);
            $progressPct = min(100.0, round(($balancedVolume / max(1.0, $tVol)) * 100.0, 1));
            $isAchieved = ($balancedVolume >= $tVol);

            $status = 'locked';
            if ($isAchieved) {
                $status = 'achieved';
            } elseif (!$isTimeExpired) {
                $status = 'in_progress';
                if (!$activeTargetFound) {
                    $currentTarget = $idx;
                    $activeTargetFound = true;
                }
            } else {
                $status = 'expired';
            }

            $targetsStatus[] = [
                'target_index'   => $idx + 1,
                'name'           => "Target " . ($idx + 1),
                'target_volume'  => $tVol,
                'days'           => $tDays,
                'days_left'      => $daysLeft,
                'is_time_expired'=> $isTimeExpired,
                'monthly_payout' => (float)$t['monthly_payout'],
                'duration_months'=> (int)$t['duration_months'],
                'total_grant'    => (float)$t['total_grant'],
                'balanced_volume'=> $balancedVolume,
                'progress_pct'   => $progressPct,
                'status'         => $status,
                'is_achieved'    => $isAchieved,
            ];
        }

        if ($currentTarget === null) {
            $currentTarget = 0; // Default to Target 1
        }

        // 4. Monthly Maintenance Status
        $maintThreshold = $this->getMaintenanceThreshold(5000.00);
        $activity = $this->getMonthlyActivitySummary($userId);
        $maintQualified = !empty($activity['maintenance_qualified']);

        // 5. Active Salary Grants
        $grants = [];
        $totalEarned = 0.0;
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM salary_tracker 
                WHERE user_id = ? 
                ORDER BY created_at DESC" . $this->tenantSql()
            );
            $stmt->execute([$userId]);
            $grants = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($grants as $g) {
                $totalEarned += (float)($g['paid_installments'] ?? 0) * (float)($g['monthly_payout'] ?? 0);
            }
        } catch (\Throwable $e) {}

        // 6. 30-Day Fast-Start Kickstart Bonus Status
        $fastStart = [];
        try {
            $engine = new MLMCommissionEngine();
            $fastStart = $engine->evaluateFastStartKickstart($userId);
        } catch (\Throwable $e) {
            $fastStart = ['success' => false];
        }

        return [
            'user_id'           => $userId,
            'registration_date' => $regDateStr,
            'days_since_reg'    => $daysSinceReg,
            'leg_balance'       => $legBal,
            'balanced_volume'   => $balancedVolume,
            'personal_volume'   => $personalVolume,
            'targets'           => $targetsStatus,
            'current_target'    => $targetsStatus[$currentTarget] ?? $targetsStatus[0],
            'maintenance'       => [
                'threshold'     => $maintThreshold,
                'activity'      => $activity,
                'is_qualified'  => $maintQualified,
            ],
            'grants'            => $grants,
            'total_salary_earned'=> $totalEarned,
            'fast_start'        => $fastStart,
        ];
    }
}
