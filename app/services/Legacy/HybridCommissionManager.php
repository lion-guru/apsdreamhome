<?php

namespace App\Services\Legacy;
/**
 * APS Dream Home - Hybrid Commission System
 * Dual Commission System: MLM + Traditional Local Market
 *
 * Features:
 * - Flexible commission type selection (MLM, Traditional, Hybrid)
 * - Regional performance tracking
 * - Unified dashboard and analytics
 * - Multi-tier commission distribution
 */

class HybridCommissionManager {
    private $db;
    private $logger;

    public function __construct($db = null, $logger = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->createHybridTables();
    }

    /**
     * Create hybrid commission system tables
     */
    private function createHybridTables() {
        // Create traditional commission system table
        $sql = "CREATE TABLE IF NOT EXISTS traditional_commissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agent_id INT NOT NULL,
            property_id INT,
            commission_amount DECIMAL(10,2) NOT NULL,
            commission_rate DECIMAL(5,2) NOT NULL,
            region VARCHAR(100),
            sale_date DATE,
            status ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (agent_id) REFERENCES user(uid) ON DELETE CASCADE,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
        )";
        $this->db->execute($sql);

        // Create regional performance tracking table
        $sql = "CREATE TABLE IF NOT EXISTS regional_performance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            region VARCHAR(100) NOT NULL,
            agent_id INT NOT NULL,
            total_sales DECIMAL(15,2) DEFAULT 0,
            total_commission DECIMAL(15,2) DEFAULT 0,
            performance_bonus DECIMAL(10,2) DEFAULT 0,
            quarter VARCHAR(7),
            year INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (agent_id) REFERENCES user(uid) ON DELETE CASCADE,
            INDEX idx_region_agent (region, agent_id),
            INDEX idx_quarter_year (quarter, year)
        )";
        $this->db->execute($sql);

        // Create commission preferences table
        $sql = "CREATE TABLE IF NOT EXISTS commission_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            commission_preference ENUM('mlm', 'traditional', 'hybrid') DEFAULT 'mlm',
            preferred_region VARCHAR(100),
            auto_approve_traditional BOOLEAN DEFAULT FALSE,
            minimum_payout_threshold DECIMAL(10,2) DEFAULT 1000.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(uid) ON DELETE CASCADE
        )";
        $this->db->execute($sql);

        // Add commission preference to user table if not exists
        try {
            $check_column = $this->db->fetch("SHOW COLUMNS FROM user LIKE 'commission_preference'");
            if (!$check_column) {
                $sql = "ALTER TABLE user ADD COLUMN commission_preference ENUM('mlm', 'traditional', 'hybrid') DEFAULT 'mlm' AFTER commission_rate";
                $this->db->execute($sql);
            }
        } catch (\Exception $e) {
            // Column might already exist or table might be missing
        }
    }

    /**
     * Calculate commission based on user preference
     */
    public function calculateCommission($propertySale, $user) {
        $preference = $user['commission_preference'] ?? 'mlm';

        if ($preference === 'hybrid') {
            // Calculate both commissions
            $mlmCommission = $this->calculateMLMCommission($propertySale, $user);
            $traditionalCommission = $this->calculateTraditionalCommission($propertySale, $user);

            return [
                'success' => true,
                'commission_type' => 'hybrid',
                'mlm_commission' => $mlmCommission,
                'traditional_commission' => $traditionalCommission,
                'total_commission' => ($mlmCommission['amount'] ?? 0) + ($traditionalCommission['amount'] ?? 0),
                'breakdown' => [
                    'mlm_percentage' => $mlmCommission['percentage'] ?? 0,
                    'traditional_percentage' => $traditionalCommission['percentage'] ?? 0,
                    'total_earnings' => ($mlmCommission['amount'] ?? 0) + ($traditionalCommission['amount'] ?? 0)
                ]
            ];
        } else {
            // Calculate single type commission
            return $this->calculateSingleCommission($propertySale, $user, $preference);
        }
    }

    /**
     * Calculate MLM commission
     */
    private function calculateMLMCommission($propertySale, $user) {
        // Fallback MLM calculation (standard 7% or user-specific rate)
        $commissionRate = $user['commission_rate'] ?? 7.0;
        $amount = $propertySale['amount'] * ($commissionRate / 100);

        return [
            'success' => true,
            'type' => 'mlm',
            'amount' => $amount,
            'percentage' => $commissionRate,
            'description' => "MLM Network Commission at {$commissionRate}%"
        ];
    }

    /**
     * Calculate Traditional commission
     */
    private function calculateTraditionalCommission($propertySale, $user) {
        $region = $user['preferred_region'] ?? 'Default';
        $baseRate = $this->getRegionalCommissionRate($region);
        $amount = $propertySale['amount'] * ($baseRate / 100);

        // Add performance bonus if applicable
        $performanceBonus = $this->calculatePerformanceBonus($user['uid'], $region);
        $totalAmount = $amount + $performanceBonus;

        return [
            'success' => true,
            'type' => 'traditional',
            'amount' => $totalAmount,
            'base_amount' => $amount,
            'performance_bonus' => $performanceBonus,
            'percentage' => $baseRate,
            'region' => $region,
            'description' => "Traditional Commission at {$baseRate}% for {$region} region"
        ];
    }

    /**
     * Calculate single type commission
     */
    private function calculateSingleCommission($propertySale, $user, $type) {
        if ($type === 'traditional') {
            return $this->calculateTraditionalCommission($propertySale, $user);
        } else {
            return $this->calculateMLMCommission($propertySale, $user);
        }
    }

    /**
     * Get regional commission rate
     */
    private function getRegionalCommissionRate($region) {
        $regionalRates = [
            'North' => 8.0,
            'South' => 7.5,
            'East' => 8.5,
            'West' => 7.0,
            'Central' => 7.5,
            'Default' => 7.0
        ];

        return $regionalRates[$region] ?? $regionalRates['Default'];
    }

    private function calculatePerformanceBonus($userId, $region) {
        $currentQuarter = date('Y-m');
        $currentYear = date('Y');

        // Get current quarter performance
        $sql = "SELECT total_sales FROM regional_performance
                WHERE agent_id = ? AND region = ? AND quarter = ? AND year = ?";
        $performance = $this->db->fetch($sql, [$userId, $region, $currentQuarter, $currentYear]);

        if ($performance) {
            $totalSales = $performance['total_sales'];

            // Calculate bonus based on sales volume
            if ($totalSales > 10000000) return 50000; // 50k bonus for > 1cr sales
            if ($totalSales > 5000000) return 25000;  // 25k bonus for > 50l sales
            if ($totalSales > 1000000) return 10000;  // 10k bonus for > 10l sales
        }

        return 0;
    }

    /**
     * Update regional performance
     */
    public function updateRegionalPerformance($userId, $region, $saleAmount, $commissionAmount) {
        $currentQuarter = "Q" . ceil(date('n') / 3);
        $currentYear = date('Y');

        // Check if performance record exists
        $sql = "SELECT id FROM regional_performance
                WHERE agent_id = ? AND region = ? AND quarter = ? AND year = ?";
        $existing = $this->db->fetch($sql, [$userId, $region, $currentQuarter, $currentYear]);

        if ($existing) {
            // Update existing record
            $sql = "UPDATE regional_performance
                    SET total_sales = total_sales + ?,
                        total_commission = total_commission + ?
                    WHERE id = ?";
            $this->db->execute($sql, [$saleAmount, $commissionAmount, $existing['id']]);
        } else {
            // Create new record
            $sql = "INSERT INTO regional_performance (agent_id, region, total_sales, total_commission, quarter, year)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $this->db->execute($sql, [$userId, $region, $saleAmount, $commissionAmount, $currentQuarter, $currentYear]);
        }

        // Recalculate bonus for the quarter
        $this->updatePerformanceBonus($userId, $region, $currentQuarter, $currentYear);
    }

    /**
     * Update performance bonus based on updated sales
     */
    private function updatePerformanceBonus($userId, $region, $quarter, $year) {
        $sql = "SELECT total_sales FROM regional_performance
                WHERE agent_id = ? AND region = ? AND quarter = ? AND year = ?";
        $performance = $this->db->fetch($sql, [$userId, $region, $quarter, $year]);

        if ($performance) {
            $totalSales = $performance['total_sales'];
            $bonus = 0;

            if ($totalSales > 10000000) $bonus = 50000;
            elseif ($totalSales > 5000000) $bonus = 25000;
            elseif ($totalSales > 1000000) $bonus = 10000;

            if ($bonus > 0) {
                $sql = "UPDATE regional_performance SET performance_bonus = ?
                        WHERE agent_id = ? AND region = ? AND quarter = ? AND year = ?";
                $this->db->execute($sql, [$bonus, $userId, $region, $quarter, $year]);
            }
        }
    }

    /**
     * Get unified dashboard data
     */
    public function getUnifiedDashboard($userId) {
        $user = $this->getUserData($userId);
        $preference = $user['commission_preference'] ?? 'mlm';

        $dashboard = [
            'user_info' => $user,
            'commission_preference' => $preference,
            'mlm_performance' => [],
            'traditional_performance' => [],
            'combined_earnings' => 0,
            'network_vs_direct' => [],
            'regional_performance' => []
        ];

        // Get MLM performance
        if ($preference === 'mlm' || $preference === 'hybrid') {
            $dashboard['mlm_performance'] = $this->getMLMAnalytics($userId);
        }

        // Get Traditional performance
        if ($preference === 'traditional' || $preference === 'hybrid') {
            $dashboard['traditional_performance'] = $this->getTraditionalAnalytics($userId);
            $dashboard['regional_performance'] = $this->getRegionalAnalytics($userId);
        }

        // Calculate combined earnings
        if ($preference === 'hybrid') {
            $dashboard['combined_earnings'] =
                ($dashboard['mlm_performance']['total_earnings'] ?? 0) +
                ($dashboard['traditional_performance']['total_earnings'] ?? 0);
        }

        return $dashboard;
    }

    /**
     * Get user data
     */
    private function getUserData($userId) {
        $sql = "SELECT u.*, cp.commission_preference, cp.preferred_region
                FROM user u
                LEFT JOIN commission_preferences cp ON u.uid = cp.user_id
                WHERE u.uid = ?";
        return $this->db->fetch($sql, [$userId]) ?? [];
    }

    /**
     * Get MLM analytics
     */
    private function getMLMAnalytics($userId) {
        // Fallback MLM analytics using common mlm_commissions table
        $sql = "SELECT
                COUNT(*) as total_commissions,
                COALESCE(SUM(commission_amount), 0) as total_earnings,
                COALESCE(AVG(commission_amount), 0) as average_commission
                FROM mlm_commissions
                WHERE associate_id = ? AND status = 'paid'";

        return $this->db->fetch($sql, [$userId]) ?? [];
    }

    /**
     * Get Traditional analytics
     */
    private function getTraditionalAnalytics($userId) {
        $sql = "SELECT
                COUNT(*) as total_commissions,
                COALESCE(SUM(commission_amount), 0) as total_earnings,
                COALESCE(AVG(commission_amount), 0) as average_commission,
                COUNT(DISTINCT region) as regions_covered
                FROM traditional_commissions
                WHERE agent_id = ? AND status = 'paid'";

        return $this->db->fetch($sql, [$userId]) ?? [];
    }

    /**
     * Get regional analytics
     */
    private function getRegionalAnalytics($userId) {
        $sql = "SELECT
                region,
                COUNT(*) as total_sales,
                COALESCE(SUM(total_sales), 0) as sales_volume,
                COALESCE(SUM(total_commission), 0) as total_commission,
                COALESCE(SUM(performance_bonus), 0) as total_bonus
                FROM regional_performance
                WHERE agent_id = ?
                GROUP BY region
                ORDER BY sales_volume DESC";

        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Update user commission preference
     */
    public function updateCommissionPreference($userId, $preference, $region = null) {
        $sql = "INSERT INTO commission_preferences
                (user_id, commission_preference, preferred_region)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                commission_preference = VALUES(commission_preference),
                preferred_region = VALUES(preferred_region)";

        try {
            $this->db->execute($sql, [$userId, $preference, $region]);

            // Also update user table
            $sql = "UPDATE user SET commission_preference = ? WHERE uid = ?";
            $this->db->execute($sql, [$preference, $userId]);

            return ['success' => true, 'message' => 'Commission preference updated successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Failed to update preference: ' . $e->getMessage()];
        }
    }
}

// Helper functions for integration
function getHybridCommissionManager($db = null) {
    global $hybridCommissionManager;
    if (!isset($hybridCommissionManager)) {
        $hybridCommissionManager = new HybridCommissionManager($db);
    }
    return $hybridCommissionManager;
}

function calculateHybridCommission($propertySale, $user) {
    $manager = getHybridCommissionManager();
    return $manager->calculateCommission($propertySale, $user);
}

function getUnifiedDashboard($userId) {
    $manager = getHybridCommissionManager();
    return $manager->getUnifiedDashboard($userId);
}
?>
