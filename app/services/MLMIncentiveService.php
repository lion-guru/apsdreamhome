<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

/**
 * MLMIncentiveService - Monthly business targets and salary-style incentive payouts
 */
class MLMIncentiveService
{
    private $conn;

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
        $db = Database::getInstance();
        $this->conn = method_exists($db, 'getConnection') ? $db->getConnection() : $db;
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        try {
            $this->conn->query("SELECT 1 FROM mlm_monthly_incentives LIMIT 1");
        } catch (\Throwable $e) {
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
            $this->conn->query($sql);
        }
    }

    public function calculateMonthlyIncentive(int $userId, ?int $month = null, ?int $year = null): array
    {
        $month = $month ?: (int)date('m');
        $year = $year ?: (int)date('Y');

        try {
            $currentRank = $this->getUserRank($userId);
            if (!isset($this->monthlyTargets[$currentRank])) {
                return ['success' => false, 'message' => "No incentive target defined for rank: $currentRank"];
            }

            $target = $this->monthlyTargets[$currentRank]['target'];
            $reward = $this->monthlyTargets[$currentRank]['reward'];
            $mbv = $this->getMonthlyBusinessVolume($userId, $month, $year);
            $achieved = $mbv >= $target;
            $incentiveAmount = $achieved ? $reward : 0;

            $stmt = $this->conn->prepare("INSERT INTO mlm_monthly_incentives 
                (user_id, month, year, rank_at_time, target_business, achieved_business, incentive_amount, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                achieved_business = VALUES(achieved_business),
                incentive_amount = VALUES(incentive_amount),
                status = IF(status = 'paid', 'paid', VALUES(status))");
            $stmt->execute([$userId, $month, $year, $currentRank, $target, $mbv, $incentiveAmount, $achieved ? 'approved' : 'failed']);

            return [
                'success' => true,
                'achieved' => $achieved,
                'amount' => $incentiveAmount,
                'mbv' => $mbv,
                'target' => $target,
                'rank' => $currentRank,
            ];
        } catch (Exception $e) {
            error_log("MLMIncentiveService::calculateMonthlyIncentive error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getMonthlyBusinessVolume(int $userId, int $month, int $year): float
    {
        $startDate = "$year-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $downline = $this->getDownlineIds($userId);
        if (empty($downline)) return 0;

        $placeholders = implode(',', array_fill(0, count($downline), '?'));
        
        try {
            $sql = "SELECT COALESCE(SUM(booking_amount), 0) FROM plot_bookings 
                    WHERE associate_id IN ($placeholders) 
                    AND status IN ('confirmed', 'completed')
                    AND booking_date BETWEEN ? AND ?";
            $params = array_merge($downline, [$startDate, $endDate]);
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return (float)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log("MLMIncentiveService::getMonthlyBusinessVolume error: " . $e->getMessage());
            return 0;
        }
    }

    public function getIncentiveSummary(int $userId): array
    {
        $this->calculateMonthlyIncentive($userId);
        $stmt = $this->conn->prepare("SELECT * FROM mlm_monthly_incentives WHERE user_id = ? ORDER BY year DESC, month DESC LIMIT 12");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyTargets(): array
    {
        return $this->monthlyTargets;
    }

    private function getUserRank(int $userId): string
    {
        try {
            $stmt = $this->conn->prepare("SELECT current_rank FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rank = $stmt->fetchColumn();
            return $rank ?: 'Associate';
        } catch (\Throwable $e) {
            return 'Associate';
        }
    }

    private function getDownlineIds(int $userId): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?");
            $stmt->execute([$userId]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map('intval', $ids);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
