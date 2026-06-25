<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * MLMNetworkService
 * Handles genealogy, downline tracking, and team statistics.
 * Uses network_tree table (not users.parent_id) for MLM hierarchy.
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
     * Returns a recursive tree structure using network_tree table.
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

        $sql = "SELECT nt.associate_id as id, u.name, u.email, u.phone, u.mlm_rank as rank, u.profile_image,
                       nt.position, nt.level as tree_level
                FROM network_tree nt
                JOIN users u ON u.id = nt.associate_id
                WHERE nt.parent_id = ?
                ORDER BY u.name ASC";
        
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
                    SELECT associate_id FROM network_tree WHERE parent_id = ?
                    UNION ALL
                    SELECT nt.associate_id FROM network_tree nt INNER JOIN downline d ON nt.parent_id = d.associate_id
                ) SELECT COUNT(*) FROM downline";
        return (int)$this->db->fetchColumn($sql, [$userId]);
    }

    /**
     * Count direct referrals.
     */
    public function getDirectCount($userId)
    {
        $sql = "SELECT COUNT(*) FROM network_tree WHERE parent_id = ?";
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
                JOIN plot_bookings pb ON cl.booking_id = pb.id
                JOIN plots p ON pb.plot_id = p.id
                JOIN users u ON pb.customer_id = u.id
                WHERE cl.beneficiary_user_id = ?
                ORDER BY cl.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]) ?? [];
    }
}
