<?php
namespace App\Services\MLM;

use App\Core\Middleware\TenantContext;

class LeadershipSalaryService
{
    private $db;
    
    const TARGET_1_VOLUME = 1500000.00;
    const TARGET_1_DAYS = 60;
    const TARGET_1_PAYOUT = 5000.00;
    const TARGET_1_DURATION = 6;
    
    const TARGET_2_VOLUME = 3000000.00;
    const TARGET_2_DAYS = 100;
    const TARGET_2_PAYOUT = 5000.00;
    const TARGET_2_DURATION = 12;

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

    /**
     * Evaluate a user's eligibility for leadership salary targets
     * Called after a booking/commission event
     */
    public function evaluateTargets(int $userId): array
    {
        $results = [];
        $registeredAt = $this->getUserRegisteredDate($userId);
        if (!$registeredAt) return $results;
        
        $daysSinceRegistration = (new \DateTime())->diff(new \DateTime($registeredAt))->days;
        $totalVolume = $this->getUserTotalVolume($userId);
        
        // Target 1: ₹15L in 60 days
        if ($totalVolume >= self::TARGET_1_VOLUME && $daysSinceRegistration <= self::TARGET_1_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_1_VOLUME, $daysSinceRegistration, self::TARGET_1_PAYOUT, self::TARGET_1_DURATION);
        }
        
        // Target 2: ₹30L in 100 days
        if ($totalVolume >= self::TARGET_2_VOLUME && $daysSinceRegistration <= self::TARGET_2_DAYS) {
            $results[] = $this->createSalaryTarget($userId, self::TARGET_2_VOLUME, $daysSinceRegistration, self::TARGET_2_PAYOUT, self::TARGET_2_DURATION);
        }
        
