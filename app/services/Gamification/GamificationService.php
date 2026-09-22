<?php

namespace App\Services\Gamification;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * Gamification Service
 * Badges, achievements, leaderboards for users and users
 */
class GamificationService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    // Badge categories
    const CATEGORY_SALES = 'sales';
    const CATEGORY_LEADS = 'leads';
    const CATEGORY_VISITS = 'visits';
    const CATEGORY_TRAINING = 'training';
    const CATEGORY_ENGAGEMENT = 'engagement';
    const CATEGORY_MILESTONE = 'milestone';

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Award badge to user
     */
    public function awardBadge(int $userId, string $badgeCode): array
    {
        // Get badge details
        $badge = $this->getBadgeByCode($badgeCode);
        if (!$badge) {
            return ['success' => false, 'error' => 'Badge not found'];
        }

        // Check if already awarded
        $existing = $this->db->query(
            "SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?",
            [$userId, $badge['id']]
        )->fetchColumn();

        if ($existing) {
            return ['success' => false, 'error' => 'Badge already awarded'];
        }

        // Award badge
        $this->db->query(
            "INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (?, ?, NOW())",
            [$userId, $badge['id']]
        );

        // Update user points
        $this->addPoints($userId, $badge['points'], "Earned badge: {$badge['name']}");

        // Check for level up
        $levelUp = $this->checkLevelUp($userId);

        return [
            'success' => true,
            'badge' => $badge,
            'points_earned' => $badge['points'],
            'level_up' => $levelUp
        ];
    }

    /**
     * Add points to user
     */
    public function addPoints(int $userId, int $points, string $reason): array
    {
        try {
            $this->db->query(
                "INSERT INTO user_points (user_id, points, reason, created_at) VALUES (?, ?, ?, NOW())",
                [$userId, $points, $reason]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        // Update total points
        $tid = $this->getTenantId();
        $this->db->query(
            "UPDATE users SET total_points = total_points + ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$points, $userId, $tid] : [$points, $userId]
        );

        return [
            'success' => true,
            'points_added' => $points,
            'reason' => $reason
        ];
    }

    /**
     * Check and process level up
     */
    private function checkLevelUp(int $userId): ?array
    {
        $tid = $this->getTenantId();
        $user = $this->db->query(
            "SELECT total_points, current_level FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$userId, $tid] : [$userId]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$user) return null;

        $newLevel = $this->calculateLevel($user['total_points']);

        if ($newLevel > $user['current_level']) {
            $this->db->query(
                "UPDATE users SET current_level = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$newLevel, $userId, $tid] : [$newLevel, $userId]
            );

            return [
                'old_level' => $user['current_level'],
                'new_level' => $newLevel,
                'rewards' => $this->getLevelRewards($newLevel)
            ];
        }

        return null;
    }

    /**
     * Calculate level from points
     */
    private function calculateLevel(int $points): int
    {
        $levels = [
            1 => 0,
            2 => 100,
            3 => 300,
            4 => 600,
            5 => 1000,
            6 => 1500,
            7 => 2500,
            8 => 4000,
            9 => 6000,
            10 => 10000
        ];

        $level = 1;
        foreach ($levels as $lvl => $required) {
            if ($points >= $required) {
                $level = $lvl;
            }
        }

        return $level;
    }

    /**
     * Get level rewards
     */
    private function getLevelRewards(int $level): array
    {
        $rewards = [
            2 => ['commission_bonus' => 1],
            3 => ['commission_bonus' => 2, 'badge' => 'rising_star'],
            4 => ['commission_bonus' => 3],
            5 => ['commission_bonus' => 5, 'badge' => 'property_pro'],
            6 => ['commission_bonus' => 7],
            7 => ['commission_bonus' => 10, 'badge' => 'sales_champion'],
            8 => ['commission_bonus' => 12],
            9 => ['commission_bonus' => 15, 'badge' => 'elite_seller'],
            10 => ['commission_bonus' => 20, 'badge' => 'legend', 'special_perks' => true]
        ];

        return $rewards[$level] ?? [];
    }

    /**
     * Get badge by code
     */
    private function getBadgeByCode(string $code): ?array
    {
        return $this->db->query(
            "SELECT * FROM badges WHERE name = ? AND is_active = 1",
            [$code]
        )->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get user badges
     */
    public function getUserBadges(int $userId): array
    {
        return $this->db->query(
            "SELECT b.*, ub.awarded_at AS earned_at FROM badges b
             JOIN user_badges ub ON b.id = ub.badge_id
             WHERE ub.user_id = ?
             ORDER BY ub.awarded_at DESC",
            [$userId]
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(string $type = 'points', int $limit = 10, int $departmentId = null): array
    {
        $sql = "SELECT u.id, u.name, u.avatar, u.total_points, u.current_level,
                       COUNT(DISTINCT ub.id) as badge_count";

        switch ($type) {
            case 'sales':
                $sql .= ", (SELECT COUNT(*) FROM sales s WHERE s.associate_id = u.id AND MONTH(s.created_at) = MONTH(CURDATE())) as sales_count";
                break;
            case 'leads':
                $sql .= ", (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND MONTH(l.created_at) = MONTH(CURDATE())) as leads_count";
                break;
            case 'visits':
                $sql .= ", (SELECT COUNT(*) FROM property_visits pv WHERE pv.customer_id = u.id AND MONTH(pv.created_at) = MONTH(CURDATE())) as visits_count";
                break;
            default:
                $sql .= ", u.total_points as score";
        }

        $tid = $this->getTenantId();
        $sql .= " FROM users u LEFT JOIN user_badges ub ON u.id = ub.user_id WHERE u.status = 'active'";

        $params = [];
        if ($tid > 1) {
            $sql .= " AND u.tenant_id = ?";
            $params[] = $tid;
        }
        if ($departmentId) {
            $sql .= " AND u.department_id = ?";
            $params[] = $departmentId;
        }

        $sql .= " GROUP BY u.id ORDER BY ";

        switch ($type) {
            case 'sales':
                $sql .= "sales_count DESC";
                break;
            case 'leads':
                $sql .= "leads_count DESC";
                break;
            case 'visits':
                $sql .= "visits_count DESC";
                break;
            default:
                $sql .= "total_points DESC";
        }

        $sql .= " LIMIT ?";
        $params[] = $limit;

        return $this->db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check and award automatic badges
     */
    public function checkAutomaticBadges(int $userId): array
    {
        $awarded = [];

        // Get user stats
        $stats = $this->getUserStats($userId);

        // Check sales badges
        if ($stats['total_sales'] >= 1) {
            $result = $this->awardBadge($userId, 'first_sale');
            if ($result['success']) $awarded[] = $result;
        }
        if ($stats['total_sales'] >= 10) {
            $result = $this->awardBadge($userId, 'sales_10');
            if ($result['success']) $awarded[] = $result;
        }
        if ($stats['total_sales'] >= 50) {
            $result = $this->awardBadge($userId, 'sales_50');
            if ($result['success']) $awarded[] = $result;
        }

        // Check lead badges
        if ($stats['total_leads'] >= 100) {
            $result = $this->awardBadge($userId, 'lead_master');
            if ($result['success']) $awarded[] = $result;
        }

        // Check visit badges
        if ($stats['total_visits'] >= 50) {
            $result = $this->awardBadge($userId, 'site_visitor');
            if ($result['success']) $awarded[] = $result;
        }

        // Check streak badges
        if ($stats['login_streak'] >= 30) {
            $result = $this->awardBadge($userId, 'consistent_30');
            if ($result['success']) $awarded[] = $result;
        }

        return $awarded;
    }

    /**
     * Get user stats for badge checking
     */
    private function getUserStats(int $userId): array
    {
        $stats = [];

        // Total sales
        $stats['total_sales'] = $this->db->query(
            "SELECT COUNT(*) FROM sales WHERE user_id = ?",
            [$userId]
        )->fetchColumn();

        // Total leads
        $stats['total_leads'] = $this->db->query(
            "SELECT COUNT(*) FROM leads WHERE assigned_to = ?",
            [$userId]
        )->fetchColumn();

        // Total visits
        $stats['total_visits'] = $this->db->query(
            "SELECT COUNT(*) FROM property_visits WHERE user_id = ?",
            [$userId]
        )->fetchColumn();

        // Login streak
        $tid = $this->getTenantId();
        $stats['login_streak'] = $this->db->query(
            "SELECT login_streak FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            $tid > 1 ? [$userId, $tid] : [$userId]
        )->fetchColumn() ?: 0;

        return $stats;
    }

    /**
     * Get user rank
     */
    public function getUserRank(int $userId): int
    {
        $tid = $this->getTenantId();
        return $this->db->query(
            "SELECT rank FROM (
                SELECT id, @rank := @rank + 1 as rank
                FROM users, (SELECT @rank := 0) r
                WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . "
                ORDER BY total_points DESC
            ) ranked WHERE id = ?",
            $tid > 1 ? [$tid, $userId] : [$userId]
        )->fetchColumn() ?: 0;
    }

    /**
     * Initialize default badges
     */
    public function initializeBadges(): void
    {
        $badges = [
            ['name' => 'first_sale', 'display_name' => 'First Sale', 'description' => 'Completed first property sale', 'points_required' => 50, 'category' => 'achievement', 'icon' => 'trophy', 'rarity' => 'uncommon'],
            ['name' => 'sales_10', 'display_name' => 'Sales Star', 'description' => 'Completed 10 property sales', 'points_required' => 200, 'category' => 'achievement', 'icon' => 'star', 'rarity' => 'rare'],
            ['name' => 'sales_50', 'display_name' => 'Sales Champion', 'description' => 'Completed 50 property sales', 'points_required' => 500, 'category' => 'achievement', 'icon' => 'crown', 'rarity' => 'epic'],
            ['name' => 'sales_champion', 'display_name' => 'Sales Champion L7', 'description' => 'Reached Level 7 sales milestone', 'points_required' => 2500, 'category' => 'milestone', 'icon' => 'crown', 'rarity' => 'epic'],
            ['name' => 'lead_master', 'display_name' => 'Lead Master', 'description' => 'Generated 100 leads', 'points_required' => 150, 'category' => 'achievement', 'icon' => 'user-plus', 'rarity' => 'rare'],
            ['name' => 'site_visitor', 'display_name' => 'Site Visitor', 'description' => 'Completed site visits', 'points_required' => 50, 'category' => 'achievement', 'icon' => 'map-marker-alt', 'rarity' => 'rare'],
            ['name' => 'rising_star', 'display_name' => 'Rising Star', 'description' => 'Reached Level 3', 'points_required' => 300, 'category' => 'milestone', 'icon' => 'rocket', 'rarity' => 'uncommon'],
            ['name' => 'property_pro', 'display_name' => 'Property Pro', 'description' => 'Reached Level 5', 'points_required' => 1000, 'category' => 'milestone', 'icon' => 'building', 'rarity' => 'rare'],
            ['name' => 'elite_seller', 'display_name' => 'Elite Seller', 'description' => 'Reached Level 9', 'points_required' => 6000, 'category' => 'milestone', 'icon' => 'gem', 'rarity' => 'epic'],
            ['name' => 'legend', 'display_name' => 'Legend', 'description' => 'Reached Level 10', 'points_required' => 10000, 'category' => 'milestone', 'icon' => 'medal', 'rarity' => 'legendary'],
            ['name' => 'consistent_30', 'display_name' => 'Consistent Achiever', 'description' => '30 day login streak', 'points_required' => 75, 'category' => 'loyalty', 'icon' => 'calendar-check', 'rarity' => 'uncommon'],
        ];

        foreach ($badges as $b) {
            $this->db->query(
                "INSERT INTO badges (name, display_name, description, points_required, category, icon, rarity, is_active, tenant_id, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    display_name = VALUES(display_name),
                    description = VALUES(description),
                    points_required = VALUES(points_required),
                    category = VALUES(category),
                    icon = VALUES(icon),
                    rarity = VALUES(rarity),
                    is_active = 1",
                [
                    $b['name'],
                    $b['display_name'],
                    $b['description'],
                    $b['points_required'],
                    $b['category'],
                    $b['icon'],
                    $b['rarity'],
                ]
            );
        }
    }
}
