<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

/**
 * MLMIncentiveService
 * Handles monthly business targets and salary-style incentive payouts.
 */
class MLMIncentiveService
{
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
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            month INT NOT NULL,
            year INT NOT NULL,
            rank_at_time VARCHAR(50),
            target_business DECIMAL(15,2),
            achieved_business DECIMAL(15,2),
            incentive_amount DECIMAL(15,2),
            status ENUM('pending', 'approved', 'paid', 'failed') DEFAULT 'pending',
            remarks TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_month_year (user_id, month, year)
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
            $sql = "INSERT INTO mlm_monthly_incentives 
                    (user_id, month, year, rank_at_time, target_business, achieved_business, incentive_amount, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    achieved_business = VALUES(achieved_business),
                    incentive_amount = VALUES(incentive_amount),
                    status = IF(status = 'paid', 'paid', VALUES(status))";
            
            $this->db->query($sql, [$userId, $month, $year, $currentRank, $target, $mbv, $incentiveAmount, $status]);

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

        // Legacy Sales
        $sqlLegacy = "SELECT SUM(sale_amount) FROM property_sales 
                      WHERE agent_id IN ($placeholders) 
                      AND created_at BETWEEN ? AND ?";
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
}
