<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

use \App\Traits\ServiceTenantTrait;

/**
 * MLMIncentiveService
 * Handles monthly business targets and salary-style incentive payouts.
 */
class MLMIncentiveService
{
    use \App\Traits\ServiceTenantTrait;

    protected $db;
    protected $logger;
    protected $rankCalculator;

    // Monthly Incentive Targets based on Rank (Mock values for "Salary" dashboard)
    protected $monthlyTargets = [
        'Associate' => ['target' => 100000, 'reward' => 2000],
        'Sr. Associate' => ['target' => 300000, 'reward' => 6000],
        'BDM' => ['target' => 700000, 'reward' => 15000],
        'Sr. BDM' => ['target' => 1500000, 'reward' => 35000],
        'Vice President' => ['target' => 3000000, 'reward' => 75000],
        'President' => ['target' => 5000000, 'reward' => 125000],
        'Site Manager' => ['target' => 10000000, 'reward' => 250000],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new \App\Services\LoggingService();
        $this->rankCalculator = new \App\Services\PerformanceRankCalculator();
        $this->ensureTableExists();
    }

    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS mlm_monthly_incentives (
            id INT(11) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            month TINYINT(2) NOT NULL,
            year SMALLINT(4) NOT NULL,
            rank_at_time VARCHAR(50) DEFAULT NULL,
            target_business DECIMAL(15,2) DEFAULT 0.00,
            achieved_business DECIMAL(15,2) DEFAULT 0.00,
            incentive_amount DECIMAL(15,2) DEFAULT 0.00,
            status ENUM('pending','approved','failed','paid') DEFAULT 'pending',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_user_month_year (user_id, month, year),
            KEY idx_user (user_id),
            KEY idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->query($sql);
    }

    /**
     * Calculate and record monthly incentive for a user.
     */
    public function calculateMonthlyIncentive($userId, $month = null, $year = null)
    {
        $month = $month ?: (int)date('m');
        $year = $year ?: (int)date('Y');

        try {
            // 1. Get User's Current Rank
            $rankData = $this->rankCalculator->calculateRank($userId);
            $currentRank = $rankData['rank'];

            if (!isset($this->monthlyTargets[$currentRank])) {
                return ['success' => false, 'message' => "No incentive target defined for rank: $currentRank"];
            }

            $target = $this->monthlyTargets[$currentRank]['target'];
            $reward = $this->monthlyTargets[$currentRank]['reward'];

            // 2. Calculate Monthly Business Volume (MBV)
            $mbv = $this->getMonthlyBusinessVolume($userId, $month, $year);

            // 3. Check if target achieved
            $status = ($mbv >= $target) ? 'approved' : 'failed';
            $incentiveAmount = ($mbv >= $target) ? $reward : 0;

            // 4. Record/Update incentive
            $tid = $this->tenantId();
            $tenantCol = $tid > 1 ? ', tenant_id' : '';
            $tenantVal = $tid > 1 ? ', ?' : '';
            $sql = "INSERT INTO mlm_monthly_incentives 
                    (user_id, month, year, rank_at_time, target_business, achieved_business, incentive_amount, status{$tenantCol}) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?{$tenantVal})
                    ON DUPLICATE KEY UPDATE 
                    achieved_business = VALUES(achieved_business),
                    incentive_amount = VALUES(incentive_amount),
                    status = IF(status = 'paid', 'paid', VALUES(status))";
            
            $tenantParams = $tid > 1 ? [$tid] : [];
            $this->db->query($sql, [$userId, $month, $year, $currentRank, $target, $mbv, $incentiveAmount, $status, ...$tenantParams]);

            return [
                'success' => true,
                'achieved' => ($mbv >= $target),
                'amount' => $incentiveAmount,
                'mbv' => $mbv,
                'target' => $target
            ];

        } catch (Exception $e) {
            $this->logger->error("Error calculating monthly incentive: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get business volume for a specific month.
     */
    public function getMonthlyBusinessVolume($userId, $month, $year)
    {
        $startDate = "$year-$month-01 00:00:00";
        $endDate = date("Y-m-t 23:59:59", strtotime($startDate));

        $downline = $this->rankCalculator->getDownlineIds($userId);
        if (empty($downline)) return 0;

        $placeholders = implode(',', array_fill(0, count($downline), '?'));
        
        // V2 Sales (Plot Bookings)
        $sqlV2 = "SELECT SUM(booking_amount) FROM plot_bookings 
                  WHERE associate_id IN ($placeholders) 
                  AND status IN ('confirmed', 'completed')
                  AND booking_date BETWEEN ? AND ?";
        
        $params = array_merge($downline, [$startDate, $endDate]);
        $v2Volume = (float)$this->db->fetchOne($sqlV2, $params)['SUM(booking_amount)'] ?: 0;

        try {
            // Legacy Sales
            $sqlLegacy = "SELECT SUM(sale_amount) FROM property_sales 
                          WHERE agent_id IN ($placeholders) 
                          AND created_at BETWEEN ? AND ?";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $legacyVolume = (float)$this->db->fetchOne($sqlLegacy, $params)['SUM(sale_amount)'] ?: 0;

        return $v2Volume + $legacyVolume;
    }

    /**
     * Get status for the "Salary Dashboard"
     */
    public function getIncentiveSummary($userId)
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        
        // Auto-update current month
        $this->calculateMonthlyIncentive($userId, $currentMonth, $currentYear);

        $sql = "SELECT * FROM mlm_monthly_incentives WHERE user_id = ? ORDER BY year DESC, month DESC LIMIT 12";
        return $this->db->select($sql, [$userId]);
    }

    /**
     * Trigger commission clawback for a specific installment or booking.
     * Delegates to MLMCommissionEngine.
     */
    public function triggerClawback($bookingId, $reason = 'EMI Default')
    {
        try {
            $engine = new \App\Services\MLM\MLMCommissionEngine();
            return $engine->processClawbacks(30);
        } catch (\Throwable $e) {
            return ['success' => false, 'processed' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Auto-promote associates based on team sales volume.
     * Delegates to MLMCommissionEngine.
     */
    public function autoPromote()
    {
        try {
            $engine = new \App\Services\MLM\MLMCommissionEngine();
            $result = $engine->runRankPromotions();
            return [
                'success' => true,
                'promoted' => (int)($result['promoted'] ?? 0),
                'unchanged' => (int)($result['unchanged'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'promoted' => 0, 'error' => $e->getMessage()];
        }
    }
}
