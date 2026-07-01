<?php
namespace App\Services;

use App\Core\Database\Database;

class RankEvaluationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function evaluateAll()
    {
        $profiles = $this->db->fetchAll("SELECT * FROM mlm_profiles WHERE status = 'active'");
        $results = [];
        foreach ($profiles as $profile) {
            $results[] = $this->evaluate($profile['user_id']);
        }
        return $results;
    }

    public function evaluate($userId)
    {
        $profile = $this->db->fetch("SELECT * FROM mlm_profiles WHERE user_id = ?", [$userId]);
        if (!$profile) {
            return ['user_id' => $userId, 'error' => 'No MLM profile found'];
        }

        $currentLevel = $profile['current_level'] ?? 'associate';
        $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_number ASC");

        $teamSize = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM network_tree WHERE associate_id = ? AND `level` <= 3",
            [$userId]
        );

        $directReferrals = (int)$profile['direct_referrals'];

        $monthlySales = (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(total_amount), 0) FROM bookings
             WHERE customer_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$userId]
        );

        $highestEligible = null;
        $highestLevel = null;

        foreach ($levels as $level) {
            $teamOk = $level['team_size_required'] <= $teamSize;
            $directOk = $level['direct_referrals_required'] <= $directReferrals;
            $salesOk = $level['monthly_target'] <= $monthlySales;

            if ($teamOk && $directOk && $salesOk) {
                $highestEligible = $level['level_name'];
                $highestLevel = $level['level_number'];
            }
        }

        $promoted = false;
        $fromLevel = $currentLevel;
        if ($highestEligible !== null && $highestEligible !== $currentLevel) {
            $this->db->query(
                "UPDATE mlm_profiles SET current_level = ?, rank_updated_at = NOW() WHERE user_id = ?",
                [$highestEligible, $userId]
            );

            // Sync associates.level (extension table) with new rank
            try {
                $this->db->query(
                    "UPDATE associates SET level = ? WHERE user_id = ?",
                    [$highestEligible, $userId]
                );
            } catch (\Throwable $e) {}

            // Log to mlm_rank_history
            try {
                $this->db->query(
                    "INSERT INTO mlm_rank_history (associate_id, from_rank, to_rank, qualifying_volume_at_promotion, leg_count_at_promotion, promoted_at)
                     VALUES (?, ?, ?, ?, ?, NOW())",
                    [$userId, $fromLevel, $highestEligible, $monthlySales, $teamSize]
                );
            } catch (\Throwable $e) {}

            // Broadcast rank change via WebSocket
            try {
                \App\Services\WebSocketBroadcaster::broadcastToUser($userId, [
                    'event'     => 'rank_promoted',
                    'user_id'   => $userId,
                    'from_rank' => $fromLevel,
                    'to_rank'   => $highestEligible,
                    'message'   => "Congratulations! You've been promoted from {$fromLevel} to {$highestEligible}",
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) {}

            $promoted = true;
        }

        return [
            'user_id' => $userId,
            'current_level' => $currentLevel,
            'eligible_level' => $highestEligible ?? $currentLevel,
            'team_size' => $teamSize,
            'direct_referrals' => $directReferrals,
            'monthly_sales' => $monthlySales,
            'promoted' => $promoted,
        ];
    }

    public function getProgress($userId)
    {
        $profile = $this->db->fetch("SELECT * FROM mlm_profiles WHERE user_id = ?", [$userId]);
        if (!$profile) return null;

        $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_number ASC");

        $teamSize = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM network_tree WHERE associate_id = ? AND `level` <= 3",
            [$userId]
        );
        $directReferrals = (int)$profile['direct_referrals'];
        $monthlySales = (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(total_amount), 0) FROM bookings
             WHERE customer_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$userId]
        );

        $currentLevelNum = 0;
        foreach ($levels as $l) {
            if (strtolower($l['level_name']) === strtolower($profile['current_level'] ?? '')) {
                $currentLevelNum = $l['level_number'];
                break;
            }
        }

        $nextLevel = null;
        foreach ($levels as $l) {
            if ($l['level_number'] === $currentLevelNum + 1) {
                $nextLevel = $l;
                break;
            }
        }

        $progress = [];
        if ($nextLevel) {
            $progress['next_level'] = $nextLevel['level_name'];
            $progress['team_size_progress'] = [
                'current' => $teamSize,
                'required' => $nextLevel['team_size_required'],
                'percent' => $nextLevel['team_size_required'] > 0
                    ? min(100, round($teamSize / $nextLevel['team_size_required'] * 100)) : 100,
            ];
            $progress['direct_progress'] = [
                'current' => $directReferrals,
                'required' => $nextLevel['direct_referrals_required'],
                'percent' => $nextLevel['direct_referrals_required'] > 0
                    ? min(100, round($directReferrals / $nextLevel['direct_referrals_required'] * 100)) : 100,
            ];
            $progress['sales_progress'] = [
                'current' => $monthlySales,
                'required' => $nextLevel['monthly_target'],
                'percent' => $nextLevel['monthly_target'] > 0
                    ? min(100, round($monthlySales / $nextLevel['monthly_target'] * 100)) : 100,
            ];
        }

        return [
            'current_level' => $profile['current_level'],
            'current_level_num' => $currentLevelNum,
            'team_size' => $teamSize,
            'direct_referrals' => $directReferrals,
            'monthly_sales' => $monthlySales,
            'next_level' => $progress['next_level'] ?? null,
            'progress' => $progress,
        ];
    }
}
