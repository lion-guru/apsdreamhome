<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

class MLMNetworkService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

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
                       nt.level as tree_level
                FROM mlm_network_tree nt
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

    public function getTeamSize($userId)
    {
        $sql = "WITH RECURSIVE downline AS (
                    SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?
                    UNION ALL
                    SELECT nt.associate_id FROM mlm_network_tree nt INNER JOIN downline d ON nt.parent_id = d.associate_id
                ) SELECT COUNT(*) FROM downline";
        return (int)$this->db->fetchColumn($sql, [$userId]);
    }

    public function getDirectCount($userId)
    {
        $sql = "SELECT COUNT(*) FROM mlm_network_tree WHERE parent_id = ?";
        return (int)$this->db->fetchColumn($sql, [$userId]);
    }

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
