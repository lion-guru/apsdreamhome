<?php
/**
 * Admin Reports Controller
 * Handles admin reports and analytics functionality
 */

namespace App\Controllers;

class AdminReportsController extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display admin reports dashboard
     */
    public function index() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'Reports & Analytics - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Reports', 'url' => BASE_URL . 'admin/reports']
        ];

        // Get overview statistics
        $this->data['overview_stats'] = $this->getOverviewStats();

        // Get recent activities
        $this->data['recent_activities'] = $this->getRecentActivities();

        // Get top performing properties
        $this->data['top_properties'] = $this->getTopPerformingProperties();

        // Get user engagement metrics
        $this->data['user_metrics'] = $this->getUserEngagementMetrics();

        // Render the reports page
        $this->render('admin/reports');
    }

    /**
     * Display property performance reports
     */
    public function properties() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'Property Performance Reports - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Reports', 'url' => BASE_URL . 'admin/reports'],
            ['title' => 'Properties', 'url' => BASE_URL . 'admin/reports/properties']
        ];

        // Get filter parameters
        $filters = [
            'period' => $_GET['period'] ?? '30days',
            'property_type' => $_GET['property_type'] ?? 'all',
            'city' => $_GET['city'] ?? 'all',
            'status' => $_GET['status'] ?? 'all',
            'sort' => $_GET['sort'] ?? 'views',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get property performance data
        $this->data['property_stats'] = $this->getPropertyPerformanceStats($filters);
        $this->data['top_performers'] = $this->getTopPerformingProperties($filters);
        $this->data['property_trends'] = $this->getPropertyTrends($filters);
        $this->data['filters'] = $filters;

        // Render the property reports page
        $this->render('admin/reports_properties');
    }

    /**
     * Display user analytics reports
     */
    public function users() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'User Analytics Reports - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Reports', 'url' => BASE_URL . 'admin/reports'],
            ['title' => 'Users', 'url' => BASE_URL . 'admin/reports/users']
        ];

        // Get filter parameters
        $filters = [
            'period' => $_GET['period'] ?? '30days',
            'user_type' => $_GET['user_type'] ?? 'all',
            'registration_source' => $_GET['registration_source'] ?? 'all',
            'activity_level' => $_GET['activity_level'] ?? 'all'
        ];

        // Get user analytics data
        $this->data['user_stats'] = $this->getUserAnalyticsStats($filters);
        $this->data['user_growth'] = $this->getUserGrowthData($filters);
        $this->data['user_activity'] = $this->getUserActivityData($filters);
        $this->data['filters'] = $filters;

        // Render the user analytics page
        $this->render('admin/reports_users');
    }

    /**
     * Display financial reports
     */
    public function financial() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'Financial Reports - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Reports', 'url' => BASE_URL . 'admin/reports'],
            ['title' => 'Financial', 'url' => BASE_URL . 'admin/reports/financial']
        ];

        // Get filter parameters
        $filters = [
            'period' => $_GET['period'] ?? '30days',
            'report_type' => $_GET['report_type'] ?? 'revenue'
        ];

        // Get financial data
        $this->data['revenue_data'] = $this->getRevenueData($filters);
        $this->data['commission_data'] = $this->getCommissionData($filters);
        $this->data['expense_data'] = $this->getExpenseData($filters);
        $this->data['profit_loss'] = $this->getProfitLossData($filters);
        $this->data['filters'] = $filters;

        // Render the financial reports page
        $this->render('admin/reports_financial');
    }

    /**
     * Display inquiry analytics
     */
    public function inquiries() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }

        // Set page data
        $this->data['page_title'] = 'Inquiry Analytics - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => BASE_URL . 'admin'],
            ['title' => 'Reports', 'url' => BASE_URL . 'admin/reports'],
            ['title' => 'Inquiries', 'url' => BASE_URL . 'admin/reports/inquiries']
        ];

        // Get filter parameters
        $filters = [
            'period' => $_GET['period'] ?? '30days',
            'status' => $_GET['status'] ?? 'all',
            'inquiry_type' => $_GET['inquiry_type'] ?? 'all',
            'priority' => $_GET['priority'] ?? 'all'
        ];

        // Get inquiry analytics data
        $this->data['inquiry_stats'] = $this->getInquiryAnalyticsStats($filters);
        $this->data['inquiry_trends'] = $this->getInquiryTrends($filters);
        $this->data['response_times'] = $this->getResponseTimeData($filters);
        $this->data['agent_performance'] = $this->getAgentPerformanceData($filters);
        $this->data['filters'] = $filters;

        // Render the inquiry analytics page
        $this->render('admin/reports_inquiries');
    }

    /**
     * Export report data (AJAX endpoint)
     */
    public function export() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $report_type = $_GET['type'] ?? '';
        $format = $_GET['format'] ?? 'csv';

        if (!$report_type) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Report type is required']);
            return;
        }

        try {
            $data = [];
            switch ($report_type) {
                case 'properties':
                    $data = $this->getPropertyPerformanceStats([]);
                    break;
                case 'users':
                    $data = $this->getUserAnalyticsStats([]);
                    break;
                case 'financial':
                    $data = $this->getRevenueData([]);
                    break;
                case 'inquiries':
                    $data = $this->getInquiryAnalyticsStats([]);
                    break;
                default:
                    throw new \Exception('Invalid report type');
            }

            if ($format === 'csv') {
                $this->exportToCSV($data, $report_type);
            } else {
                echo json_encode(['success' => true, 'data' => $data]);
            }

        } catch (\Exception $e) {
            error_log('Export report error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Export failed']);
        }
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Get overview statistics for dashboard
     */
    private function getOverviewStats() {
        try {
            global $pdo;
            if (!$pdo) {
                return $this->getDefaultOverviewStats();
            }

            $stats = [];

            // Total properties
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties WHERE status = 'available'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_properties'] = (int)($result['total'] ?? 0);

            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_users'] = (int)($result['total'] ?? 0);

            // Total inquiries (last 30 days)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM property_inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_inquiries'] = (int)($result['total'] ?? 0);

            // Total favorites
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM property_favorites");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_favorites'] = (int)($result['total'] ?? 0);

            // Monthly revenue (placeholder - would need actual sales data)
            $stats['monthly_revenue'] = 0;

            // Conversion rate (placeholder)
            $stats['conversion_rate'] = 0;

            return $stats;

        } catch (\Exception $e) {
            error_log('Overview stats query error: ' . $e->getMessage());
            return $this->getDefaultOverviewStats();
        }
    }

    /**
     * Get default overview stats when database is unavailable
     */
    private function getDefaultOverviewStats() {
        return [
            'total_properties' => 0,
            'total_users' => 0,
            'total_inquiries' => 0,
            'total_favorites' => 0,
            'monthly_revenue' => 0,
            'conversion_rate' => 0
        ];
    }

    /**
     * Get recent activities for dashboard
     */
    private function getRecentActivities() {
        return [
            ['type' => 'inquiry', 'action' => 'new', 'title' => 'New property inquiry', 'time' => '2 hours ago', 'user' => 'John Doe'],
            ['type' => 'favorite', 'action' => 'added', 'title' => 'Property favorited', 'time' => '4 hours ago', 'user' => 'Jane Smith'],
            ['type' => 'property', 'action' => 'viewed', 'title' => 'Property viewed', 'time' => '6 hours ago', 'user' => 'Mike Johnson'],
            ['type' => 'user', 'action' => 'registered', 'title' => 'New user registration', 'time' => '8 hours ago', 'user' => 'Sarah Wilson'],
        ];
    }

    /**
     * Get top performing properties
     */
    private function getTopPerformingProperties($filters = []) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            // For now, return sample data - in real implementation, this would analyze views, favorites, inquiries
            return [
                ['id' => 1, 'title' => 'Luxury Villa in City Center', 'views' => 150, 'favorites' => 25, 'inquiries' => 12],
                ['id' => 2, 'title' => 'Modern Apartment Complex', 'views' => 120, 'favorites' => 18, 'inquiries' => 8],
                ['id' => 3, 'title' => 'Spacious Family Home', 'views' => 98, 'favorites' => 15, 'inquiries' => 6],
            ];

        } catch (\Exception $e) {
            error_log('Top properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user engagement metrics
     */
    private function getUserEngagementMetrics() {
        try {
            global $pdo;
            if (!$pdo) {
                return $this->getDefaultUserMetrics();
            }

            $metrics = [];

            // Active users (last 30 days)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $metrics['active_users'] = (int)($result['total'] ?? 0);

            // New registrations (last 30 days)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $metrics['new_registrations'] = (int)($result['total'] ?? 0);

            // Users with favorites
            $stmt = $pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM property_favorites");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $metrics['users_with_favorites'] = (int)($result['total'] ?? 0);

            // Average session duration (placeholder)
            $metrics['avg_session_duration'] = '5m 30s';

            return $metrics;

        } catch (\Exception $e) {
            error_log('User metrics query error: ' . $e->getMessage());
            return $this->getDefaultUserMetrics();
        }
    }

    /**
     * Get default user metrics
     */
    private function getDefaultUserMetrics() {
        return [
            'active_users' => 0,
            'new_registrations' => 0,
            'users_with_favorites' => 0,
            'avg_session_duration' => '0m 0s'
        ];
    }

    /**
     * Get property performance statistics
     */
    private function getPropertyPerformanceStats($filters) {
        // Implementation would include detailed property analytics
        return [
            'total_views' => 0,
            'total_favorites' => 0,
            'total_inquiries' => 0,
            'avg_price' => 0,
            'conversion_rate' => 0
        ];
    }

    /**
     * Get property trends data
     */
    private function getPropertyTrends($filters) {
        // Implementation would include trend analysis over time
        return [
            ['date' => '2024-01-01', 'views' => 100, 'favorites' => 15, 'inquiries' => 8],
            ['date' => '2024-01-02', 'views' => 120, 'favorites' => 18, 'inquiries' => 12],
            ['date' => '2024-01-03', 'views' => 95, 'favorites' => 12, 'inquiries' => 6],
        ];
    }

    /**
     * Get user analytics statistics
     */
    private function getUserAnalyticsStats($filters) {
        // Implementation would include detailed user analytics
        return [
            'total_users' => 0,
            'active_users' => 0,
            'new_users' => 0,
            'user_retention' => 0
        ];
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData($filters) {
        // Implementation would include user growth trends
        return [
            ['month' => 'Jan', 'registrations' => 25, 'active' => 20],
            ['month' => 'Feb', 'registrations' => 30, 'active' => 25],
            ['month' => 'Mar', 'registrations' => 28, 'active' => 22],
        ];
    }

    /**
     * Get user activity data
     */
    private function getUserActivityData($filters) {
        // Implementation would include user activity metrics
        return [
            ['activity' => 'Property Views', 'count' => 1500],
            ['activity' => 'Favorites Added', 'count' => 120],
            ['activity' => 'Inquiries Submitted', 'count' => 85],
            ['activity' => 'Profile Updates', 'count' => 45],
        ];
    }

    /**
     * Get revenue data
     */
    private function getRevenueData($filters) {
        // Implementation would include actual revenue data
        return [
            ['month' => 'Jan', 'revenue' => 50000, 'commission' => 2500],
            ['month' => 'Feb', 'revenue' => 75000, 'commission' => 3750],
            ['month' => 'Mar', 'revenue' => 60000, 'commission' => 3000],
        ];
    }

    /**
     * Get commission data
     */
    private function getCommissionData($filters) {
        // Implementation would include commission tracking
        return [
            ['agent' => 'Agent A', 'properties_sold' => 5, 'commission' => 15000],
            ['agent' => 'Agent B', 'properties_sold' => 3, 'commission' => 9000],
            ['agent' => 'Agent C', 'properties_sold' => 2, 'commission' => 6000],
        ];
    }

    /**
     * Get expense data
     */
    private function getExpenseData($filters) {
        // Implementation would include expense tracking
        return [
            ['category' => 'Marketing', 'amount' => 15000],
            ['category' => 'Operations', 'amount' => 8000],
            ['category' => 'Technology', 'amount' => 12000],
        ];
    }

    /**
     * Get profit/loss data
     */
    private function getProfitLossData($filters) {
        // Implementation would calculate P&L
        return [
            'total_revenue' => 185000,
            'total_expenses' => 35000,
            'net_profit' => 150000,
            'profit_margin' => 81.1
        ];
    }

    /**
     * Get inquiry analytics statistics
     */
    private function getInquiryAnalyticsStats($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return $this->getDefaultInquiryStats();
            }

            $stats = [];

            // Total inquiries
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM property_inquiries");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_inquiries'] = (int)($result['total'] ?? 0);

            // New inquiries (last 30 days)
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM property_inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['new_inquiries'] = (int)($result['total'] ?? 0);

            // Response rate
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM property_inquiries WHERE status IN ('responded', 'closed')");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $responded = (int)($result['total'] ?? 0);

            $stats['response_rate'] = $stats['total_inquiries'] > 0 ? round(($responded / $stats['total_inquiries']) * 100, 1) : 0;

            // Average response time (placeholder)
            $stats['avg_response_time'] = '4.2 hours';

            return $stats;

        } catch (\Exception $e) {
            error_log('Inquiry stats query error: ' . $e->getMessage());
            return $this->getDefaultInquiryStats();
        }
    }

    /**
     * Get default inquiry statistics
     */
    private function getDefaultInquiryStats() {
        return [
            'total_inquiries' => 0,
            'new_inquiries' => 0,
            'response_rate' => 0,
            'avg_response_time' => '0 hours'
        ];
    }

    /**
     * Get inquiry trends
     */
    private function getInquiryTrends($filters) {
        // Implementation would include inquiry trends over time
        return [
            ['date' => '2024-01-01', 'inquiries' => 15, 'responses' => 12],
            ['date' => '2024-01-02', 'inquiries' => 18, 'responses' => 15],
            ['date' => '2024-01-03', 'inquiries' => 12, 'responses' => 10],
        ];
    }

    /**
     * Get response time data
     */
    private function getResponseTimeData($filters) {
        // Implementation would include response time analytics
        return [
            ['range' => '0-2 hours', 'count' => 45],
            ['range' => '2-4 hours', 'count' => 30],
            ['range' => '4-8 hours', 'count' => 15],
            ['range' => '8-24 hours', 'count' => 8],
            ['range' => '24+ hours', 'count' => 2],
        ];
    }

    /**
     * Get agent performance data
     */
    private function getAgentPerformanceData($filters) {
        // Implementation would include agent performance metrics
        return [
            ['agent' => 'Agent A', 'inquiries_assigned' => 25, 'responses' => 23, 'avg_response_time' => '3.5h'],
            ['agent' => 'Agent B', 'inquiries_assigned' => 18, 'responses' => 16, 'avg_response_time' => '4.2h'],
            ['agent' => 'Agent C', 'inquiries_assigned' => 12, 'responses' => 11, 'avg_response_time' => '2.8h'],
        ];
    }

    /**
     * Export data to CSV format
     */
    private function exportToCSV($data, $report_type) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            // Add headers
            fputcsv($output, array_keys($data[0]));

            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit();
    }

    /**
     * Get activity color for timeline
     */
    private function getActivityColor($type) {
        $colors = [
            'inquiry' => 'warning',
            'favorite' => 'danger',
            'property' => 'info',
            'user' => 'success'
        ];
        return $colors[$type] ?? 'secondary';
    }
}

?>
