<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;
use Exception;

class PerformanceRankCalculator
{
    protected $db;
    protected $teamRanks = [
        'Associate' => ['members' => 0, 'performance' => 0, 'target' => 1000000],
        'Sr. Associate' => ['members' => 10, 'performance' => 60, 'target' => 3500000],
        'BDM' => ['members' => 25, 'performance' => 70, 'target' => 7000000],
        'Sr. BDM' => ['members' => 50, 'performance' => 80, 'target' => 15000000],
        'Vice President' => ['members' => 100, 'performance' => 90, 'target' => 30000000],
        'President' => ['members' => 250, 'performance' => 95, 'target' => 50000000],
        'Site Manager' => ['members' => 500, 'performance' => 98, 'target' => 100000000],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Calculate team performance rank for a user
     */
    public function calculateRank($userId)
    {
        try {
            // 1. Get team size
            $teamSize = $this->getTeamSize($userId);

            // 2. Get real business volume
            $businessVolume = $this->getBusinessVolume($userId);

            $currentRank = 'Associate';
            foreach ($this->teamRanks as $rank => $criteria) {
                if ($teamSize >= $criteria['members'] && $businessVolume >= ($criteria['target'] ?? 0)) {
                    $currentRank = $rank;
                }
            }

            return [
                'success' => true,
                'rank' => $currentRank,
                'team_size' => $teamSize,
                'business_volume' => $businessVolume,
                'performance' => $this->calculatePerformancePercent($currentRank, $businessVolume),
                'next_rank_info' => $this->getNextRankInfo($currentRank, $teamSize, $businessVolume)
            ];

        } catch (Exception $e) {
            error_log("Performance Rank Calculation Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getTeamSize($userId)
    {
        $downline = $this->getDownlineIds($userId);
        return count($downline);
    }

    /**
     * Get all downline IDs recursively
     */
    public function getDownlineIds($userId)
    {
        try {
            // Using a recursive CTE for performance (MariaDB 10.2.2+ / MySQL 8.0+)
            $sql = "
                WITH RECURSIVE downline AS (
                    SELECT user_id, sponsor_user_id
                    FROM mlm_profiles
                    WHERE sponsor_user_id = ?
                    UNION ALL
                    SELECT p.user_id, p.sponsor_user_id
                    FROM mlm_profiles p
                    INNER JOIN downline d ON p.sponsor_user_id = d.user_id
                )
                SELECT user_id FROM downline
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);

        } catch (Exception $e) {
            // Fallback for older databases or if CTE fails
            $downline = [];
            $queue = [$userId];
            $processed = [];

            while (!empty($queue)) {
                $parentId = array_shift($queue);
                if (in_array($parentId, $processed)) continue;
                $processed[] = $parentId;

                $stmt = $this->db->prepare("SELECT user_id FROM mlm_profiles WHERE sponsor_user_id = ?");
                $stmt->execute([$parentId]);
                $children = $stmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($children as $childId) {
                    if (!in_array($childId, $downline)) {
                        $downline[] = $childId;
                        $queue[] = $childId;
                    }
                }
            }
            return $downline;
        }
    }

    public function getBusinessVolume($userId)
    {
        $downline = $this->getDownlineIds($userId);
        if (empty($downline)) return 0;

        $placeholders = implode(',', array_fill(0, count($downline), '?'));
        
        // Sum sales from legacy property_sales
        $sqlLegacy = "SELECT SUM(sale_amount) FROM property_sales WHERE agent_id IN ($placeholders) OR buyer_id IN ($placeholders)";
        $stmtLegacy = $this->db->prepare($sqlLegacy);
        $params = array_merge($downline, $downline);
        $stmtLegacy->execute($params);
        $legacyVolume = (float)$stmtLegacy->fetchColumn() ?: 0;

        // Sum sales from new plot_bookings (V2)
        $sqlV2 = "SELECT SUM(booking_amount) FROM plot_bookings WHERE associate_id IN ($placeholders) AND status IN ('confirmed', 'completed')";
        $stmtV2 = $this->db->prepare($sqlV2);
        $stmtV2->execute($downline);
        $v2Volume = (float)$stmtV2->fetchColumn() ?: 0;
        
        return $legacyVolume + $v2Volume;
    }

    protected function getAveragePerformance($userId)
    {
        // Placeholder for real performance metric
        // In real app, this would query sales volume vs targets
        return 75; // Mock 75% for now
    }

    protected function getNextRankInfo($currentRank, $teamSize, $performance)
    {
        $ranks = array_keys($this->teamRanks);
        $currentIndex = array_search($currentRank, $ranks);
        
        if ($currentIndex === false || $currentIndex >= count($ranks) - 1) {
            return null;
        }

        $nextRank = $ranks[$currentIndex + 1];
        $criteria = $this->teamRanks[$nextRank];

        return [
            'next_rank' => $nextRank,
            'required_members' => $criteria['members'],
            'required_bv' => $criteria['target'],
            'members_needed' => max(0, $criteria['members'] - $teamSize),
            'bv_needed' => max(0, $criteria['target'] - $performance)
        ];
    }

    protected function calculatePerformancePercent($currentRank, $bv)
    {
        $target = $this->teamRanks[$currentRank]['target'] ?? 0;
        if ($target <= 0) return 100;
        return min(100, round(($bv / $target) * 100));
    }

    /**
     * Get nested hierarchy tree for D3.js (Optimized)
     */
    public function getHierarchyTree($userId, $maxLevels = 3)
    {
        // 1. Fetch all members in the local network up to maxLevels in one query
        // Using a modified CTE to include depth
        $sql = "
            WITH RECURSIVE network AS (
                SELECT u.id, u.name, mp.current_level, mp.referral_code, mp.status, mp.sponsor_user_id, 0 as depth
                FROM users u
                JOIN mlm_profiles mp ON u.id = mp.user_id
                WHERE u.id = ?
                
                UNION ALL
                
                SELECT u.id, u.name, mp.current_level, mp.referral_code, mp.status, mp.sponsor_user_id, n.depth + 1
                FROM users u
                JOIN mlm_profiles mp ON u.id = mp.user_id
                JOIN network n ON mp.sponsor_user_id = n.id
                WHERE n.depth < ?
            )
            SELECT * FROM network
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $maxLevels]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return null;

        // 2. Build the tree in memory
        $members = [];
        foreach ($rows as $row) {
            $members[$row['id']] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'type' => $row['current_level'],
                'referral_code' => $row['referral_code'],
                'status' => $row['status'],
                'sponsor_id' => $row['sponsor_user_id'],
                'children' => []
            ];
        }

        $root = null;
        foreach ($members as $id => &$member) {
            if ($id == $userId) {
                $root = &$member;
            } else if (isset($members[$member['sponsor_id']])) {
                $members[$member['sponsor_id']]['children'][] = &$member;
            }
        }

        return $root;
    }
}
