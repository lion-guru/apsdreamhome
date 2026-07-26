<?php

/**
 * MLM Network Tree Controller
 * Provides interactive genealogy visualization with D3.js
 */

namespace App\Http\Controllers;

require_once __DIR__ . '/Admin/AdminController.php';

use App\Core\Database\Database;

class MLMTreeController extends \App\Http\Controllers\Admin\AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $isAdmin = isset($_SESSION['admin_id']);
        $isAssociate = isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'associate';
        $isCustomer = isset($_SESSION['user_id']);
        if (!$isAdmin && !$isAssociate && !$isCustomer) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/associate/login');
            exit;
        }
        $this->db = Database::getInstance();

        // Override admin layout for associate/agent/customer roles
        $role = $_SESSION['role'] ?? '';
        if (in_array($role, ['associate', 'agent'])) {
            $this->layout = 'layouts/associate';
        } elseif (!$isAdmin) {
            $this->layout = 'layouts/base';
        }
    }

    /**
     * Show MLM tree page — role-aware layout
     */
    public function tree()
    {
        $role = $_SESSION['role'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Fetch network tree data for this user
        $treeData = ['nodes' => [], 'stats' => []];
        try {
            // Get full downline from mlm_network_tree (iterative descent for all levels)
            $allNodes = [];
            $currentParentIds = [$userId];
            $visitedIds = [$userId];
            $maxDepth = 10;
            $depth = 0;

            while (!empty($currentParentIds) && $depth < $maxDepth) {
                $placeholders = implode(',', array_fill(0, count($currentParentIds), '?'));
                $levelNodes = $this->db->fetchAll(
                    "SELECT n.id, n.associate_id as id, u.name, u.email,
                            n.level, n.position, n.parent_id,
                            COALESCE(ml.current_level, 'associate') as current_level,
                            COALESCE(ml.total_team_size, 0) as personal_bv,
                            COALESCE(ml.lifetime_sales, 0) as total_commission,
                            n.created_at as joined_at,
                            CASE WHEN u.status = 'active' THEN 1 ELSE 0 END as is_active,
                            u.id as profile_user_id
                     FROM mlm_network_tree n
                     LEFT JOIN users u ON u.id = n.associate_id
                     LEFT JOIN mlm_profiles ml ON ml.user_id = n.associate_id
                     WHERE n.parent_id IN ({$placeholders})
                     ORDER BY n.level ASC, n.position ASC",
                    $currentParentIds
                );

                $nextParentIds = [];
                foreach ($levelNodes as $ln) {
                    if (!in_array($ln['id'], $visitedIds)) {
                        $allNodes[] = $ln;
                        $visitedIds[] = $ln['id'];
                        $nextParentIds[] = $ln['id'];
                    }
                }
                $currentParentIds = $nextParentIds;
                $depth++;
            }

            // Also include self as root
            $self = $this->db->fetchOne(
                "SELECT u.id, u.name, u.email, 
                        COALESCE(ml.current_level, 'associate') as current_level,
                        COALESCE(ml.total_team_size, 0) as personal_bv,
                        COALESCE(ml.lifetime_sales, 0) as total_commission,
                        u.created_at as joined_at,
                        1 as is_active
                 FROM users u
                 LEFT JOIN mlm_profiles ml ON ml.user_id = u.id
                 WHERE u.id = ?",
                [$userId]
            );

            if ($self) {
                $self['parent_id'] = null;
                $allNodes = array_merge([$self], $allNodes);
            }

            $totalDownline = count($allNodes) - 1; // exclude self
            $leftCount = 0;
            $rightCount = 0;
            foreach ($allNodes as $n) {
                if (($n['position'] ?? '') === 'left') $leftCount++;
                elseif (($n['position'] ?? '') === 'right') $rightCount++;
            }

            $treeData = [
                'nodes' => $allNodes,
                'stats' => [
                    'total_downline' => $totalDownline,
                    'left_count' => $leftCount,
                    'right_count' => $rightCount,
                    'pairing_bonus' => 0,
                ]
            ];
        } catch (\Exception $e) {
            error_log('MLMTreeController tree error: ' . $e->getMessage());
        }

        $this->render('associate/network_tree', [
            'page_title' => 'Network Tree',
            'treeData' => $treeData,
            'current_page' => 'network',
        ]);
    }

    /**
     * Show MLM genealogy tree page
     */
    public function genealogy()
    {
        @session_start();

        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['role'] ?? null;

        // Only allow admin/super_admin to view other users' trees
        $isAdmin = in_array($userRole, ['admin', 'super_admin'], true);
        $viewUserId = $userId;
        if ($isAdmin && isset($_GET['user_id'])) {
            $viewUserId = (int)$_GET['user_id'];
        }

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
        $viewPath = __DIR__ . '/../../views/mlm/genealogy.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Fallback to admin genealogy if mlm folder doesn't exist
            include __DIR__ . '/../../views/admin/mlm/genealogy.php';
        }
    }

    /**
     * API: Get network tree data for D3.js
     */
    public function getTreeData()
    {
        header('Content-Type: application/json');

        @session_start();

        $userId = isset($_GET['root_id']) ? (int)$_GET['root_id'] : ($_SESSION['user_id'] ?? null);
        $levels = min((int)($_GET['levels'] ?? 5), 10); // Max 10 levels

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

        @session_start();

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
