<?php

/**
 * Team Management Controller
 * Comprehensive team management system with hierarchy, performance, and communication
 */

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

class TeamManagementController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Display team overview dashboard
     */
    public function index()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'] ?? 0;

        try {
            // Get team statistics
            $teamStats = $this->getTeamStatistics($userId);

            // Get recent team activities
            $recentActivities = $this->getRecentTeamActivities($userId);

            // Get top performers
            $topPerformers = $this->getTopPerformers($userId);

            $this->render('pages/team-management', [
                'page_title' => 'Team Management - APS Dream Home',
                'page_description' => 'Manage your team members and track performance',
                'team_stats' => $teamStats,
                'recent_activities' => $recentActivities,
                'top_performers' => $topPerformers
            ]);
        } catch (Exception $e) {
            error_log("Team Management Error: " . $e->getMessage());
            $this->render('pages/team-management', [
                'page_title' => 'Team Management - APS Dream Home',
                'page_description' => 'Manage your team members and track performance',
                'error' => 'Failed to load team data'
            ]);
        }
    }

    /**
     * Get team statistics
     */
    private function getTeamStatistics($userId)
    {
        try {
            // Get total team members
            // fetchOne() method exists in Database class at line 102-105
            $totalMembers = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM mlm_profiles WHERE sponsor_id = ? OR user_id = ?",
                [$userId, $userId]
            );

            // Get active members (last 30 days)
            // fetchOne() method exists in Database class at line 102-105
            $activeMembers = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM users u 
                 JOIN mlm_profiles m ON u.id = m.user_id 
                 WHERE (m.sponsor_id = ? OR u.id = ?) AND u.last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                [$userId, $userId]
            );

            // Get new members this month
            // fetchOne() method exists in Database class at line 102-105
            $newMembers = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM mlm_profiles 
                 WHERE sponsor_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
                [$userId]
            );

            // Get team performance metrics
            // fetchOne() method exists in Database class at line 102-105
            $totalCommission = $this->db->fetchOne(
                "SELECT COALESCE(SUM(c.amount), 0) as total FROM commissions c 
                 JOIN mlm_profiles m ON c.user_id = m.user_id 
                 WHERE m.sponsor_id = ? OR m.user_id = ?",
                [$userId, $userId]
            );

            // Get team levels distribution
            $levelDistribution = $this->db->fetchAll(
                "SELECT level, COUNT(*) as count FROM mlm_profiles 
                 WHERE sponsor_id = ? OR user_id = ? 
                 GROUP BY level 
                 ORDER BY level",
                [$userId, $userId]
            );

            return [
                'total_members' => $totalMembers['count'] ?? 0,
                'active_members' => $activeMembers['count'] ?? 0,
                'new_members' => $newMembers['count'] ?? 0,
                'total_commission' => number_format($totalCommission['total'] ?? 0),
                'level_distribution' => $levelDistribution,
                'growth_rate' => $totalMembers['count'] > 0 ? round(($newMembers['count'] / $totalMembers['count']) * 100, 2) : 0
            ];
        } catch (Exception $e) {
            error_log("Team Statistics Error: " . $e->getMessage());
            return [
                'total_members' => 0,
                'active_members' => 0,
                'new_members' => 0,
                'total_commission' => 0,
                'level_distribution' => [],
                'growth_rate' => 0
            ];
        }
    }

    /**
     * Get recent team activities
     */
    private function getRecentTeamActivities($userId)
    {
        try {
            $activities = $this->db->fetchAll(
                "SELECT 
                    u.name as user_name,
                    u.email,
                    m.action,
                    m.description,
                    m.created_at
                 FROM user_activities m 
                 JOIN users u ON m.user_id = u.id 
                 WHERE m.user_id = ? OR m.related_user_id = ?
                 ORDER BY m.created_at DESC 
                 LIMIT 10",
                [$userId, $userId]
            );

            return $activities;
        } catch (Exception $e) {
            error_log("Recent Activities Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top performers
     */
    private function getTopPerformers($userId)
    {
        try {
            // fetchOne() method exists in Database class at line 102-105
            $performers = $this->db->fetchOne(
                "SELECT 
                    u.name,
                    u.email,
                    m.level,
                    (SELECT COALESCE(SUM(c.amount), 0) FROM commissions c WHERE c.user_id = u.id) as commission,
                    (SELECT COUNT(*) FROM mlm_profiles WHERE sponsor_id = u.id) as team_size
                 FROM users u 
                 JOIN mlm_profiles m ON u.id = m.user_id 
                 WHERE m.sponsor_id = ? OR u.id = ?
                 ORDER BY commission DESC, team_size DESC 
                 LIMIT 5",
                [$userId, $userId]
            );

            return $performers;
        } catch (Exception $e) {
            error_log("Top Performers Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add team member
     */
    public function addTeamMember()
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            $memberData = [
                'user_id' => Security::sanitize($data['user_id'] ?? 0),
                'name' => Security::sanitize($data['name'] ?? ''),
                'email' => Security::sanitize($data['email'] ?? ''),
                'phone' => Security::sanitize($data['phone'] ?? ''),
                'position' => Security::sanitize($data['position'] ?? 'left'),
                'level' => 1,
                'sponsor_id' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert user
            $this->db->execute(
                "INSERT INTO users (name, email, phone, created_at) VALUES (?, ?, ?)",
                [$memberData['name'], $memberData['email'], $memberData['phone'], $memberData['created_at']]
            );

            $newUserId = $this->db->lastInsertId();

            // Insert MLM profile
            $this->db->execute(
                "INSERT INTO mlm_profiles (user_id, sponsor_id, level, position, created_at) VALUES (?, ?, ?, ?, ?)",
                [$newUserId, $memberData['sponsor_id'], $memberData['level'], $memberData['position'], $memberData['created_at']]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Team member added successfully',
                'member' => $memberData
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add team member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get team member details
     */
    public function getTeamMember($memberId)
    {
        $this->requireLogin();

        try {
            // fetchOne() method exists in Database class at line 102-105
            $member = $this->db->fetchOne(
                "SELECT 
                    u.id,
                    u.name,
                    u.email,
                    u.phone,
                    u.created_at,
                    m.level,
                    m.position,
                    m.sponsor_id,
                    sp.name as sponsor_name
                 FROM users u 
                 JOIN mlm_profiles m ON u.id = m.user_id 
                 LEFT JOIN users sp ON sp.id = m.sponsor_id 
                 WHERE u.id = ?",
                [$memberId]
            );

            return $this->jsonResponse([
                'success' => true,
                'member' => $member
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get team member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update team member
     */
    public function updateTeamMember($memberId)
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            $updateData = [
                'name' => Security::sanitize($data['name'] ?? ''),
                'email' => Security::sanitize($data['email'] ?? ''),
                'phone' => Security::sanitize($data['phone'] ?? ''),
                'position' => Security::sanitize($data['position'] ?? 'left'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update user
            $this->db->execute(
                "UPDATE users SET name = ?, email = ?, phone = ?, updated_at = ? WHERE id = ?",
                [$updateData['name'], $updateData['email'], $updateData['phone'], $updateData['updated_at'], $memberId]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Team member updated successfully',
                'member' => $updateData
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update team member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete team member
     */
    public function deleteTeamMember($memberId)
    {
        $this->requireLogin();

        try {
            // Check if member is not the current user
            if ($memberId == $_SESSION['user_id']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Cannot delete your own account'
                ], 400);
            }

            // Delete MLM profile first
            $this->db->execute(
                "DELETE FROM mlm_profiles WHERE user_id = ?",
                [$memberId]
            );

            // Delete user
            $this->db->execute(
                "DELETE FROM users WHERE id = ?",
                [$memberId]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Team member deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to delete team member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send team message
     */
    public function sendTeamMessage()
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            $messageData = [
                'sender_id' => $_SESSION['user_id'],
                'message' => Security::sanitize($data['message'] ?? ''),
                'type' => Security::sanitize($data['type'] ?? 'announcement'),
                'recipients' => Security::sanitize($data['recipients'] ?? 'all'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert team message
            $this->db->execute(
                "INSERT INTO team_messages (sender_id, message, type, recipients, created_at) VALUES (?, ?, ?, ?, ?)",
                [
                    $messageData['sender_id'],
                    $messageData['message'],
                    $messageData['type'],
                    $messageData['recipients'],
                    $messageData['created_at']
                ]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Team message sent successfully',
                'message_data' => $messageData
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to send team message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get team messages
     */
    public function getTeamMessages()
    {
        $this->requireLogin();

        try {
            $messages = $this->db->fetchAll(
                "SELECT 
                    tm.message,
                    tm.type,
                    tm.recipients,
                    tm.created_at,
                    u.name as sender_name
                 FROM team_messages tm 
                 JOIN users u ON tm.sender_id = u.id 
                 ORDER BY tm.created_at DESC 
                 LIMIT 50"
            );

            return $this->jsonResponse([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get team messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData(): array
    {
        $data = [];

        // Get JSON data
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?: [];
        }

        // Merge with POST data
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }

        // Merge with GET data
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }

        return $data;
    }
}
