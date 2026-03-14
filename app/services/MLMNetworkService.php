<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * MLMNetworkService
 * Handles genealogy, downline tracking, and team statistics.
 */
class MLMNetworkService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get the downline tree for a specific user.
     * Returns a recursive tree structure or a flattened list depending on depth.
     */
    public function getDownline($userId, $maxLevels = 3)
    {
        return $this->fetchRecursive($userId, 1, $maxLevels);
    }

    private function fetchRecursive($parentId, $currentLevel, $maxLevels)
    {
        if ($currentLevel > $maxLevels) {
            return [];
        }

        $sql = "SELECT id, name, email, phone, rank, profile_image 
                FROM users 
                WHERE parent_id = ? 
                ORDER BY name ASC";
        
        $children = $this->db->fetchAll($sql, [$parentId]) ?? [];

        foreach ($children as &$child) {
            $child['level'] = $currentLevel;
            $child['team_size'] = $this->getTeamSize($child['id']);
            $child['direct_referrals'] = $this->getDirectCount($child['id']);
            $child['children'] = $this->fetchRecursive($child['id'], $currentLevel + 1, $maxLevels);
        }

        return $children;
    }

    /**
     * Count total members in an agent's downline (recursive).
     */
    public function getTeamSize($userId)
    {
        $sql = "WITH RECURSIVE downline AS (
                    SELECT id FROM users WHERE parent_id = ?
                    UNION ALL
                    SELECT u.id FROM users u INNER JOIN downline d ON u.parent_id = d.id
                ) SELECT COUNT(*) FROM downline";
        return (int)$this->db->fetchColumn($sql, [$userId]);
    }

    /**
     * Count direct referrals.
     */
    public function getDirectCount($userId)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE parent_id = ?";
        return (int)$this->db->fetchColumn($sql, [$userId]);
    }

    /**
     * Get commission business breakdown for an associate.
     * Shows which transaction generated which commission.
     */
    public function getBusinessBreakdown($userId)
    {
        $sql = "SELECT 
                    cl.*, 
                    p.title as property_name, 
                    u.name as buyer_name,
                    cl.amount as commission_earned,
                    cl.status as payout_status
                FROM mlm_commission_ledger cl
                JOIN sales s ON cl.sale_id = s.id
                JOIN properties p ON s.property_id = p.id
                JOIN users u ON s.buyer_id = u.id
                WHERE cl.user_id = ?
                ORDER BY cl.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]) ?? [];
    }
}
