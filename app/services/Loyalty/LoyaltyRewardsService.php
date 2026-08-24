<?php

namespace App\Services\Loyalty;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

/**
 * Loyalty & Rewards Service
 * Customer points system with tier-based benefits
 */
class LoyaltyRewardsService
{
    use ServiceTenantTrait;

    private $database;
    private $tiers;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->tiers = [
            'bronze' => ['min_points' => 0, 'multiplier' => 1, 'discount' => 0],
            'silver' => ['min_points' => 1000, 'multiplier' => 1.5, 'discount' => 5],
            'gold' => ['min_points' => 5000, 'multiplier' => 2, 'discount' => 10],
            'platinum' => ['min_points' => 10000, 'multiplier' => 3, 'discount' => 15],
            'diamond' => ['min_points' => 25000, 'multiplier' => 5, 'discount' => 20]
        ];
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure loyalty tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Loyalty points
        $pdo->exec("CREATE TABLE IF NOT EXISTS loyalty_points (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('customer', 'associate') NOT NULL DEFAULT 'customer',
            points INT NOT NULL DEFAULT 0,
            lifetime_points INT NOT NULL DEFAULT 0,
            redeemed_points INT NOT NULL DEFAULT 0,
            current_tier VARCHAR(20) DEFAULT 'bronze',
            tier_achieved_at TIMESTAMP NULL,
            referral_count INT DEFAULT 0,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user (user_id, user_type, tenant_id),
            INDEX idx_tier (current_tier),
            INDEX idx_points (points),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Points transactions
        $pdo->exec("CREATE TABLE IF NOT EXISTS loyalty_transactions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('customer', 'associate') NOT NULL,
            transaction_type ENUM('earned', 'redeemed', 'bonus', 'expired', 'adjusted') NOT NULL,
            points INT NOT NULL,
            description VARCHAR(255) NOT NULL,
            reference_type VARCHAR(50) NULL,
            reference_id INT NULL,
            balance_after INT NOT NULL,
            expiry_date DATE NULL,
            is_expired TINYINT(1) DEFAULT 0,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id, user_type, tenant_id),
            INDEX idx_type (transaction_type),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Rewards catalog
        $pdo->exec("CREATE TABLE IF NOT EXISTS rewards_catalog (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT NULL,
            points_required INT NOT NULL,
            reward_type ENUM('discount', 'service', 'product', 'cashback') NOT NULL,
            reward_value DECIMAL(10,2) NULL,
            image_url VARCHAR(255) NULL,
            stock_quantity INT DEFAULT -1,
            is_limited TINYINT(1) DEFAULT 0,
            start_date DATE NULL,
            end_date DATE NULL,
            terms_conditions TEXT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_type (reward_type),
            INDEX idx_active (is_active),
            INDEX idx_points (points_required)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Reward redemptions
        $pdo->exec("CREATE TABLE IF NOT EXISTS reward_redemptions (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_type ENUM('customer', 'associate') NOT NULL,
            reward_id INT NOT NULL,
            points_used INT NOT NULL,
            redemption_code VARCHAR(50) NOT NULL UNIQUE,
            status ENUM('pending', 'approved', 'delivered', 'cancelled', 'expired') DEFAULT 'pending',
            delivery_method ENUM('digital', 'physical', 'auto_credit') DEFAULT 'digital',
            delivery_details JSON NULL,
            used_at TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id, user_type, tenant_id),
            INDEX idx_status (status),
            INDEX idx_code (redemption_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Tier benefits
        $pdo->exec("CREATE TABLE IF NOT EXISTS tier_benefits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tier_name VARCHAR(20) NOT NULL,
            benefit_type VARCHAR(50) NOT NULL,
            benefit_name VARCHAR(100) NOT NULL,
            benefit_description TEXT NULL,
            benefit_value VARCHAR(50) NULL,
            is_active TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_benefit (tier_name, benefit_type),
            INDEX idx_tier (tier_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Points earning rules
        $pdo->exec("CREATE TABLE IF NOT EXISTS points_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rule_name VARCHAR(100) NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            points_earned INT NOT NULL,
            multiplier DECIMAL(3,2) DEFAULT 1.00,
            min_transaction_amount DECIMAL(15,2) NULL,
            max_points_per_day INT NULL,
            is_active TINYINT(1) DEFAULT 1,
            start_date DATE NULL,
            end_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_action (action_type),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Seed default data
        $this->seedDefaultData();
    }
    
    /**
     * Seed default data
     */
    private function seedDefaultData(): void
    {
        // Tier benefits
        $benefits = [
            ['bronze', 'discount', 'Base Discount', 'Standard pricing', '0%'],
            ['bronze', 'booking', 'Free Consultation', 'Free property consultation', '1 session'],
            ['silver', 'discount', 'Silver Discount', '5% off on all bookings', '5%'],
            ['silver', 'emi', 'EMI Waiver', 'Zero EMI processing fee', '1 waiver/year'],
            ['gold', 'discount', 'Gold Discount', '10% off on all bookings', '10%'],
            ['gold', 'priority', 'Priority Support', '24/7 priority customer support', 'unlimited'],
            ['platinum', 'discount', 'Platinum Discount', '15% off on all bookings', '15%'],
            ['platinum', 'visits', 'Free Site Visits', 'Complimentary site visits', 'unlimited'],
            ['diamond', 'discount', 'Diamond Discount', '20% off on all bookings', '20%'],
            ['diamond', 'exclusive', 'Exclusive Previews', 'Early access to new projects', 'priority']
        ];
        
        try {
            $stmt = $this->database->prepare("INSERT IGNORE INTO tier_benefits 
                (tier_name, benefit_type, benefit_name, benefit_description, benefit_value)
                VALUES (?, ?, ?, ?, ?)");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        foreach ($benefits as $benefit) {
            $stmt->execute($benefit);
        }
        
        // Points earning rules
        $rules = [
            ['Booking Property', 'booking', 100, 1.00, 50000, 500],
            ['Site Visit Completion', 'site_visit', 50, 1.00, null, 200],
            ['Referral Signup', 'referral_signup', 200, 1.00, null, null],
            ['Referral Purchase', 'referral_purchase', 1000, 2.00, null, null],
            ['App Download', 'app_download', 100, 1.00, null, 100],
            ['Profile Completion', 'profile_complete', 50, 1.00, null, 50],
            ['Property Review', 'review', 25, 1.00, null, 100],
            ['Social Share', 'social_share', 10, 1.00, null, 50],
            ['Newsletter Subscription', 'newsletter', 20, 1.00, null, 20],
            ['Birthday Bonus', 'birthday', 500, 1.00, null, 500]
        ];
        
        $ruleStmt = $this->database->prepare("INSERT IGNORE INTO points_rules 
            (rule_name, action_type, points_earned, multiplier, max_points_per_day)
            VALUES (?, ?, ?, ?, ?)");
        
        foreach ($rules as $rule) {
            $ruleStmt->execute([$rule[0], $rule[1], $rule[2], $rule[3], $rule[4]]);
        }
        
        // Rewards catalog
        $rewards = [
            ['₹500 Cashback', 'Get ₹500 credited to your wallet', 500, 'cashback', 500],
            ['₹1000 Cashback', 'Get ₹1000 credited to your wallet', 1000, 'cashback', 1000],
            ['₹2000 Cashback', 'Get ₹2000 credited to your wallet', 2000, 'cashback', 2000],
            ['Free Site Visit', 'Complimentary site visit with transport', 200, 'service', 0],
            ['Legal Consultation', 'Free legal consultation for property', 500, 'service', 0],
            ['Home Decor Voucher', '₹5000 home decor voucher', 2500, 'product', 5000],
            ['5% Booking Discount', 'Extra 5% off on next booking', 1000, 'discount', 5],
            ['10% Booking Discount', 'Extra 10% off on next booking', 2000, 'discount', 10],
            ['Priority Processing', 'Fast-track your booking process', 300, 'service', 0]
        ];
        
        $rewardStmt = $this->database->prepare("INSERT IGNORE INTO rewards_catalog 
            (reward_name, reward_description, points_cost, reward_type, reward_value)
            VALUES (?, ?, ?, ?, ?)");
        
        foreach ($rewards as $reward) {
            $rewardStmt->execute($reward);
        }
    }
    
    /**
     * Get or create loyalty account
     */
    public function getOrCreateAccount(int $userId, string $userType = 'customer'): array
    {
        $tSql = $this->tenantSql();
        $params = [$userId, $userType];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $sql = "SELECT * FROM loyalty_points WHERE user_id = ? AND user_type = ?" . $tSql;
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $account = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$account) {
            // Create new account
            $insertData = $this->tenantInsertData();
            $cols = "user_id, user_type, current_tier";
            $placeholders = "?, ?, 'bronze'";
            $insertParams = [$userId, $userType];
            if (!empty($insertData)) {
                $cols .= ", " . implode(', ', array_keys($insertData));
                $placeholders .= ", ?";
                $insertParams = array_merge($insertParams, array_values($insertData));
            }
            $insertSql = "INSERT INTO loyalty_points ($cols) VALUES ($placeholders)";
            $insertStmt = $this->database->prepare($insertSql);
            $insertStmt->execute($insertParams);
            
            return [
                'user_id' => $userId,
                'user_type' => $userType,
                'points' => 0,
                'lifetime_points' => 0,
                'current_tier' => 'bronze',
                'is_new' => true
            ];
        }
        
        $account['is_new'] = false;
        return $account;
    }
    
    /**
     * Earn points
     */
    public function earnPoints(int $userId, string $actionType, array $context = []): array
    {
        $userType = $context['user_type'] ?? 'customer';
        
        // Get points rule
        $rule = $this->getPointsRule($actionType);
        if (!$rule) {
            return ['success' => false, 'error' => 'No rule found for action: ' . $actionType];
        }
        
        // Calculate points
        $basePoints = $rule['points_earned'];
        $multiplier = $rule['multiplier'];
        
        // Get user's tier for additional multiplier
        $account = $this->getOrCreateAccount($userId, $userType);
        $tierMultiplier = $this->tiers[$account['current_tier']]['multiplier'] ?? 1;
        
        $totalPoints = round($basePoints * $multiplier * $tierMultiplier);
        
        // Check max points per day
        if ($rule['max_points_per_day']) {
            $todayPoints = $this->getPointsEarnedToday($userId, $userType, $actionType);
            if ($todayPoints >= $rule['max_points_per_day']) {
                return ['success' => false, 'error' => 'Daily points limit reached'];
            }
            $totalPoints = min($totalPoints, $rule['max_points_per_day'] - $todayPoints);
        }
        
        if ($totalPoints <= 0) {
            return ['success' => false, 'error' => 'No points to award'];
        }
        
        try {
            // Add transaction
            $this->addTransaction($userId, $userType, 'earned', $totalPoints, 
                "Points earned for: {$rule['rule_name']}", 
                $context['reference_type'] ?? null,
                $context['reference_id'] ?? null
            );
            
            // Update account
            $newBalance = $this->updatePointsBalance($userId, $userType, $totalPoints);
            
            // Check tier upgrade
            $tierUpgrade = $this->checkTierUpgrade($userId, $userType);
            
            return [
                'success' => true,
                'points_earned' => $totalPoints,
                'new_balance' => $newBalance,
                'tier_upgrade' => $tierUpgrade
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Redeem points
     */
    public function redeemPoints(int $userId, int $rewardId, string $userType = 'customer'): array
    {
        // Get reward
        $reward = $this->getReward($rewardId);
        if (!$reward || !$reward['is_active']) {
            return ['success' => false, 'error' => 'Reward not found or inactive'];
        }
        
        // Check stock
        if ($reward['is_limited'] && $reward['stock_quantity'] <= 0) {
            return ['success' => false, 'error' => 'Reward out of stock'];
        }
        
        // Get account
        $account = $this->getOrCreateAccount($userId, $userType);
        if ($account['points'] < $reward['points_required']) {
            return ['success' => false, 'error' => 'Insufficient points'];
        }
        
        try {
            // Generate redemption code
            $redemptionCode = strtoupper(uniqid('APS'));
            
            // Deduct points
            $this->addTransaction($userId, $userType, 'redeemed', -$reward['points_required'],
                "Redeemed: {$reward['name']}", 'reward', $rewardId
            );
            
            $newBalance = $this->updatePointsBalance($userId, $userType, -$reward['points_required']);
            
            // Create redemption record
            $insertData = $this->tenantInsertData();
            $cols = "user_id, user_type, reward_id, points_used, redemption_code, expires_at";
            $placeholders = "?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY)";
            $insertParams = [
                $userId, $userType, $rewardId,
                $reward['points_required'], $redemptionCode
            ];
            if (!empty($insertData)) {
                $cols .= ", " . implode(', ', array_keys($insertData));
                $placeholders .= ", ?";
                $insertParams = array_merge($insertParams, array_values($insertData));
            }
            $insertSql = "INSERT INTO reward_redemptions ($cols) VALUES ($placeholders)";
            $insertStmt = $this->database->prepare($insertSql);
            $insertStmt->execute($insertParams);
            
            // Update stock
            if ($reward['is_limited']) {
                $this->updateRewardStock($rewardId, -1);
            }
            
            return [
                'success' => true,
                'redemption_code' => $redemptionCode,
                'points_used' => $reward['points_required'],
                'new_balance' => $newBalance,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get loyalty dashboard
     */
    public function getDashboard(int $userId, string $userType = 'customer'): array
    {
        $account = $this->getOrCreateAccount($userId, $userType);
        
        // Get tier benefits
        $tierBenefits = $this->getTierBenefits($account['current_tier']);
        
        // Get next tier info
        $nextTier = $this->getNextTier($account['current_tier']);
        $pointsToNext = $nextTier ? ($nextTier['min_points'] - $account['lifetime_points']) : 0;
        
        // Get recent transactions
        $transactions = $this->getRecentTransactions($userId, $userType, 10);
        
        // Get available rewards
        $rewards = $this->getAvailableRewards($account['points']);
        
        // Get referral stats
        $referralStats = $this->getReferralStats($userId, $userType);
        
        return [
            'account' => $account,
            'tier_benefits' => $tierBenefits,
            'next_tier' => $nextTier,
            'points_to_next_tier' => max(0, $pointsToNext),
            'progress_percent' => $nextTier ? 
                round(($account['lifetime_points'] / $nextTier['min_points']) * 100, 2) : 100,
            'recent_transactions' => $transactions,
            'available_rewards' => $rewards,
            'referral_stats' => $referralStats
        ];
    }
    
    /**
     * Get tier benefits
     */
    private function getTierBenefits(string $tier): array
    {
        try {
            $sql = "SELECT * FROM tier_benefits WHERE tier_name = ? AND is_active = 1 ORDER BY sort_order";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$tier]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get next tier
     */
    private function getNextTier(string $currentTier): ?array
    {
        $tiers = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
        $currentIndex = array_search($currentTier, $tiers);
        
        if ($currentIndex === false || $currentIndex >= count($tiers) - 1) {
            return null;
        }
        
        $nextTierName = $tiers[$currentIndex + 1];
        return ['name' => $nextTierName, 'min_points' => $this->tiers[$nextTierName]['min_points']];
    }
    
    /**
     * Check tier upgrade
     */
    private function checkTierUpgrade(int $userId, string $userType): ?array
    {
        $account = $this->getOrCreateAccount($userId, $userType);
        $currentTier = $account['current_tier'];
        $lifetimePoints = $account['lifetime_points'];
        
        $newTier = null;
        foreach ($this->tiers as $tierName => $tierData) {
            if ($lifetimePoints >= $tierData['min_points'] && $tierName !== $currentTier) {
                $newTier = $tierName;
            }
        }
        
        if ($newTier && $newTier !== $currentTier) {
            // Update tier
            $tSql = $this->tenantSql();
            $sql = "UPDATE loyalty_points SET 
                current_tier = ?
                WHERE user_id = ? AND user_type = ?" . $tSql;

            $stmt = $this->database->prepare($sql);
            $params = [$newTier, $userId, $userType];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            
            return [
                'upgraded' => true,
                'old_tier' => $currentTier,
                'new_tier' => $newTier,
                'message' => "Congratulations! You've been upgraded to {$newTier} tier!"
            ];
        }
        
        return null;
    }
    
    /**
     * Get points rule
     */
    private function getPointsRule(string $actionType): ?array
    {
        $sql = "SELECT * FROM points_rules WHERE action_type = ? AND is_active = 1 
            AND (start_date IS NULL OR start_date <= CURDATE())
            AND (end_date IS NULL OR end_date >= CURDATE())
            LIMIT 1";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$actionType]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get points earned today
     */
    private function getPointsEarnedToday(int $userId, string $userType, string $actionType): int
    {
        $tSql = $this->tenantSql();
        $sql = "SELECT SUM(points) FROM loyalty_transactions 
            WHERE user_id = ? AND user_type = ? AND transaction_type = 'earned'
            AND DATE(created_at) = CURDATE() AND description LIKE ?" . $tSql;

        $stmt = $this->database->prepare($sql);
        $params = [$userId, $userType, "%$actionType%"];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Add transaction
     */
    private function addTransaction(int $userId, string $userType, string $type, 
        int $points, string $description, ?string $refType, ?int $refId): void
    {
        // Get current balance
        $account = $this->getOrCreateAccount($userId, $userType);
        $balanceAfter = $account['points'] + $points;

        $insertData = $this->tenantInsertData();
        $cols = "user_id, user_type, transaction_type, points, description, 
             reference_type, reference_id, balance_after";
        $placeholders = "?, ?, ?, ?, ?, ?, ?, ?";
        $params = [
            $userId, $userType, $type, $points, $description,
            $refType, $refId, $balanceAfter
        ];
        if (!empty($insertData)) {
            $cols .= ", " . implode(', ', array_keys($insertData));
            $placeholders .= ", ?";
            $params = array_merge($params, array_values($insertData));
        }

        $sql = "INSERT INTO loyalty_transactions ($cols) VALUES ($placeholders)";
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
    }
    
    /**
     * Update points balance
     */
    private function updatePointsBalance(int $userId, string $userType, int $pointsDelta): int
    {
        $tSql = $this->tenantSql();
        $sql = "UPDATE loyalty_points SET 
            points = points + ?,
            lifetime_points = CASE WHEN ? > 0 THEN lifetime_points + ? ELSE lifetime_points END
            WHERE user_id = ? AND user_type = ?" . $tSql;

        $params = [
            $pointsDelta, $pointsDelta, $pointsDelta,
            $userId, $userType
        ];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        
        // Get new balance
        $account = $this->getOrCreateAccount($userId, $userType);
        return $account['points'];
    }
    
    /**
     * Get reward
     */
    private function getReward(int $rewardId): ?array
    {
        $sql = "SELECT * FROM rewards_catalog WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$rewardId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Update reward stock
     */
    private function updateRewardStock(int $rewardId, int $delta): void
    {
        $sql = "UPDATE rewards_catalog SET stock_quantity = stock_quantity + ? WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$delta, $rewardId]);
    }
    
    /**
     * Get recent transactions
     */
    private function getRecentTransactions(int $userId, string $userType, int $limit): array
    {
        $tSql = $this->tenantSql();
        $sql = "SELECT * FROM loyalty_transactions 
            WHERE user_id = ? AND user_type = ?
            " . $tSql . "
            ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->database->prepare($sql);
        $params = [$userId, $userType];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $params[] = $limit;
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get available rewards
     */
    private function getAvailableRewards(int $points): array
    {
        $sql = "SELECT * FROM rewards_catalog 
            WHERE is_active = 1 AND points_required <= ?
            AND (start_date IS NULL OR start_date <= CURDATE())
            AND (end_date IS NULL OR end_date >= CURDATE())
            AND (is_limited = 0 OR stock_quantity > 0)
            ORDER BY points_required ASC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$points]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get referral stats
     */
    private function getReferralStats(int $userId, string $userType): array
    {
        $tSql = $this->tenantSql();
        $sql = "SELECT referral_count FROM loyalty_points WHERE user_id = ? AND user_type = ?" . $tSql;
        $stmt = $this->database->prepare($sql);
        $params = [$userId, $userType];
        if ($this->tenantId() > 1) $params[] = $this->tenantId();
        $stmt->execute($params);
        $count = $stmt->fetchColumn();
        
        return [
            'total_referrals' => $count ?: 0,
            'points_from_referrals' => ($count ?: 0) * 200 // Assuming 200 points per referral
        ];
    }
    
    /**
     * Get all tiers info
     */
    public function getAllTiers(): array
    {
        $result = [];
        foreach ($this->tiers as $name => $data) {
            $data['benefits'] = $this->getTierBenefits($name);
            $result[$name] = $data;
        }
        return $result;
    }
    
    /**
     * Admin: Get loyalty stats
     */
    public function getAdminStats(): array
    {
        $tSql = $this->tenantSql();
        $tid = $this->tenantId();

        // Total members by tier
        $tierSql = "SELECT current_tier, COUNT(*) as count FROM loyalty_points WHERE 1=1" . $tSql . " GROUP BY current_tier";
        $tierStmt = $this->database->prepare($tierSql);
        $tierParams = $tid > 1 ? [$tid] : [];
        $tierStmt->execute($tierParams);
        $byTier = $tierStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Total points in circulation
        $pointsSql = "SELECT SUM(points) as total_active, SUM(lifetime_points) as total_lifetime FROM loyalty_points WHERE 1=1" . $tSql;
        $pointsStmt = $this->database->prepare($pointsSql);
        $pointsStmt->execute($tid > 1 ? [$tid] : []);
        $pointsData = $pointsStmt->fetch(\PDO::FETCH_ASSOC);

        // Recent redemptions
        $redemptionData = [0, 0];
        try {
            $redemptionSql = "SELECT COUNT(*), COALESCE(SUM(points_used), 0) FROM reward_redemptions WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)" . $tSql;
            $redemptionStmt = $this->database->prepare($redemptionSql);
            $redemptionStmt->execute($tid > 1 ? [$tid] : []);
            $redemptionData = $redemptionStmt->fetch(\PDO::FETCH_NUM);
        } catch (\Exception $e) {
            // Table or column may not exist yet
                    error_log("LoyaltyRewardsService.php: " . $e->getMessage());
        }
        
        return [
            'members_by_tier' => $byTier,
            'total_active_points' => $pointsData['total_active'] ?? 0,
            'total_lifetime_points' => $pointsData['total_lifetime'] ?? 0,
            'redemptions_30d' => $redemptionData[0] ?? 0,
            'points_redeemed_30d' => $redemptionData[1] ?? 0
        ];
    }
}
