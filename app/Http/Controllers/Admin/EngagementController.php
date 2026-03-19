<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Engagement Controller - Custom MVC Implementation
 * Handles MLM engagement metrics and goals management in Admin panel
 */
class EngagementController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display engagement dashboard
     */
    public function index()
    {
        try {
            // Get engagement metrics
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_users,
                        COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as monthly_active,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users,
                        COUNT(CASE WHEN role = 'associate' THEN 1 END) as total_associates,
                        COUNT(CASE WHEN role = 'associate' AND last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_associates
                    FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $userMetrics = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get sales metrics
            $sql = "SELECT 
                        COUNT(*) as total_sales,
                        COALESCE(SUM(amount), 0) as total_revenue,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as sales_this_month,
                        COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount END), 0) as revenue_this_month
                    FROM sales";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $salesMetrics = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get commission metrics
            $sql = "SELECT 
                        COUNT(*) as total_commissions,
                        COALESCE(SUM(amount), 0) as total_commission_amount,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as commissions_this_month,
                        COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount END), 0) as commission_amount_this_month
                    FROM commissions";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $commissionMetrics = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get network metrics
            $sql = "SELECT 
                        COUNT(*) as total_network_connections,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_connections,
                        AVG(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END) * 100 as network_participation_rate
                    FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $networkMetrics = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Get engagement goals
            $sql = "SELECT * FROM engagement_goals WHERE status = 'active' ORDER BY priority ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $goals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'MLM Engagement - APS Dream Home',
                'active_page' => 'engagement',
                'user_metrics' => $userMetrics,
                'sales_metrics' => $salesMetrics,
                'commission_metrics' => $commissionMetrics,
                'network_metrics' => $networkMetrics,
                'goals' => $goals
            ];

            return $this->render('admin/engagement/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Engagement Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load engagement dashboard');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Display engagement goals
     */
    public function goals()
    {
        try {
            $sql = "SELECT * FROM engagement_goals ORDER BY priority ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $goals = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Calculate goal progress
            foreach ($goals as &$goal) {
                $goal['progress'] = $this->calculateGoalProgress($goal);
            }

            $data = [
                'page_title' => 'Engagement Goals - APS Dream Home',
                'active_page' => 'engagement',
                'goals' => $goals
            ];

            return $this->render('admin/engagement/goals', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Engagement Goals error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load engagement goals');
            return $this->redirect('admin/engagement');
        }
    }

    /**
     * Show the form for creating a new engagement goal
     */
    public function createGoal()
    {
        try {
            $data = [
                'page_title' => 'Create Engagement Goal - APS Dream Home',
                'active_page' => 'engagement',
                'goal_types' => ['user_growth', 'sales_target', 'commission_target', 'network_growth', 'engagement_rate'],
                'periods' => ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']
            ];

            return $this->render('admin/engagement/create_goal', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Engagement Create Goal error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load goal form');
            return $this->redirect('admin/engagement/goals');
        }
    }

    /**
     * Store a newly created engagement goal
     */
    public function storeGoal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['title', 'description', 'goal_type', 'target_value', 'period', 'start_date', 'end_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate goal type
            $validTypes = ['user_growth', 'sales_target', 'commission_target', 'network_growth', 'engagement_rate'];
            if (!in_array($data['goal_type'], $validTypes)) {
                return $this->jsonError('Invalid goal type', 400);
            }

            // Validate period
            $validPeriods = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
            if (!in_array($data['period'], $validPeriods)) {
                return $this->jsonError('Invalid period', 400);
            }

            // Validate dates
            if (!strtotime($data['start_date']) || !strtotime($data['end_date'])) {
                return $this->jsonError('Invalid date format', 400);
            }

            // Insert goal
            $sql = "INSERT INTO engagement_goals 
                    (title, description, goal_type, target_value, current_value, period, 
                     start_date, end_date, priority, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                CoreFunctionsServiceCustom::validateInput($data['title'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['description'], 'string'),
                $data['goal_type'],
                floatval($data['target_value']),
                0, // current_value starts at 0
                $data['period'],
                $data['start_date'],
                $data['end_date'],
                (int)($data['priority'] ?? 5),
                $data['status'] ?? 'active'
            ]);

            if ($result) {
                $goalId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'engagement_goal_created', [
                    'goal_id' => $goalId,
                    'title' => $data['title'],
                    'goal_type' => $data['goal_type']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Engagement goal created successfully',
                    'goal_id' => $goalId
                ]);
            }

            return $this->jsonError('Failed to create engagement goal', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Store Engagement Goal error: " . $e->getMessage());
            return $this->jsonError('Failed to create engagement goal', 500);
        }
    }

    /**
     * Update engagement goal progress
     */
    public function updateGoalProgress($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $goalId = intval($id);
            if ($goalId <= 0) {
                return $this->jsonError('Invalid goal ID', 400);
            }

            $data = $_POST;

            // Check if goal exists
            $sql = "SELECT * FROM engagement_goals WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$goalId]);
            $goal = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$goal) {
                return $this->jsonError('Goal not found', 404);
            }

            // Update current value
            $sql = "UPDATE engagement_goals 
                    SET current_value = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                floatval($data['current_value'] ?? 0),
                $goalId
            ]);

            if ($result) {
                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'engagement_goal_updated', [
                    'goal_id' => $goalId,
                    'current_value' => $data['current_value']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Goal progress updated successfully'
                ]);
            }

            return $this->jsonError('Failed to update goal progress', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Update Goal Progress error: " . $e->getMessage());
            return $this->jsonError('Failed to update goal progress', 500);
        }
    }

    /**
     * Get engagement statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // User engagement metrics
            $sql = "SELECT 
                        COUNT(*) as total_users,
                        COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_users,
                        COUNT(CASE WHEN last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as monthly_active,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users
                    FROM users";
            $result = $this->db->fetchOne($sql);
            $stats['user_engagement'] = $result ?: [];

            // Sales performance metrics
            $sql = "SELECT 
                        COUNT(*) as total_sales,
                        COALESCE(SUM(amount), 0) as total_revenue,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as sales_this_month,
                        COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount END), 0) as revenue_this_month
                    FROM sales";
            $result = $this->db->fetchOne($sql);
            $stats['sales_performance'] = $result ?: [];

            // Network growth metrics
            $sql = "SELECT 
                        COUNT(*) as total_network,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_connections,
                        AVG(CASE WHEN parent_id IS NOT NULL THEN 1 ELSE 0 END) * 100 as participation_rate
                    FROM users";
            $result = $this->db->fetchOne($sql);
            $stats['network_growth'] = $result ?: [];

            // Goal achievement metrics
            $sql = "SELECT 
                        COUNT(*) as total_goals,
                        COUNT(CASE WHEN current_value >= target_value THEN 1 END) as achieved_goals,
                        AVG(CASE WHEN target_value > 0 THEN (current_value / target_value) * 100 ELSE 0 END) as average_progress
                    FROM engagement_goals WHERE status = 'active'";
            $result = $this->db->fetchOne($sql);
            $stats['goal_achievement'] = $result ?: [];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Engagement Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * Calculate goal progress
     */
    private function calculateGoalProgress($goal)
    {
        try {
            $currentValue = floatval($goal['current_value'] ?? 0);
            $targetValue = floatval($goal['target_value'] ?? 1);
            
            if ($targetValue <= 0) {
                return 0;
            }
            
            return min(($currentValue / $targetValue) * 100, 100);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}