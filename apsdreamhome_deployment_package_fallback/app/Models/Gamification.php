<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Associate MLM Gamification System Model
 * Handles points, badges, challenges, leaderboards, achievements, and rewards
 */
class Gamification extends Model
{
    protected $table = 'gamification_points';

    const ACTIVITY_FIRST_LOGIN = 'first_login';
    const ACTIVITY_PROPERTY_VIEW = 'property_view';
    const ACTIVITY_LEAD_GENERATED = 'lead_generated';
    const ACTIVITY_SALE_COMPLETED = 'sale_completed';
    const ACTIVITY_RECRUITMENT = 'recruitment';
    const ACTIVITY_TRAINING_COMPLETED = 'training_completed';
    const ACTIVITY_CHALLENGE_COMPLETED = 'challenge_completed';

    /**
     * Award points to user
     */
    public function awardPoints(int $userId, string $userType, int $points, string $activityType, array $metadata = []): array
    {
        $pointsRecord = $this->getOrCreatePointsRecord($userId, $userType);

        $balanceBefore = $pointsRecord['points_total'];
        $newBalance = $balanceBefore + $points;

        // Update points record
        $this->update($pointsRecord['id'], [
            'points_total' => $newBalance,
            'points_available' => $pointsRecord['points_available'] + $points,
            'last_activity_date' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update level if needed
        $newLevel = $this->calculateLevel($newBalance);
        if ($newLevel > $pointsRecord['current_level']) {
            $this->update($pointsRecord['id'], ['current_level' => $newLevel]);
        }

        // Log transaction
        $this->logPointsTransaction($userId, $userType, 'earned', $points, $balanceBefore, $newBalance, $activityType, $metadata);

        // Check for badges and achievements
        $this->checkForBadgesAndAchievements($userId, $userType, $activityType, $metadata);

        // Update streaks
        $this->updateActivityStreak($userId, $userType);

        return [
            'success' => true,
            'points_awarded' => $points,
            'new_balance' => $newBalance,
            'level_up' => $newLevel > $pointsRecord['current_level']
        ];
    }

    /**
     * Redeem points for reward
     */
    public function redeemPoints(int $userId, string $userType, int $rewardId): array
    {
        $reward = $this->query("SELECT * FROM rewards_catalog WHERE id = ? AND is_active = 1", [$rewardId])->fetch();
        if (!$reward) {
            return ['success' => false, 'message' => 'Reward not found'];
        }

        $pointsRecord = $this->getOrCreatePointsRecord($userId, $userType);
        if ($pointsRecord['points_available'] < $reward['points_cost']) {
            return ['success' => false, 'message' => 'Insufficient points'];
        }

        // Create redemption record
        $redemptionId = $this->insertInto('reward_redemptions', [
            'user_id' => $userId,
            'user_type' => $userType,
            'reward_id' => $rewardId,
            'points_spent' => $reward['points_cost'],
            'status' => 'pending'
        ]);

        // Deduct points
        $balanceBefore = $pointsRecord['points_total'];
        $newBalance = $balanceBefore - $reward['points_cost'];

        $this->update($pointsRecord['id'], [
            'points_available' => $pointsRecord['points_available'] - $reward['points_cost'],
            'points_redeemed' => $pointsRecord['points_redeemed'] + $reward['points_cost'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log transaction
        $this->logPointsTransaction($userId, $userType, 'redeemed', -$reward['points_cost'], $balanceBefore, $newBalance, 'redemption', [
            'reward_id' => $rewardId,
            'redemption_id' => $redemptionId
        ]);

        return [
            'success' => true,
            'redemption_id' => $redemptionId,
            'points_spent' => $reward['points_cost'],
            'remaining_points' => $newBalance
        ];
    }

    /**
     * Award badge to user
     */
    public function awardBadge(int $userId, string $userType, int $badgeId): array
    {
        // Check if user already has this badge
        $existing = $this->query(
            "SELECT id FROM user_badges WHERE user_id = ? AND user_type = ? AND badge_id = ?",
            [$userId, $userType, $badgeId]
        )->fetch();

        if ($existing) {
            return ['success' => false, 'message' => 'Badge already awarded'];
        }

        $badge = $this->query("SELECT * FROM gamification_badges WHERE id = ?", [$badgeId])->fetch();
        if (!$badge) {
            return ['success' => false, 'message' => 'Badge not found'];
        }

        // Check badge limits
        if ($badge['max_awards']) {
            $awardCount = $this->query(
                "SELECT COUNT(*) as count FROM user_badges WHERE badge_id = ?",
                [$badgeId]
            )->fetch()['count'];

            if ($awardCount >= $badge['max_awards']) {
                return ['success' => false, 'message' => 'Badge award limit reached'];
            }
        }

        // Award badge
        $this->insertInto('user_badges', [
            'user_id' => $userId,
            'user_type' => $userType,
            'badge_id' => $badgeId,
            'awarded_at' => date('Y-m-d H:i:s')
        ]);

        // Award points if badge gives points
        if ($badge['points_required'] > 0) {
            $this->awardPoints($userId, $userType, $badge['points_required'], 'badge_awarded', [
                'badge_id' => $badgeId,
                'badge_name' => $badge['badge_name']
            ]);
        }

        return [
            'success' => true,
            'badge_name' => $badge['badge_name'],
            'points_awarded' => $badge['points_required']
        ];
    }

    /**
     * Create a challenge
     */
    public function createChallenge(array $challengeData): array
    {
        $challengeRecord = [
            'challenge_name' => $challengeData['challenge_name'],
            'challenge_description' => $challengeData['challenge_description'],
            'challenge_type' => $challengeData['challenge_type'] ?? 'daily',
            'target_metric' => $challengeData['target_metric'],
            'target_value' => $challengeData['target_value'],
            'points_reward' => $challengeData['points_reward'] ?? 0,
            'badge_reward_id' => $challengeData['badge_reward_id'] ?? null,
            'bonus_multiplier' => $challengeData['bonus_multiplier'] ?? 1.00,
            'start_date' => $challengeData['start_date'],
            'end_date' => $challengeData['end_date'],
            'max_participants' => $challengeData['max_participants'] ?? null,
            'difficulty_level' => $challengeData['difficulty_level'] ?? 'medium',
            'category' => $challengeData['category'] ?? null,
            'created_by' => $challengeData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $challengeId = $this->insertInto('gamification_challenges', $challengeRecord);

        return [
            'success' => true,
            'challenge_id' => $challengeId,
            'message' => 'Challenge created successfully'
        ];
    }

    /**
     * Join a challenge
     */
    public function joinChallenge(int $userId, string $userType, int $challengeId): array
    {
        $challenge = $this->query("SELECT * FROM gamification_challenges WHERE id = ? AND is_active = 1", [$challengeId])->fetch();
        if (!$challenge) {
            return ['success' => false, 'message' => 'Challenge not found'];
        }

        // Check if challenge is full
        if ($challenge['max_participants'] && $challenge['current_participants'] >= $challenge['max_participants']) {
            return ['success' => false, 'message' => 'Challenge is full'];
        }

        // Check if user already joined
        $existing = $this->query(
            "SELECT id FROM challenge_participants WHERE challenge_id = ? AND user_id = ? AND user_type = ?",
            [$challengeId, $userId, $userType]
        )->fetch();

        if ($existing) {
            return ['success' => false, 'message' => 'Already joined this challenge'];
        }

        // Add participant
        $this->insertInto('challenge_participants', [
            'challenge_id' => $challengeId,
            'user_id' => $userId,
            'user_type' => $userType,
            'target_value' => $challenge['target_value'],
            'joined_at' => date('Y-m-d H:i:s')
        ]);

        // Update participant count
        $this->query(
            "UPDATE gamification_challenges SET current_participants = current_participants + 1 WHERE id = ?",
            [$challengeId]
        );

        return ['success' => true, 'message' => 'Joined challenge successfully'];
    }

    /**
     * Update challenge progress
     */
    public function updateChallengeProgress(int $userId, string $userType, int $challengeId, int $progressValue): array
    {
        $participant = $this->query(
            "SELECT * FROM challenge_participants WHERE challenge_id = ? AND user_id = ? AND user_type = ?",
            [$challengeId, $userId, $userType]
        )->fetch();

        if (!$participant) {
            return ['success' => false, 'message' => 'Not participating in this challenge'];
        }

        if ($participant['is_completed']) {
            return ['success' => false, 'message' => 'Challenge already completed'];
        }

        $newProgress = min($progressValue, $participant['target_value']);

        $this->query(
            "UPDATE challenge_participants SET current_progress = ? WHERE id = ?",
            [$newProgress, $participant['id']]
        );

        // Check if completed
        if ($newProgress >= $participant['target_value']) {
            $this->completeChallenge($participant['id'], $challengeId, $userId, $userType);
        }

        return [
            'success' => true,
            'progress' => $newProgress,
            'target' => $participant['target_value'],
            'completed' => $newProgress >= $participant['target_value']
        ];
    }

    /**
     * Get leaderboard
     */
    public function getLeaderboard(int $leaderboardId, int $limit = 50): array
    {
        $leaderboard = $this->query("SELECT * FROM leaderboards WHERE id = ? AND is_active = 1", [$leaderboardId])->fetch();
        if (!$leaderboard) {
            return ['error' => 'Leaderboard not found'];
        }

        $metricField = $leaderboard['metric_field'];
        $query = "";

        // Build query based on leaderboard type
        switch ($leaderboard['leaderboard_type']) {
            case 'points':
                $query = "SELECT gp.user_id, gp.user_type, gp.{$metricField} as metric_value,
                                 u.first_name, u.last_name, gp.current_level
                          FROM gamification_points gp
                          LEFT JOIN users u ON gp.user_id = u.id AND gp.user_type = 'associate'
                          WHERE gp.{$metricField} > 0";
                break;
            case 'badges':
                $query = "SELECT ub.user_id, ub.user_type, COUNT(*) as metric_value,
                                 u.first_name, u.last_name
                          FROM user_badges ub
                          LEFT JOIN users u ON ub.user_id = u.id AND ub.user_type = 'associate'
                          GROUP BY ub.user_id, ub.user_type";
                break;
            case 'network':
                // This would need to be implemented based on your MLM structure
                $query = "SELECT user_id, user_type, 0 as metric_value, first_name, last_name FROM users WHERE 1=0"; // Placeholder
                break;
        }

        if (!empty($query)) {
            $query .= " ORDER BY metric_value DESC LIMIT ?";
            $entries = $this->query($query, [$limit])->fetchAll();

            // Add ranks
            foreach ($entries as $index => &$entry) {
                $entry['rank'] = $index + 1;
            }
        } else {
            $entries = [];
        }

        return [
            'leaderboard' => $leaderboard,
            'entries' => $entries,
            'total_entries' => count($entries)
        ];
    }

    /**
     * Get user gamification profile
     */
    public function getUserProfile(int $userId, string $userType): array
    {
        $points = $this->getOrCreatePointsRecord($userId, $userType);

        $badges = $this->query(
            "SELECT ub.*, gb.badge_name, gb.badge_icon, gb.badge_color, gb.rarity_level
             FROM user_badges ub
             LEFT JOIN gamification_badges gb ON ub.badge_id = gb.id
             WHERE ub.user_id = ? AND ub.user_type = ? AND ub.is_displayed = 1
             ORDER BY ub.awarded_at DESC",
            [$userId, $userType]
        )->fetchAll();

        $achievements = $this->query(
            "SELECT ua.*, a.achievement_name, a.achievement_icon
             FROM user_achievements ua
             LEFT JOIN achievements a ON ua.achievement_id = a.id
             WHERE ua.user_id = ? AND ua.user_type = ?
             ORDER BY ua.unlocked_at DESC",
            [$userId, $userType]
        )->fetchAll();

        $activeChallenges = $this->query(
            "SELECT cp.*, gc.challenge_name, gc.target_value, gc.end_date
             FROM challenge_participants cp
             LEFT JOIN gamification_challenges gc ON cp.challenge_id = gc.id
             WHERE cp.user_id = ? AND cp.user_type = ? AND cp.is_completed = 0 AND gc.end_date >= CURDATE()
             ORDER BY gc.end_date ASC",
            [$userId, $userType]
        )->fetchAll();

        return [
            'points' => $points,
            'badges' => $badges,
            'achievements' => $achievements,
            'active_challenges' => $activeChallenges,
            'level_progress' => $this->getLevelProgress($points['experience_points']),
            'streak_info' => [
                'current_streak' => $points['streak_days'],
                'longest_streak' => $points['longest_streak']
            ]
        ];
    }

    /**
     * Get rewards catalog
     */
    public function getRewardsCatalog(): array
    {
        return $this->query(
            "SELECT * FROM rewards_catalog WHERE is_active = 1 ORDER BY points_cost ASC"
        )->fetchAll();
    }

    /**
     * Get user redemption history
     */
    public function getUserRedemptions(int $userId, string $userType, int $limit = 20): array
    {
        return $this->query(
            "SELECT rr.*, rc.reward_name, rc.reward_type
             FROM reward_redemptions rr
             LEFT JOIN rewards_catalog rc ON rr.reward_id = rc.id
             WHERE rr.user_id = ? AND rr.user_type = ?
             ORDER BY rr.redemption_date DESC LIMIT ?",
            [$userId, $userType, $limit]
        )->fetchAll();
    }

    // Helper methods

    private function getOrCreatePointsRecord(int $userId, string $userType): array
    {
        $record = $this->query(
            "SELECT * FROM gamification_points WHERE user_id = ? AND user_type = ?",
            [$userId, $userType]
        )->fetch();

        if (!$record) {
            $recordId = $this->insert([
                'user_id' => $userId,
                'user_type' => $userType,
                'points_total' => 0,
                'points_available' => 0,
                'current_level' => 1,
                'experience_points' => 0,
                'streak_days' => 0,
                'longest_streak' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $record = $this->find($recordId);
        }

        return $record;
    }

    private function calculateLevel(int $totalPoints): int
    {
        // Simple level calculation: level = floor(sqrt(points/100)) + 1
        return floor(sqrt($totalPoints / 100)) + 1;
    }

    private function getLevelProgress(int $experiencePoints): array
    {
        $currentLevel = $this->calculateLevel($experiencePoints);
        $pointsForCurrentLevel = ($currentLevel - 1) * ($currentLevel - 1) * 100;
        $pointsForNextLevel = $currentLevel * $currentLevel * 100;
        $pointsInLevel = $experiencePoints - $pointsForCurrentLevel;
        $pointsNeeded = $pointsForNextLevel - $pointsForCurrentLevel;

        return [
            'current_level' => $currentLevel,
            'points_in_level' => $pointsInLevel,
            'points_needed' => $pointsNeeded,
            'progress_percentage' => $pointsNeeded > 0 ? round(($pointsInLevel / $pointsNeeded) * 100, 2) : 100
        ];
    }

    private function logPointsTransaction(int $userId, string $userType, string $transactionType, int $pointsAmount, int $balanceBefore, int $balanceAfter, string $referenceType, array $metadata = []): void
    {
        $this->insertInto('points_transactions', [
            'user_id' => $userId,
            'user_type' => $userType,
            'transaction_type' => $transactionType,
            'points_amount' => $pointsAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_type' => $referenceType,
            'metadata' => json_encode($metadata),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function checkForBadgesAndAchievements(int $userId, string $userType, string $activityType, array $metadata): void
    {
        $badges = $this->query("SELECT * FROM gamification_badges WHERE is_active = 1");

        foreach ($badges as $badge) {
            $criteria = json_decode($badge['criteria_rules'], true);

            if ($this->checkBadgeCriteria($userId, $userType, $criteria, $activityType, $metadata)) {
                $this->awardBadge($userId, $userType, $badge['id']);
            }
        }
    }

    private function checkBadgeCriteria(int $userId, string $userType, array $criteria, string $activityType, array $metadata): bool
    {
        // Simplified badge checking logic
        foreach ($criteria as $key => $value) {
            switch ($key) {
                case 'sales_count':
                    $salesCount = $this->getUserSalesCount($userId, $userType);
                    if ($salesCount < $value) return false;
                    break;
                case 'recruits_count':
                    $recruitsCount = $this->getUserRecruitsCount($userId, $userType);
                    if ($recruitsCount < $value) return false;
                    break;
                case 'streak_days':
                    $streakDays = $this->getUserStreakDays($userId, $userType);
                    if ($streakDays < $value) return false;
                    break;
                case 'revenue_amount':
                    $revenueAmount = $this->getUserRevenueAmount($userId, $userType);
                    if ($revenueAmount < $value) return false;
                    break;
            }
        }
        return true;
    }

    private function updateActivityStreak(int $userId, string $userType): void
    {
        $pointsRecord = $this->getOrCreatePointsRecord($userId, $userType);
        $today = date('Y-m-d');
        $lastActivity = $pointsRecord['last_activity_date'];

        if ($lastActivity === $today) {
            // Already active today
            return;
        }

        $streakDays = 1;
        if ($lastActivity === date('Y-m-d', strtotime('-1 day'))) {
            // Consecutive day
            $streakDays = $pointsRecord['streak_days'] + 1;
        }

        $this->update($pointsRecord['id'], [
            'streak_days' => $streakDays,
            'longest_streak' => max($streakDays, $pointsRecord['longest_streak']),
            'last_activity_date' => $today,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function completeChallenge(int $participantId, int $challengeId, int $userId, string $userType): void
    {
        $challenge = $this->query("SELECT * FROM gamification_challenges WHERE id = ?", [$challengeId])->fetch();

        // Update participant
        $this->query(
            "UPDATE challenge_participants SET is_completed = 1, completed_at = NOW(), points_earned = ? WHERE id = ?",
            [$challenge['points_reward'], $participantId]
        );

        // Award points
        if ($challenge['points_reward'] > 0) {
            $this->awardPoints($userId, $userType, $challenge['points_reward'], 'challenge_completed', [
                'challenge_id' => $challengeId,
                'challenge_name' => $challenge['challenge_name']
            ]);
        }

        // Award badge if specified
        if ($challenge['badge_reward_id']) {
            $this->awardBadge($userId, $userType, $challenge['badge_reward_id']);
        }
    }

    // Placeholder methods (implement based on your actual data structure)
    private function getUserSalesCount(int $userId, string $userType): int { return rand(0, 10); }
    private function getUserRecruitsCount(int $userId, string $userType): int { return rand(0, 5); }
    private function getUserStreakDays(int $userId, string $userType): int { return rand(0, 30); }
    private function getUserRevenueAmount(int $userId, string $userType): float { return rand(0, 1000000); }
}
