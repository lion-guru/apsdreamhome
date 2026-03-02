<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Associate MLM Performance Dashboard Model
 * Handles performance analytics, dashboard widgets, goals, and reporting
 */
class PerformanceDashboard extends Model
{
    protected $table = 'performance_metrics';

    /**
     * Get user dashboard data
     */
    public function getUserDashboard(int $userId, string $userType, array $filters = []): array
    {
        $period = $filters['period'] ?? 'monthly';
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-t');

        $dashboard = [
            'user_info' => $this->getUserInfo($userId, $userType),
            'key_metrics' => $this->getKeyMetrics($userId, $userType, $startDate, $endDate),
            'widgets' => $this->getDashboardWidgets($userId, $userType),
            'goals' => $this->getUserGoals($userId, $userType),
            'rank_info' => $this->getUserRankInfo($userId, $userType),
            'recent_activity' => $this->getRecentActivity($userId, $userType),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_type' => $period
            ]
        ];

        return $dashboard;
    }

    /**
     * Get key performance metrics
     */
    public function getKeyMetrics(int $userId, string $userType, string $startDate, string $endDate): array
    {
        $metrics = [];

        // Sales volume
        $metrics['sales_volume'] = $this->calculateMetric(
            $userId, $userType, 'sales_volume', $startDate, $endDate
        );

        // Commission earned
        $metrics['commission_earned'] = $this->calculateMetric(
            $userId, $userType, 'commission_earned', $startDate, $endDate
        );

        // Network size
        $metrics['network_size'] = $this->calculateMetric(
            $userId, $userType, 'network_size', $startDate, $endDate
        );

        // Leads generated
        $metrics['leads_generated'] = $this->calculateMetric(
            $userId, $userType, 'leads_generated', $startDate, $endDate
        );

        // Training completion
        $metrics['training_completed'] = $this->calculateMetric(
            $userId, $userType, 'training_completed', $startDate, $endDate
        );

        // Gamification points
        $metrics['gamification_points'] = $this->calculateMetric(
            $userId, $userType, 'gamification_points', $startDate, $endDate
        );

        return $metrics;
    }

    /**
     * Calculate specific metric
     */
    private function calculateMetric(int $userId, string $userType, string $metricType, string $startDate, string $endDate): array
    {
        $query = "";
        $params = [$userId, $userType, $startDate, $endDate];

        switch ($metricType) {
            case 'sales_volume':
                $query = "SELECT COALESCE(SUM(sale_price), 0) as value FROM sales_data WHERE created_at BETWEEN ? AND ?";
                $params = [$startDate, $endDate];
                break;
            case 'commission_earned':
                $query = "SELECT COALESCE(SUM(amount), 0) as value FROM commissions WHERE user_id = ? AND created_at BETWEEN ? AND ?";
                break;
            case 'network_size':
                $query = "SELECT COUNT(*) as value FROM user_network WHERE parent_id = ? AND joined_at <= ?";
                $params = [$userId, $endDate];
                break;
            case 'leads_generated':
                $query = "SELECT COUNT(*) as value FROM leads WHERE created_by = ? AND created_at BETWEEN ? AND ?";
                break;
            case 'training_completed':
                $query = "SELECT COUNT(*) as value FROM user_course_enrollments WHERE user_id = ? AND user_type = ? AND status = 'completed' AND completion_date BETWEEN ? AND ?";
                break;
            case 'gamification_points':
                $query = "SELECT COALESCE(SUM(points_amount), 0) as value FROM points_transactions WHERE user_id = ? AND user_type = ? AND transaction_type = 'earned' AND created_at BETWEEN ? AND ?";
                break;
        }

        if (!empty($query)) {
            $result = $this->query($query, $params)->fetch();
            $currentValue = (float)($result['value'] ?? 0);

            // Calculate previous period for comparison
            $previousValue = $this->getPreviousPeriodValue($userId, $userType, $metricType, $startDate, $endDate);

            $growth = $previousValue > 0 ? (($currentValue - $previousValue) / $previousValue) * 100 : 0;

            return [
                'current_value' => $currentValue,
                'previous_value' => $previousValue,
                'growth_percentage' => round($growth, 2),
                'trend' => $growth > 0 ? 'up' : ($growth < 0 ? 'down' : 'stable')
            ];
        }

        return [
            'current_value' => 0,
            'previous_value' => 0,
            'growth_percentage' => 0,
            'trend' => 'stable'
        ];
    }

    /**
     * Get dashboard widgets
     */
    public function getDashboardWidgets(int $userId, string $userType): array
    {
        // Get user's custom widget configuration or default widgets
        $userConfig = $this->query(
            "SELECT widgets_configuration FROM user_dashboard_configs
             WHERE user_id = ? AND user_type = ? AND is_active = 1
             ORDER BY is_default DESC LIMIT 1",
            [$userId, $userType]
        )->fetch();

        if ($userConfig) {
            return json_decode($userConfig['widgets_configuration'], true);
        }

        // Return default widgets
        $defaultWidgets = $this->query(
            "SELECT * FROM dashboard_widgets WHERE is_default = 1 AND is_active = 1 ORDER BY sort_order ASC"
        )->fetchAll();

        $widgets = [];
        foreach ($defaultWidgets as $widget) {
            $widgets[] = [
                'id' => $widget['id'],
                'name' => $widget['widget_name'],
                'type' => $widget['widget_type'],
                'category' => $widget['widget_category'],
                'size' => $widget['default_size'],
                'configuration' => json_decode($widget['configuration'], true),
                'data' => $this->getWidgetData($widget, $userId, $userType)
            ];
        }

        return $widgets;
    }

    /**
     * Get widget data
     */
    private function getWidgetData(array $widget, int $userId, string $userType): array
    {
        $config = json_decode($widget['configuration'], true);

        switch ($widget['widget_type']) {
            case 'metric_card':
                return $this->getMetricCardData($config, $userId, $userType);
            case 'chart':
                return $this->getChartData($config, $userId, $userType);
            case 'progress_bar':
                return $this->getProgressBarData($config, $userId, $userType);
            case 'leaderboard':
                return $this->getLeaderboardData($config);
            default:
                return [];
        }
    }

    /**
     * Get metric card data
     */
    private function getMetricCardData(array $config, int $userId, string $userType): array
    {
        $metric = $config['metric'];
        $period = $config['period'] ?? 'monthly';

        $startDate = $this->getPeriodStartDate($period);
        $endDate = date('Y-m-d');

        $metricData = $this->calculateMetric($userId, $userType, $metric, $startDate, $endDate);

        return [
            'value' => $metricData['current_value'],
            'previous_value' => $metricData['previous_value'],
            'growth' => $metricData['growth_percentage'],
            'trend' => $metricData['trend'],
            'target' => $config['target'] ?? null
        ];
    }

    /**
     * Get chart data
     */
    private function getChartData(array $config, int $userId, string $userType): array
    {
        $metrics = $config['metrics'] ?? [];
        $period = $config['period'] ?? '6months';
        $chartType = $config['chart_type'] ?? 'line';

        $data = [];
        $labels = [];

        // Generate data points for the period
        $endDate = new DateTime();
        $startDate = new DateTime();

        switch ($period) {
            case '7days':
                $startDate->modify('-6 days');
                break;
            case '30days':
                $startDate->modify('-29 days');
                break;
            case '3months':
                $startDate->modify('-2 months');
                break;
            case '6months':
                $startDate->modify('-5 months');
                break;
            case '1year':
                $startDate->modify('-11 months');
                break;
        }

        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');

            $dataPoint = ['date' => $dateStr];
            foreach ($metrics as $metric) {
                $metricValue = $this->calculateMetric(
                    $userId, $userType, $metric, $dateStr, $dateStr
                );
                $dataPoint[$metric] = $metricValue['current_value'];
            }
            $data[] = $dataPoint;

            $currentDate->modify('+1 month');
        }

        return [
            'chart_type' => $chartType,
            'labels' => $labels,
            'datasets' => $data,
            'metrics' => $metrics
        ];
    }

    /**
     * Get progress bar data
     */
    private function getProgressBarData(array $config, int $userId, string $userType): array
    {
        $goals = $this->getUserGoals($userId, $userType);

        $progressData = [];
        foreach ($goals as $goal) {
            $progressData[] = [
                'goal_name' => $goal['goal_name'],
                'current_value' => $goal['current_value'],
                'target_value' => $goal['target_value'],
                'progress_percentage' => $goal['progress_percentage'],
                'status' => $goal['status']
            ];
        }

        return $progressData;
    }

    /**
     * Get leaderboard data
     */
    private function getLeaderboardData(array $config): array
    {
        $metric = $config['metric'] ?? 'sales_volume';
        $period = $config['period'] ?? 'monthly';
        $limit = $config['limit'] ?? 10;

        $startDate = $this->getPeriodStartDate($period);
        $endDate = date('Y-m-d');

        $leaderboard = $this->query(
            "SELECT u.auser as user_name, SUM(pm.metric_value) as total_value,
                    ROW_NUMBER() OVER (ORDER BY SUM(pm.metric_value) DESC) as rank
             FROM performance_metrics pm
             LEFT JOIN admin u ON pm.user_id = u.aid AND pm.user_type = 'associate'
             WHERE pm.metric_type = ? AND pm.metric_date BETWEEN ? AND ?
             GROUP BY pm.user_id, pm.user_type, u.auser
             ORDER BY total_value DESC
             LIMIT ?",
            [$metric, $startDate, $endDate, $limit]
        )->fetchAll();

        return $leaderboard;
    }

    /**
     * Get user goals
     */
    public function getUserGoals(int $userId, string $userType): array
    {
        $goals = $this->query(
            "SELECT * FROM performance_goals
             WHERE user_id = ? AND user_type = ?
             ORDER BY created_at DESC",
            [$userId, $userType]
        )->fetchAll();

        foreach ($goals as &$goal) {
            // Calculate current progress if auto-calculate is enabled
            if ($goal['auto_calculate']) {
                $goal['current_value'] = $this->calculateGoalProgress($goal);
                $goal['progress_percentage'] = $goal['target_value'] > 0
                    ? min(100, round(($goal['current_value'] / $goal['target_value']) * 100, 2))
                    : 0;

                // Update goal status
                if ($goal['progress_percentage'] >= 100 && $goal['status'] === 'active') {
                    $this->completeGoal($goal['id']);
                }
            }
        }

        return $goals;
    }

    /**
     * Create performance goal
     */
    public function createGoal(array $goalData): array
    {
        $goalRecord = [
            'user_id' => $goalData['user_id'],
            'user_type' => $goalData['user_type'] ?? 'associate',
            'goal_name' => $goalData['goal_name'],
            'goal_type' => $goalData['goal_type'],
            'target_value' => $goalData['target_value'],
            'start_date' => $goalData['start_date'],
            'end_date' => $goalData['end_date'],
            'reward_points' => $goalData['reward_points'] ?? 0,
            'reward_badge_id' => $goalData['reward_badge_id'] ?? null,
            'created_by' => $goalData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $goalId = $this->insertInto('performance_goals', $goalRecord);

        return [
            'success' => true,
            'goal_id' => $goalId,
            'message' => 'Performance goal created successfully'
        ];
    }

    /**
     * Generate performance report
     */
    public function generatePerformanceReport(int $userId, string $userType, array $reportConfig): array
    {
        $startDate = $reportConfig['start_date'];
        $endDate = $reportConfig['end_date'];
        $metrics = $reportConfig['metrics'] ?? ['sales_volume', 'commission_earned', 'network_size'];

        $reportData = [
            'user_info' => $this->getUserInfo($userId, $userType),
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'metrics' => [],
            'goals' => $this->getUserGoals($userId, $userType),
            'rankings' => [],
            'recommendations' => $this->generatePerformanceRecommendations($userId, $userType)
        ];

        // Calculate metrics
        foreach ($metrics as $metric) {
            $reportData['metrics'][$metric] = $this->calculateMetric($userId, $userType, $metric, $startDate, $endDate);
        }

        // Calculate rankings
        foreach ($metrics as $metric) {
            $reportData['rankings'][$metric] = $this->getUserRanking($userId, $userType, $metric, $startDate, $endDate);
        }

        // Save report
        $reportId = $this->saveReport($userId, $userType, $reportConfig, $reportData);

        $reportData['report_id'] = $reportId;

        return $reportData;
    }

    /**
     * Update performance metrics
     */
    public function updatePerformanceMetrics(int $userId, string $userType, array $metrics): void
    {
        $metricDate = date('Y-m-d');

        foreach ($metrics as $metricType => $value) {
            // Calculate comparison with previous period
            $previousValue = $this->getPreviousPeriodValue($userId, $userType, $metricType, $metricDate, $metricDate);
            $growth = $previousValue > 0 ? (($value - $previousValue) / $previousValue) * 100 : 0;

            $metricRecord = [
                'user_id' => $userId,
                'user_type' => $userType,
                'metric_type' => $metricType,
                'metric_value' => $value,
                'metric_date' => $metricDate,
                'comparison_value' => $previousValue,
                'growth_percentage' => round($growth, 4),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->insertInto('performance_metrics', $metricRecord);
        }
    }

    // Helper methods

    private function getUserInfo(int $userId, string $userType): array
    {
        // This would integrate with your user tables
        return [
            'id' => $userId,
            'type' => $userType,
            'name' => 'Sample User', // Would fetch from actual user table
            'join_date' => '2024-01-01'
        ];
    }

    private function getPeriodStartDate(string $period): string
    {
        switch ($period) {
            case 'daily':
                return date('Y-m-d');
            case 'weekly':
                return date('Y-m-d', strtotime('monday this week'));
            case 'monthly':
                return date('Y-m-01');
            case 'quarterly':
                $quarter = ceil(date('n') / 3);
                return date('Y') . '-' . sprintf('%02d', ($quarter - 1) * 3 + 1) . '-01';
            case 'yearly':
                return date('Y-01-01');
            default:
                return date('Y-m-01');
        }
    }

    private function getPreviousPeriodValue(int $userId, string $userType, string $metricType, string $startDate, string $endDate): float
    {
        // Calculate previous period dates
        $periodLength = strtotime($endDate) - strtotime($startDate);
        $prevEndDate = date('Y-m-d', strtotime($startDate) - 1);
        $prevStartDate = date('Y-m-d', strtotime($prevEndDate) - $periodLength);

        $result = $this->query(
            "SELECT AVG(metric_value) as avg_value FROM performance_metrics
             WHERE user_id = ? AND user_type = ? AND metric_type = ? AND metric_date BETWEEN ? AND ?",
            [$userId, $userType, $metricType, $prevStartDate, $prevEndDate]
        )->fetch();

        return (float)($result['avg_value'] ?? 0);
    }

    private function getUserRankInfo(int $userId, string $userType): array
    {
        // Get current rank and achievements
        $rank = $this->query(
            "SELECT * FROM rank_achievements
             WHERE user_id = ? AND user_type = ? AND is_current_rank = 1
             ORDER BY achieved_at DESC LIMIT 1",
            [$userId, $userType]
        )->fetch();

        return $rank ?: ['rank_name' => 'Associate', 'rank_level' => 1];
    }

    private function getRecentActivity(int $userId, string $userType): array
    {
        // Get recent performance activities
        return $this->query(
            "SELECT * FROM performance_metrics
             WHERE user_id = ? AND user_type = ?
             ORDER BY created_at DESC LIMIT 10",
            [$userId, $userType]
        )->fetchAll();
    }

    private function calculateGoalProgress(array $goal): float
    {
        // This would implement specific calculation logic based on goal type
        switch ($goal['goal_type']) {
            case 'sales_target':
                // Calculate sales in goal period
                return 0; // Placeholder
            case 'recruitment_target':
                // Calculate recruits in goal period
                return 0; // Placeholder
            default:
                return $goal['current_value'];
        }
    }

    private function completeGoal(int $goalId): void
    {
        $this->query(
            "UPDATE performance_goals SET status = 'completed', updated_at = NOW() WHERE id = ?",
            [$goalId]
        );

        // Award rewards would be implemented here
    }

    private function getUserRanking(int $userId, string $userType, string $metric, string $startDate, string $endDate): array
    {
        // Calculate user's ranking for specific metric
        $userTotal = $this->query(
            "SELECT SUM(metric_value) as total FROM performance_metrics
             WHERE metric_type = ? AND metric_date BETWEEN ? AND ? AND user_id = ? AND user_type = ?",
            [$metric, $startDate, $endDate, $userId, $userType]
        )->fetch()['total'] ?? 0;

        $rank = $this->query(
            "SELECT COUNT(*) + 1 as rank FROM (
                SELECT user_id, SUM(metric_value) as total
                FROM performance_metrics
                WHERE metric_type = ? AND metric_date BETWEEN ? AND ?
                GROUP BY user_id, user_type
                HAVING total > ?
            ) as rankings",
            [$metric, $startDate, $endDate, $userTotal]
        )->fetch()['rank'] ?? 0;

        return [
            'rank' => $rank,
            'total_participants' => $this->query(
                "SELECT COUNT(DISTINCT user_id) as count FROM performance_metrics
                 WHERE metric_type = ? AND metric_date BETWEEN ? AND ?",
                [$metric, $startDate, $endDate]
            )->fetch()['count'] ?? 0
        ];
    }

    private function generatePerformanceRecommendations(int $userId, string $userType): array
    {
        $recommendations = [];

        // Analyze performance and generate recommendations
        $metrics = $this->getKeyMetrics($userId, $userType, date('Y-m-01'), date('Y-m-t'));

        if ($metrics['sales_volume']['growth_percentage'] < 0) {
            $recommendations[] = [
                'type' => 'sales_improvement',
                'title' => 'Sales Performance Needs Attention',
                'description' => 'Your sales have declined. Consider reviewing your sales strategy.',
                'priority' => 'high'
            ];
        }

        if ($metrics['network_size']['current_value'] < 10) {
            $recommendations[] = [
                'type' => 'network_growth',
                'title' => 'Expand Your Network',
                'description' => 'Focus on recruiting new associates to grow your network.',
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }

    private function saveReport(int $userId, string $userType, array $config, array $data): int
    {
        $reportRecord = [
            'report_name' => $config['report_name'] ?? 'Performance Report',
            'report_type' => 'individual',
            'date_range' => json_encode(['start_date' => $config['start_date'], 'end_date' => $config['end_date']]),
            'filters' => json_encode($config['filters'] ?? []),
            'metrics_included' => json_encode($config['metrics'] ?? []),
            'generated_data' => json_encode($data),
            'generated_by' => $userId,
            'generated_for' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insertInto('performance_reports', $reportRecord);
    }
}
