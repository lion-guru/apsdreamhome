<?php
/**
 * Gamification Service
 * 
 * Points, badges, levels, and leaderboard system
 * Rewards users for various activities
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class GamificationService
{
    use ServiceTenantTrait;
    private $db;

    // Points configuration
    const POINTS = [
        'lead_created' => 10,
        'lead_converted' => 50,
        'booking_completed' => 100,
        'referral_signup' => 25,
        'referral_booking' => 75,
        'site_visit_completed' => 30,
        'document_uploaded' => 15,
        'profile_completed' => 20,
        'daily_login' => 5,
        'review_posted' => 10,
    ];

    // Level thresholds
    const LEVELS = [
        1 => ['name' => 'Newcomer', 'min_points' => 0, 'icon' => 'fa-seedling', 'color' => '#9e9e9e'],
        2 => ['name' => 'Explorer', 'min_points' => 100, 'icon' => 'fa-compass', 'color' => '#4caf50'],
        3 => ['name' => 'Achiever', 'min_points' => 500, 'icon' => 'fa-trophy', 'color' => '#ff9800'],
        4 => ['name' => 'Expert', 'min_points' => 1000, 'icon' => 'fa-star', 'color' => '#2196f3'],
        5 => ['name' => 'Master', 'min_points' => 2500, 'icon' => 'fa-crown', 'color' => '#9c27b0'],
        6 => ['name' => 'Legend', 'min_points' => 5000, 'icon' => 'fa-gem', 'color' => '#f44336'],
        7 => ['name' => 'Champion', 'min_points' => 10000, 'icon' => 'fa-medal', 'color' => '#ff5722'],
    ];

    // Badge definitions
    const BADGES = [
        'first_lead' => ['name' => 'First Lead', 'description' => 'Created your first lead', 'icon' => 'fa-bullseye', 'points' => 10],
        'lead_master' => ['name' => 'Lead Master', 'description' => 'Created 50 leads', 'icon' => 'fa-crosshairs', 'points' => 100],
        'conversion_king' => ['name' => 'Conversion King', 'description' => 'Converted 10 leads', 'icon' => 'fa-handshake', 'points' => 150],
        'referral_champion' => ['name' => 'Referral Champion', 'description' => 'Referred 5 users', 'icon' => 'fa-users', 'points' => 200],
        'booking_pro' => ['name' => 'Booking Pro', 'description' => 'Completed 5 bookings', 'icon' => 'fa-file-contract', 'points' => 250],
        'social_butterfly' => ['name' => 'Social Butterfly', 'description' => 'Shared 10 properties', 'icon' => 'fa-share-alt', 'points' => 50],
        'early_bird' => ['name' => 'Early Bird', 'description' => 'Logged in 7 days in a row', 'icon' => 'fa-sun', 'points' => 75],
        'night_owl' => ['name' => 'Night Owl', 'description' => 'Active after 10 PM', 'icon' => 'fa-moon', 'points' => 25],
        'weekend_warrior' => ['name' => 'Weekend Warrior', 'description' => 'Active on weekends', 'icon' => 'fa-calendar-week', 'points' => 50],
        'century_club' => ['name' => 'Century Club', 'description' => 'Earned 100 points', 'icon' => 'fa-100', 'points' => 100],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Award points to user for an action
     */
    public function awardPoints(int $userId, string $action, string $description = ''): array
    {
        $points = self::POINTS[$action] ?? 0;
        if ($points <= 0) {
            return ['success' => false, 'message' => 'Invalid action'];
        }

        $tid = $this->tenantId();

        // Insert points transaction
        $this->db->insert('gamification_points', array_merge([
            'user_id' => $userId,
            'action' => $action,
            'points' => $points,
            'description' => $description,
        ], $tid > 1 ? ['tenant_id' => $tid] : []));

        // Update user total points
        $this->db->query(
            "INSERT INTO gamification_user_stats (user_id, total_points, updated_at) 
             VALUES (?, ?, NOW()) 
             ON DUPLICATE KEY UPDATE total_points = total_points + VALUES(total_points), updated_at = NOW()",
            array_merge([$userId], [$points])
        );

        // Check for level up
        $newLevel = $this->checkLevelUp($userId);

        // Check for new badges
        $newBadges = $this->checkBadges($userId);

        return [
            'success' => true,
            'points_awarded' => $points,
            'new_level' => $newLevel,
            'new_badges' => $newBadges,
        ];
    }

    /**
     * Get user's gamification stats
     */
    public function getUserStats(int $userId): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid, $userId] : [$userId];

        $stats = $this->db->fetchOne(
            "SELECT * FROM gamification_user_stats WHERE user_id = ? {$tSql}",
            $params
        ) ?? ['user_id' => $userId, 'total_points' => 0, 'current_level' => 1];

        $level = $this->getLevel($stats['current_level'] ?? 1);
        $nextLevel = $this->getLevel(($stats['current_level'] ?? 1) + 1);

        $stats['level_name'] = $level['name'];
        $stats['level_icon'] = $level['icon'];
        $stats['level_color'] = $level['color'];
        $stats['next_level'] = $nextLevel;
        $stats['points_to_next'] = $nextLevel ? max(0, $nextLevel['min_points'] - ($stats['total_points'] ?? 0)) : 0;
        $stats['progress_percent'] = $nextLevel ? min(100, round((($stats['total_points'] ?? 0) - $level['min_points']) / ($nextLevel['min_points'] - $level['min_points']) * 100)) : 100;

        return $stats;
    }

    /**
     * Get user's badges
     */
    public function getUserBadges(int $userId): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid, $userId] : [$userId];

        return $this->db->fetchAll(
            "SELECT * FROM gamification_user_badges WHERE user_id = ? {$tSql} ORDER BY earned_at DESC",
            $params
        ) ?? [];
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(int $limit = 20): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " WHERE gus.tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        return $this->db->fetchAll(
            "SELECT gus.*, u.name, u.email, u.role,
                    RANK() OVER (ORDER BY gus.total_points DESC) as rank
             FROM gamification_user_stats gus
             LEFT JOIN users u ON gus.user_id = u.id
             {$tSql}
             ORDER BY gus.total_points DESC
             LIMIT ?",
            array_merge($params, [$limit])
        ) ?? [];
    }

    /**
     * Get level info
     */
    public function getLevel(int $level): array
    {
        return self::LEVELS[$level] ?? end(self::LEVELS);
    }

    /**
     * Check and update user level
     */
    private function checkLevelUp(int $userId): ?array
    {
        $stats = $this->getUserStats($userId);
        $totalPoints = $stats['total_points'] ?? 0;
        $currentLevel = $stats['current_level'] ?? 1;

        $newLevel = $currentLevel;
        foreach (self::LEVELS as $level => $data) {
            if ($totalPoints >= $data['min_points']) {
                $newLevel = $level;
            }
        }

        if ($newLevel > $currentLevel) {
            $this->db->query(
                "UPDATE gamification_user_stats SET current_level = ?, updated_at = NOW() WHERE user_id = ?",
                [$newLevel, $userId]
            );
            return self::LEVELS[$newLevel];
        }

        return null;
    }

    /**
     * Check and award badges
     */
    private function checkBadges(int $userId): array
    {
        $newBadges = [];
        $stats = $this->getUserStats($userId);

        // Century Club badge
        if (($stats['total_points'] ?? 0) >= 100) {
            $badge = $this->awardBadge($userId, 'century_club');
            if ($badge) $newBadges[] = $badge;
        }

        return $newBadges;
    }

    /**
     * Award a badge to user
     */
    private function awardBadge(int $userId, string $badgeKey): ?array
    {
        if (!isset(self::BADGES[$badgeKey])) {
            return null;
        }

        $badge = self::BADGES[$badgeKey];
        $tid = $this->tenantId();

        // Check if already has badge
        $existing = $this->db->fetchOne(
            "SELECT id FROM gamification_user_badges WHERE user_id = ? AND badge_key = ?",
            [$userId, $badgeKey]
        );

        if ($existing) {
            return null;
        }

        // Award badge
        $this->db->insert('gamification_user_badges', array_merge([
            'user_id' => $userId,
            'badge_key' => $badgeKey,
            'badge_name' => $badge['name'],
            'badge_description' => $badge['description'],
            'badge_icon' => $badge['icon'],
            'points' => $badge['points'],
        ], $tid > 1 ? ['tenant_id' => $tid] : []));

        // Award badge points
        $this->db->query(
            "UPDATE gamification_user_stats SET total_points = total_points + ?, updated_at = NOW() WHERE user_id = ?",
            [$badge['points'], $userId]
        );

        return $badge;
    }

    /**
     * Get top associate by gamification points
     */
    public function getTopAssociate(): ?array
    {
        return $this->db->fetch(
            "SELECT gus.user_id, gus.total_points, gus.current_level, u.name, u.email
             FROM gamification_user_stats gus
             JOIN users u ON gus.user_id = u.id
             WHERE u.role = 'associate'" . ($this->tenantId() > 1 ? " AND u.tenant_id = ?" : "") . "
             ORDER BY gus.total_points DESC LIMIT 1",
            $this->tenantId() > 1 ? [$this->tenantId()] : []
        ) ?: null;
    }

    /**
     * Get top agent by gamification points
     */
    public function getTopAgent(): ?array
    {
        return $this->db->fetch(
            "SELECT gus.user_id, gus.total_points, gus.current_level, u.name, u.email
             FROM gamification_user_stats gus
             JOIN users u ON gus.user_id = u.id
             WHERE u.role = 'agent'" . ($this->tenantId() > 1 ? " AND u.tenant_id = ?" : "") . "
             ORDER BY gus.total_points DESC LIMIT 1",
            $this->tenantId() > 1 ? [$this->tenantId()] : []
        ) ?: null;
    }

    /**
     * Get top employee by gamification points
     */
    public function getTopEmployee(): ?array
    {
        return $this->db->fetch(
            "SELECT gus.user_id, gus.total_points, gus.current_level, u.name, u.email
             FROM gamification_user_stats gus
             JOIN users u ON gus.user_id = u.id
             WHERE u.role = 'employee'" . ($this->tenantId() > 1 ? " AND u.tenant_id = ?" : "") . "
             ORDER BY gus.total_points DESC LIMIT 1",
            $this->tenantId() > 1 ? [$this->tenantId()] : []
        ) ?: null;
    }
}