        return $results;
    }
    
    private function createSalaryTarget(int $userId, float $targetVolume, int $achievedDays, float $monthlyPayout, int $durationMonths): array
    {
        // Check if already achieved this target
        $stmt = $this->db->prepare("SELECT id FROM salary_tracker WHERE user_id = ? AND target_volume = ? AND status = 'active'");
        $stmt->execute([$userId, $targetVolume]);
        if ($stmt->fetch()) {
            return ['target' => $targetVolume, 'status' => 'already_active'];
        }
        
        $startDate = date('Y-m-d', strtotime('+1 month'));
        $endDate = date('Y-m-d', strtotime("+$durationMonths months"));
        
        $stmt = $this->db->prepare("INSERT INTO salary_tracker (user_id, target_volume, achieved_in_days, achieved_date, monthly_payout, duration_months, start_date, end_date, status) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, 'active')");
        $stmt->execute([$userId, $targetVolume, $achievedDays, $monthlyPayout, $durationMonths, $startDate, $endDate]);
        
        $targetId = $this->db->lastInsertId();
        
        // Check for overlap with existing active targets
        $stmt = $this->db->prepare("SELECT id, monthly_payout, start_date, end_date FROM salary_tracker WHERE user_id = ? AND status = 'active' AND id != ?");
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
        $stmt = $this->db->query("SELECT * FROM salary_tracker WHERE status = 'active' AND start_date <= CURDATE() AND end_date >= CURDATE()");
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
            $stmt = $this->db->prepare("SELECT COUNT(*) as paid FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'performance_bonus' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') AND notes LIKE 'Leadership Salary%'");
            $stmt->execute([$userId]);
            if ($stmt->fetch(\PDO::FETCH_ASSOC)['paid'] > 0) continue;

            // --- MONTHLY QUALIFICATION CHECK ---
            // Salary is performance-linked: must hit minimum monthly sales volume
            $monthlyVolume = $this->getMonthlySalesVolume($userId);
            $minTarget = self::MONTHLY_TARGET_STARTER;
            if ($monthlyVolume < $minTarget) {
                // Withhold salary — target not met
                $this->db->prepare(
                    "INSERT INTO mlm_commission_ledger 
                     (beneficiary_user_id, source_user_id, commission_type, amount, level, status, notes, created_at)
                     VALUES (?, 0, 'salary_withheld', 0, 0, 'withheld', ?, NOW())"
                )->execute([$userId, "Leadership Salary WITHHELD — Monthly volume ₹" . number_format($monthlyVolume) . " < target ₹" . number_format($minTarget)]);
                error_log("LeadershipSalary: WITHHELD user #$userId — monthly volume ₹$monthlyVolume < target ₹$minTarget");
                continue;
            }
            
            try {
                $this->db->beginTransaction();
                
                // Credit wallet
                $stmt = $this->db->prepare("UPDATE user_wallets SET balance = balance + ?, total_credited = total_credited + ? WHERE user_id = ?");
                $stmt->execute([$totalMonthlyPayout, $totalMonthlyPayout, $userId]);
                
                // Create monthly commission entry for EACH source target (so accounting is transparent)
                $targetIds = array_column($targets, 'id');
                $stmt = $this->db->prepare("INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, level, status, notes, created_at) VALUES (?, ?, 'performance_bonus', ?, 1, 'approved', CONCAT('Leadership Salary [Targets: ', ?, '] - Monthly payout with overlap aggregation'), NOW())");
                $stmt->execute([$userId, 1, $totalMonthlyPayout, implode(',', $targetIds)]);
                
                $this->db->commit();
                $processed++;
                $totalAmount += $totalMonthlyPayout;
                
                error_log("LeadershipSalary: Paid user #$userId total ₹$totalMonthlyPayout for " . count($targets) . " overlapping target(s)");
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                error_log("LeadershipSalary: Failed to pay user #$userId: " . $e->getMessage());
            }
        }
        
        // Check for completed targets (end_date passed)
        $stmt = $this->db->query("UPDATE salary_tracker SET status = 'completed' WHERE status = 'active' AND end_date < CURDATE()");
        
        return ['processed' => $processed, 'total_amount' => $totalAmount];
    }
    
    private function getUserRegisteredDate(int $userId): ?string
    {
        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $stmt = $this->db->prepare("SELECT created_at FROM users WHERE id = ?{$tenantSql}");
        $stmt->execute($tid > 1 ? [$userId, $tid] : [$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ? $user['created_at'] : null;
    }
    
    private function getUserTotalVolume(int $userId): float
    {
        // Query plot_bookings via associates link (primary source for plot sales)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(pb.total_plot_value), 0) as total 
            FROM plot_bookings pb
            JOIN associates a ON a.id = pb.associate_id
            WHERE a.user_id = ? 
              AND pb.status NOT IN ('cancelled', 'defaulted')
        ");
        $stmt->execute([$userId]);
        $plotTotal = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // Also include legacy bookings table (CRM-style bookings)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(b.total_amount), 0) as total 
            FROM bookings b
            WHERE b.associate_id = ? 
              AND b.status IN ('confirmed', 'completed')
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
              AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
        ");
        $stmt->execute([$userId]);
        return (float)$stmt->fetch(\PDO::FETCH_ASSOC)['volume'];
    }
    
    private function datesOverlap(string $start1, string $end1, ?string $start2, ?string $end2): bool
    {
        if (!$start2 || !$end2) return false;
        return $start1 <= $end2 && $end1 >= $start2;
    }
    
    /**
     * Get salary status for a user
     */
    public function getUserSalaryStatus(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM salary_tracker WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $targets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total_salary_paid FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND commission_type = 'performance_bonus' AND notes LIKE 'Leadership Salary%'");
        $stmt->execute([$userId]);
        $totalPaid = (float)$stmt->fetch(\PDO::FETCH_ASSOC)['total_salary_paid'];
        
        return [
            'targets' => $targets,
            'total_salary_paid' => $totalPaid,
            'active_targets' => array_filter($targets, fn($t) => $t['status'] === 'active'),
        ];
    }
}
