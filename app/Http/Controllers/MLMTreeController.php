<?php

/**
 * MLM Network Tree Controller
 * Provides interactive genealogy visualization with D3.js
 */

namespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Core\Database\Database;

class MLMTreeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Show MLM genealogy tree page
     */
    public function genealogy()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_SESSION['user_id'] ?? null;
        $userType = $_SESSION['user_type'] ?? null;

        // Allow admin to view any user's tree
        $viewUserId = $_GET['user_id'] ?? $userId;

        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Get current user info
        $currentUser = $this->db->fetchOne(
            "SELECT id, name, email, customer_id, referral_code, referred_by 
             FROM users WHERE id = ?",
            [$viewUserId]
        );

        if (!$currentUser) {
            $_SESSION['error'] = "User not found";
            header('Location: ' . BASE_URL . '/associate/dashboard');
            exit;
        }

        // Get network statistics
        $stats = $this->getNetworkStats($viewUserId);

        // Get upline (parent chain)
        $upline = $this->getUpline($viewUserId);

        $base = BASE_URL;
        include __DIR__ . '/../views/mlm/genealogy.php';
    }

    /**
     * API: Get network tree data for D3.js
     */
    public function getTreeData()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_GET['root_id'] ?? ($_SESSION['user_id'] ?? null);
        $levels = min($_GET['levels'] ?? 5, 10); // Max 10 levels

        if (!$userId) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $treeData = $this->buildTree($userId, $levels);
            echo json_encode($treeData);
        } catch (\Exception $e) {
            error_log("MLM Tree error: " . $e->getMessage());
            echo json_encode(['error' => 'Failed to load tree data']);
        }
        exit;
    }

    /**
     * API: Search network members
     */
    public function search()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        $query = $_GET['q'] ?? '';
        $rootId = $_SESSION['user_id'] ?? null;

        if (!$rootId || empty($query)) {
            echo json_encode([]);
            exit;
        }

        try {
            // Search within user's network
            $results = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email, u.customer_id, u.referral_code,
                        nt.level, nt.position,
                        (SELECT COUNT(*) FROM network_tree WHERE parent_id = u.id) as downline_count,
                        (SELECT COALESCE(SUM(commission_earnings), 0) FROM wallet_points WHERE user_id = u.id) as total_commission
                 FROM users u
                 JOIN network_tree nt ON u.id = nt.associate_id
                 WHERE nt.root_id = ? 
                 AND (u.name LIKE ? OR u.email LIKE ? OR u.customer_id LIKE ?)
                 LIMIT 20",
                [$rootId, "%$query%", "%$query%", "%$query%"]
            );

            echo json_encode($results);
        } catch (\Exception $e) {
            echo json_encode([]);
        }
        exit;
    }

    /**
     * API: Get member details
     */
    public function getMemberDetails()
    {
        header('Content-Type: application/json');

        $memberId = $_GET['id'] ?? null;

        if (!$memberId) {
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        try {
            $member = $this->db->fetchOne(
                "SELECT u.*, 
                        wp.points_balance, wp.total_earned, wp.commission_earnings,
                        (SELECT COUNT(*) FROM network_tree WHERE parent_id = u.id) as direct_referrals,
                        (SELECT COUNT(*) FROM network_tree WHERE root_id = u.id) as total_team_size
                 FROM users u
                 LEFT JOIN wallet_points wp ON u.id = wp.user_id
                 WHERE u.id = ?",
                [$memberId]
            );

            if (!$member) {
                echo json_encode(['error' => 'Member not found']);
                exit;
            }

            // Get recent commissions
            $commissions = $this->db->fetchAll(
                "SELECT * FROM commissions 
                 WHERE associate_id = ? 
                 ORDER BY created_at DESC LIMIT 5",
                [$memberId]
            );

            // Get direct downline
            $downline = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email, u.customer_id, u.created_at
                 FROM users u
                 JOIN network_tree nt ON u.id = nt.associate_id
                 WHERE nt.parent_id = ?
                 ORDER BY u.created_at DESC",
                [$memberId]
            );

            echo json_encode([
                'member' => $member,
                'recent_commissions' => $commissions,
                'direct_downline' => $downline
            ]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Failed to load details']);
        }
        exit;
    }

    /**
     * Build hierarchical tree data
     */
    private function buildTree($rootId, $maxLevels = 5, $currentLevel = 0)
    {
        if ($currentLevel >= $maxLevels) {
            return null;
        }

        // Get user with network info
        try {
            // Check if root_id exists
            $checkColumn = $this->db->query("SHOW COLUMNS FROM network_tree LIKE 'root_id'");
            $hasRootId = !empty($checkColumn);

            if ($hasRootId) {
                $user = $this->db->fetchOne(
                    "SELECT u.*, 
                            wp.points_balance, wp.commission_earnings,
                            (SELECT COUNT(*) FROM network_tree WHERE root_id = u.id) as team_size
                     FROM users u
                     LEFT JOIN wallet_points wp ON u.id = wp.user_id
                     WHERE u.id = ?",
                    [$rootId]
                );
            } else {
                $user = $this->db->fetchOne(
                    "SELECT u.*, 
                            wp.points_balance, wp.commission_earnings,
                            (SELECT COUNT(*) FROM network_tree WHERE associate_id = u.id OR parent_id = u.id) as team_size
                     FROM users u
                     LEFT JOIN wallet_points wp ON u.id = wp.user_id
                     WHERE u.id = ?",
                    [$rootId]
                );
            }
        } catch (\Exception $e) {
            $user = null;
        }

        if (!$user) {
            return null;
        }

        // Get children
        try {
            if ($hasRootId) {
                $children = $this->db->fetchAll(
                    "SELECT u.id, u.name, u.email, u.customer_id, u.referral_code, u.status,
                            u.created_at as join_date,
                            wp.points_balance, wp.commission_earnings,
                            (SELECT COUNT(*) FROM network_tree WHERE root_id = u.id) as team_size,
                            nt.level, nt.position
                     FROM users u
                     JOIN network_tree nt ON u.id = nt.associate_id
                     LEFT JOIN wallet_points wp ON u.id = wp.user_id
                     WHERE nt.parent_id = ? AND nt.level <= ?
                     ORDER BY nt.position ASC, u.created_at ASC",
                    [$rootId, $maxLevels]
                );
            } else {
                $children = $this->db->fetchAll(
                    "SELECT u.id, u.name, u.email, u.customer_id, u.referral_code, u.status,
                            u.created_at as join_date,
                            wp.points_balance, wp.commission_earnings,
                            (SELECT COUNT(*) FROM network_tree WHERE associate_id = u.id OR parent_id = u.id) as team_size,
                            nt.level, nt.position
                     FROM users u
                     JOIN network_tree nt ON u.id = nt.associate_id
                     LEFT JOIN wallet_points wp ON u.id = wp.user_id
                     WHERE nt.parent_id = ? AND nt.level <= ?
                     ORDER BY nt.position ASC, u.created_at ASC",
                    [$rootId, $maxLevels]
                );
            }
        } catch (\Exception $e) {
            $children = [];
        }

        $node = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'customer_id' => $user['customer_id'],
            'referral_code' => $user['referral_code'],
            'status' => $user['status'],
            'wallet_balance' => $user['points_balance'] ?? 0,
            'commission_earned' => $user['commission_earnings'] ?? 0,
            'team_size' => $user['team_size'] ?? 0,
            'join_date' => $user['created_at'] ?? date('Y-m-d'),
            'children' => []
        ];

        // Recursively build children
        foreach ($children as $child) {
            $childNode = [
                'id' => $child['id'],
                'name' => $child['name'],
                'email' => $child['email'],
                'customer_id' => $child['customer_id'],
                'referral_code' => $child['referral_code'],
                'status' => $child['status'],
                'wallet_balance' => $child['points_balance'] ?? 0,
                'commission_earned' => $child['commission_earnings'] ?? 0,
                'team_size' => $child['team_size'] ?? 0,
                'level' => $child['level'],
                'position' => $child['position'],
                'join_date' => $child['join_date'],
                'children' => []
            ];

            // Recursively get grandchildren
            if ($currentLevel < $maxLevels - 1) {
                $grandchildren = $this->buildTree($child['id'], $maxLevels, $currentLevel + 1);
                if ($grandchildren && !empty($grandchildren['children'])) {
                    $childNode['children'] = $grandchildren['children'];
                }
            }

            $node['children'][] = $childNode;
        }

        return $node;
    }

    /**
     * Get network statistics
     */
    private function getNetworkStats($userId)
    {
        try {
            // Check if root_id column exists
            $checkColumn = $this->db->query("SHOW COLUMNS FROM network_tree LIKE 'root_id'");
            $hasRootId = !empty($checkColumn);

            if ($hasRootId) {
                // Use root_id if available
                $stats = $this->db->fetchOne(
                    "SELECT 
                        (SELECT COUNT(*) FROM network_tree WHERE root_id = ?) as total_members,
                        (SELECT COUNT(*) FROM network_tree WHERE parent_id = ?) as direct_referrals,
                        (SELECT MAX(level) FROM network_tree WHERE root_id = ?) as max_depth,
                        (SELECT COALESCE(SUM(wp.commission_earnings), 0) 
                         FROM wallet_points wp 
                         JOIN network_tree nt ON wp.user_id = nt.associate_id 
                         WHERE nt.root_id = ?) as total_team_commission",
                    [$userId, $userId, $userId, $userId]
                );

                // Level-wise breakdown
                $levelStats = $this->db->fetchAll(
                    "SELECT level, COUNT(*) as count 
                     FROM network_tree 
                     WHERE root_id = ? 
                     GROUP BY level 
                     ORDER BY level",
                    [$userId]
                );
            } else {
                // Fallback - use associate_id as root
                $stats = $this->db->fetchOne(
                    "SELECT 
                        (SELECT COUNT(*) FROM network_tree WHERE associate_id = ? OR parent_id = ?) as total_members,
                        (SELECT COUNT(*) FROM network_tree WHERE parent_id = ?) as direct_referrals,
                        (SELECT MAX(level) FROM network_tree WHERE associate_id = ? OR parent_id = ?) as max_depth,
                        (SELECT COALESCE(SUM(wp.commission_earnings), 0) 
                         FROM wallet_points wp 
                         WHERE wp.user_id = ?) as total_team_commission",
                    [$userId, $userId, $userId, $userId, $userId, $userId]
                );

                // Level-wise breakdown without root_id
                $levelStats = $this->db->fetchAll(
                    "SELECT level, COUNT(*) as count 
                     FROM network_tree 
                     WHERE associate_id = ? OR parent_id = ?
                     GROUP BY level 
                     ORDER BY level",
                    [$userId, $userId]
                );
            }
        } catch (\Exception $e) {
            // Return default stats if query fails
            $stats = [
                'total_members' => 0,
                'direct_referrals' => 0,
                'max_depth' => 0,
                'total_team_commission' => 0
            ];
            $levelStats = [];
        }

        return [
            'total_members' => $stats['total_members'] ?? 0,
            'direct_referrals' => $stats['direct_referrals'] ?? 0,
            'max_depth' => $stats['max_depth'] ?? 0,
            'total_team_commission' => $stats['total_team_commission'] ?? 0,
            'level_breakdown' => $levelStats
        ];
    }

    /**
     * Get upline chain
     */
    private function getUpline($userId, $maxLevels = 5)
    {
        $upline = [];
        $currentId = $userId;
        $level = 0;

        while ($level < $maxLevels) {
            $user = $this->db->fetchOne(
                "SELECT u.id, u.name, u.email, u.customer_id, u.referred_by,
                        nt.level, nt.parent_id
                 FROM users u
                 LEFT JOIN network_tree nt ON u.id = nt.associate_id
                 WHERE u.id = ?",
                [$currentId]
            );

            if (!$user || !$user['referred_by']) {
                break;
            }

            $parent = $this->db->fetchOne(
                "SELECT id, name, email, customer_id FROM users WHERE id = ?",
                [$user['referred_by']]
            );

            if ($parent) {
                $upline[] = array_merge($parent, ['level' => $level + 1]);
                $currentId = $parent['id'];
            }

            $level++;
        }

        return $upline;
    }
}
