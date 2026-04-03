<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Achievement Controller
 * Handles user gamification, points, and badges
 */
class AchievementController extends BaseController
{
    protected $db;
    private $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * User achievements dashboard
     */
    public function index()
    {
        $userId = $_SESSION['user_id'] ?? 0;

        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        $userPoints = $this->getUserPoints($userId);
        $userBadges = $this->getUserBadges($userId);
        $allBadges = $this->getAllBadges();
        $leaderboard = $this->getLeaderboard(10);
        $recentPoints = $this->getRecentPoints($userId);

        $data = [
            'page_title' => 'My Achievements - APS Dream Home',
            'user_points' => $userPoints,
            'user_badges' => $userBadges,
            'all_badges' => $allBadges,
            'leaderboard' => $leaderboard,
            'recent_points' => $recentPoints
        ];

        $this->render('dashboard/achievements', $data);
    }

    /**
     * Award points to user
     */
    public function awardPoints($userId, $action, $points = null)
    {
        try {
            // Point values for different actions
            $pointValues = [
                'registration' => 100,
                'profile_complete' => 50,
                'property_view' => 10,
                'property_enquiry' => 25,
                'site_visit' => 50,
                'booking' => 200,
                'referral' => 150,
                'review' => 30,
                'social_share' => 20,
                'deal_won' => 500
            ];

            $points = $points ?? ($pointValues[$action] ?? 10);

            // Insert points record
            $sql = "INSERT INTO user_points (user_id, action, points, description, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $action, $points, $this->getActionDescription($action)]);

            // Update user total points
            $sql = "UPDATE users SET total_points = COALESCE(total_points, 0) + ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$points, $userId]);

            // Check for badge awards
            $this->checkAndAwardBadges($userId);

            return true;
        } catch (\Exception $e) {
            error_log("AchievementController::awardPoints error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user points
     */
    private function getUserPoints($userId)
    {
        $sql = "SELECT COALESCE(SUM(points), 0) as total_points,
                       COUNT(*) as total_actions
                FROM user_points 
                WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $points = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get this month's points
        $sql = "SELECT COALESCE(SUM(points), 0) as month_points
                FROM user_points 
                WHERE user_id = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $monthPoints = $stmt->fetch(\PDO::FETCH_ASSOC);

        return array_merge($points, $monthPoints);
    }

    /**
     * Get user badges
     */
    private function getUserBadges($userId)
    {
        $sql = "SELECT ub.*, b.name, b.description, b.icon, b.color, b.requirement_type, b.requirement_value
                FROM user_badges ub
                JOIN badges b ON ub.badge_id = b.id
                WHERE ub.user_id = ?
                ORDER BY ub.awarded_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all available badges
     */
    private function getAllBadges()
    {
        $sql = "SELECT * FROM badges ORDER BY requirement_value ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get leaderboard
     */
    private function getLeaderboard($limit = 10)
    {
        $sql = "SELECT u.id, u.name, u.email, COALESCE(SUM(up.points), 0) as total_points,
                       COUNT(DISTINCT ub.badge_id) as badge_count
                FROM users u
                LEFT JOIN user_points up ON u.id = up.user_id
                LEFT JOIN user_badges ub ON u.id = ub.user_id
                WHERE u.role = 'customer'
                GROUP BY u.id
                ORDER BY total_points DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recent points for user
     */
    private function getRecentPoints($userId, $limit = 10)
    {
        $sql = "SELECT * FROM user_points WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check and award badges based on user activity
     */
    private function checkAndAwardBadges($userId)
    {
        $userStats = $this->getUserStats($userId);

        $badges = [
            ['name' => 'New Member', 'icon' => 'user', 'color' => 'info', 'req' => 'registration', 'value' => 1],
            ['name' => 'Property Explorer', 'icon' => 'search', 'color' => 'primary', 'req' => 'property_view', 'value' => 10],
            ['name' => 'Serious Buyer', 'icon' => 'heart', 'color' => 'danger', 'req' => 'property_enquiry', 'value' => 5],
            ['name' => 'Site Visitor', 'icon' => 'car', 'color' => 'warning', 'req' => 'site_visit', 'value' => 3],
            ['name' => 'Deal Closer', 'icon' => 'trophy', 'color' => 'success', 'req' => 'booking', 'value' => 1],
            ['name' => 'Champion', 'icon' => 'crown', 'color' => 'dark', 'req' => 'points', 'value' => 1000]
        ];

        foreach ($badges as $badge) {
            // Check if user already has this badge
            $sql = "SELECT COUNT(*) FROM user_badges ub 
                    JOIN badges b ON ub.badge_id = b.id 
                    WHERE ub.user_id = ? AND b.name = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $badge['name']]);
            if ($stmt->fetchColumn() > 0) continue;

            // Check if user qualifies
            $qualified = false;
            switch ($badge['req']) {
                case 'registration':
                    $qualified = true; // Award on first activity
                    break;
                case 'points':
                    $qualified = $userStats['total_points'] >= $badge['value'];
                    break;
                default:
                    $qualified = ($userStats[$badge['req'] . '_count'] ?? 0) >= $badge['value'];
            }

            if ($qualified) {
                // Get or create badge
                $sql = "SELECT id FROM badges WHERE name = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$badge['name']]);
                $badgeId = $stmt->fetchColumn();

                if (!$badgeId) {
                    $sql = "INSERT INTO badges (name, description, icon, color, requirement_type, requirement_value, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $badge['name'],
                        'Awarded for ' . $badge['req'] . ' achievement',
                        $badge['icon'],
                        $badge['color'],
                        $badge['req'],
                        $badge['value']
                    ]);
                    $badgeId = $this->pdo->lastInsertId();
                }

                // Award badge to user
                $sql = "INSERT INTO user_badges (user_id, badge_id, awarded_at) VALUES (?, ?, NOW())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$userId, $badgeId]);
            }
        }
    }

    /**
     * Get user statistics for badge qualification
     */
    private function getUserStats($userId)
    {
        $sql = "SELECT 
                    COALESCE(SUM(points), 0) as total_points,
                    SUM(CASE WHEN action = 'property_view' THEN 1 ELSE 0 END) as property_view_count,
                    SUM(CASE WHEN action = 'property_enquiry' THEN 1 ELSE 0 END) as property_enquiry_count,
                    SUM(CASE WHEN action = 'site_visit' THEN 1 ELSE 0 END) as site_visit_count,
                    SUM(CASE WHEN action = 'booking' THEN 1 ELSE 0 END) as booking_count
                FROM user_points 
                WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get action description
     */
    private function getActionDescription($action)
    {
        $descriptions = [
            'registration' => 'Welcome bonus for joining APS Dream Home',
            'profile_complete' => 'Completed profile information',
            'property_view' => 'Viewed a property listing',
            'property_enquiry' => 'Submitted a property enquiry',
            'site_visit' => 'Scheduled or completed a site visit',
            'booking' => 'Successfully booked a property',
            'referral' => 'Referred a new customer',
            'review' => 'Submitted a property review',
            'social_share' => 'Shared property on social media',
            'deal_won' => 'Closed a successful deal'
        ];
        return $descriptions[$action] ?? 'Activity points';
    }
}
