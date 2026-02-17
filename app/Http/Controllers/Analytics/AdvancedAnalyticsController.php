<?php

/**
 * Advanced Analytics Controller
 * Provides comprehensive business intelligence and reporting
 */

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Admin\AdminController;

class AdvancedAnalyticsController extends AdminController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Main analytics dashboard
     */
    public function dashboard()
    {
        // Get comprehensive analytics data
        $analytics_data = $this->getComprehensiveAnalytics();

        $this->data['page_title'] = $this->mlSupport->translate('Advanced Analytics') . ' - ' . APP_NAME;
        $this->data['analytics_data'] = $analytics_data;
        $this->data['date_range'] = [
            'start' => $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end' => $_GET['end_date'] ?? date('Y-m-d')
        ];

        $this->render('admin/advanced_analytics');
    }

    /**
     * Property performance analytics
     */
    public function propertyAnalytics()
    {
        $property_data = $this->getPropertyAnalytics();

        $this->data['page_title'] = $this->mlSupport->translate('Property Analytics') . ' - ' . APP_NAME;
        $this->data['property_data'] = $property_data;

        $this->render('admin/property_analytics');
    }

    /**
     * User behavior analytics
     */
    public function userAnalytics()
    {
        $user_data = $this->getUserAnalytics();

        $this->data['page_title'] = $this->mlSupport->translate('User Analytics') . ' - ' . APP_NAME;
        $this->data['user_data'] = $user_data;

        $this->render('admin/user_analytics');
    }

    /**
     * Financial analytics
     */
    public function financialAnalytics()
    {
        $financial_data = $this->getFinancialAnalytics();

        $this->data['page_title'] = $this->mlSupport->translate('Financial Analytics') . ' - ' . APP_NAME;
        $this->data['financial_data'] = $financial_data;

        $this->render('admin/financial_analytics');
    }

    /**
     * MLM network analytics
     */
    public function mlmAnalytics()
    {
        $mlm_data = $this->getMLMAnalytics();

        $this->data['page_title'] = $this->mlSupport->translate('MLM Analytics') . ' - ' . APP_NAME;
        $this->data['mlm_data'] = $mlm_data;

        $this->render('admin/mlm_analytics');
    }

    /**
     * API - Get real-time analytics data
     */
    public function apiGetRealtimeData()
    {
        header('Content-Type: application/json');

        // AdminController handles auth check

        $realtime_data = $this->getRealtimeAnalytics();

        sendJsonResponse([
            'success' => true,
            'data' => $realtime_data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get comprehensive analytics data
     */
    private function getComprehensiveAnalytics()
    {
        try {
            $data = [];

            // Overview metrics
            $data['overview'] = [
                'total_properties' => $this->getTotalProperties(),
                'active_properties' => $this->getActiveProperties(),
                'total_users' => $this->getTotalUsers(),
                'active_users' => $this->getActiveUsers(),
                'total_inquiries' => $this->getTotalInquiries(),
                'total_revenue' => $this->getTotalRevenue(),
                'conversion_rate' => $this->getConversionRate(),
                'avg_property_price' => $this->getAveragePropertyPrice()
            ];

            // Growth trends (last 12 months)
            $data['growth_trends'] = $this->getGrowthTrends();

            // Top performing locations
            $data['top_locations'] = $this->getTopPerformingLocations();

            // User engagement metrics
            $data['user_engagement'] = $this->getUserEngagementMetrics();

            // Property performance
            $data['property_performance'] = $this->getPropertyPerformanceMetrics();

            return $data;
        } catch (\Exception $e) {
            error_log('Comprehensive analytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property-specific analytics
     */
    private function getPropertyAnalytics()
    {
        try {
            $data = [];

            // Property distribution by type
            $sql = "SELECT pt.name as type, COUNT(p.id) as count, AVG(p.price) as avg_price
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE p.status = 'available'
                    GROUP BY p.property_type_id, pt.name
                    ORDER BY count DESC";

            $stmt = $this->db->query($sql);
            $data['type_distribution'] = $stmt->fetchAll();

            // Price distribution
            $data['price_distribution'] = $this->getPriceDistribution();

            // Location performance
            $data['location_performance'] = $this->getLocationPerformance();

            // Property age analysis
            $data['property_age'] = $this->getPropertyAgeAnalysis();

            return $data;
        } catch (\Exception $e) {
            error_log('Property analytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user behavior analytics
     */
    private function getUserAnalytics()
    {
        try {
            $data = [];

            // User registration trends
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                    FROM users
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE(created_at)
                    ORDER BY date";

            $stmt = $this->db->query($sql);
            $data['registration_trends'] = $stmt->fetchAll();

            // User activity analysis
            $data['activity_analysis'] = $this->getUserActivityAnalysis();

            // Geographic distribution
            $data['geographic_distribution'] = $this->getGeographicDistribution();

            // User retention metrics
            $data['retention_metrics'] = $this->getUserRetentionMetrics();

            return $data;
        } catch (\Exception $e) {
            error_log('User analytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get financial analytics
     */
    private function getFinancialAnalytics()
    {
        try {
            $data = [];

            // Revenue trends
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                           SUM(amount) as revenue
                    FROM transactions
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month";

            $stmt = $this->db->query($sql);
            $data['revenue_trends'] = $stmt->fetchAll();

            // Commission payouts
            $data['commission_analysis'] = $this->getCommissionAnalysis();

            // Payment method analysis
            $data['payment_methods'] = $this->getPaymentMethodAnalysis();

            // Profit margins
            $data['profit_margins'] = $this->getProfitMarginAnalysis();

            return $data;
        } catch (\Exception $e) {
            error_log('Financial analytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get MLM network analytics
     */
    private function getMLMAnalytics()
    {
        try {
            $associateMLM = new \App\Models\AssociateMLM();
            $chatbot = new \App\Models\AIChatbot();
            $mlmAnalytics = new \App\Models\MLMAdvancedAnalytics();

            $data = [];

            // MLM network overview
            $data['network_overview'] = $this->getMLMNetworkOverview();

            // Level distribution
            $data['level_distribution'] = $this->getMLMLevelDistribution();

            // Commission trends
            $data['commission_trends'] = $this->getMLMCommissionTrends();

            // Advanced Analytics
            // Pass null for system-wide analytics, or get logged in user's ID if this is for a specific user.
            // Assuming this controller method is for Admin dashboard overview (based on method name mlmAnalytics and isAdmin check in public method),
            // so we want system-wide analytics or top-level analytics.
            $data['advanced_analytics'] = $mlmAnalytics->generateMLMAnalytics(null, 'monthly');

            // Top performers
            $data['top_performers'] = $associateMLM->getTopPerformers(20);

            // Chatbot analytics
            $data['chatbot_stats'] = $chatbot->getChatbotStats();

            return $data;
        } catch (\Exception $e) {
            error_log('MLM analytics error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get real-time analytics data
     */
    private function getRealtimeAnalytics()
    {
        try {
            $data = [];

            // Current active users (last 5 minutes)
            $sql = "SELECT COUNT(DISTINCT user_id) as active_users
                    FROM user_activity
                    WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";

            $stmt = $this->db->query($sql);
            $data['active_users'] = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['active_users'];

            // Properties viewed today
            $sql = "SELECT COUNT(*) as views_today FROM property_views WHERE DATE(view_date) = CURDATE()";
            $stmt = $this->db->query($sql);
            $data['property_views_today'] = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['views_today'];

            // Inquiries today
            $sql = "SELECT COUNT(*) as inquiries_today FROM property_inquiries WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->query($sql);
            $data['inquiries_today'] = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['inquiries_today'];

            // Revenue today
            $sql = "SELECT SUM(amount) as revenue_today FROM transactions WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->query($sql);
            $data['revenue_today'] = (float)($stmt->fetch(\PDO::FETCH_ASSOC)['revenue_today'] ?? 0);

            // Chatbot conversations today
            $sql = "SELECT COUNT(*) as chatbot_today FROM chatbot_conversations WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->query($sql);
            $data['chatbot_conversations_today'] = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['chatbot_today'];

            return $data;
        } catch (\Exception $e) {
            error_log('Realtime analytics error: ' . $e->getMessage());
            return [];
        }
    }

    // Helper methods for data collection
    private function getTotalProperties()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM properties");
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveProperties()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as active FROM properties WHERE status = 'available'");
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['active'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalUsers()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getActiveUsers()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['active'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalInquiries()
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM property_inquiries");
            return (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalRevenue()
    {
        try {
            $stmt = $this->db->query("SELECT SUM(amount) as total FROM transactions");
            return (float)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getConversionRate()
    {
        try {
            // Calculate conversion rate (inquiries to sales)
            $stmt = $this->db->query("SELECT COUNT(*) as total_inquiries FROM property_inquiries");
            $total_inquiries = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total_inquiries'];

            $stmt = $this->db->query("SELECT COUNT(*) as total_sales FROM transactions WHERE status = 'completed'");
            $total_sales = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total_sales'];

            return $total_inquiries > 0 ? round(($total_sales / $total_inquiries) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getAveragePropertyPrice()
    {
        try {
            $stmt = $this->db->query("SELECT AVG(price) as avg_price FROM properties WHERE status = 'available'");
            return (float)($stmt->fetch(\PDO::FETCH_ASSOC)['avg_price'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getGrowthTrends()
    {
        // Placeholder - would implement actual growth trend calculation
        return [
            ['month' => '2024-01', 'properties' => 150, 'users' => 300, 'revenue' => 1500000],
            ['month' => '2024-02', 'properties' => 175, 'users' => 350, 'revenue' => 1750000],
            ['month' => '2024-03', 'properties' => 200, 'users' => 400, 'revenue' => 2000000]
        ];
    }

    private function getTopPerformingLocations()
    {
        try {
            $sql = "SELECT city, state, COUNT(*) as property_count, AVG(price) as avg_price
                    FROM properties
                    WHERE status = 'available'
                    GROUP BY city, state
                    ORDER BY property_count DESC
                    LIMIT 10";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserEngagementMetrics()
    {
        // Placeholder - would implement actual engagement metrics
        return [
            'avg_session_duration' => '4.5 minutes',
            'pages_per_session' => 3.2,
            'bounce_rate' => '32%',
            'return_visitor_rate' => '28%'
        ];
    }

    private function getPropertyPerformanceMetrics()
    {
        // Placeholder - would implement actual property performance metrics
        return [
            'avg_days_on_market' => 45,
            'price_reduction_rate' => '15%',
            'view_to_inquiry_ratio' => '12%',
            'inquiry_to_sale_ratio' => '8%'
        ];
    }

    private function getPriceDistribution()
    {
        try {
            $sql = "SELECT
                        CASE
                            WHEN price < 1000000 THEN 'Under ₹10L'
                            WHEN price < 5000000 THEN '₹10L - ₹50L'
                            WHEN price < 10000000 THEN '₹50L - ₹1Cr'
                            WHEN price < 50000000 THEN '₹1Cr - ₹5Cr'
                            ELSE 'Above ₹5Cr'
                        END as price_range,
                        COUNT(*) as count
                    FROM properties
                    WHERE status = 'available'
                    GROUP BY price_range
                    ORDER BY MIN(price)";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getLocationPerformance()
    {
        try {
            $sql = "SELECT city, state,
                           COUNT(*) as properties,
                           AVG(price) as avg_price,
                           COUNT(DISTINCT created_by) as agents
                    FROM properties
                    WHERE status = 'available'
                    GROUP BY city, state
                    ORDER BY properties DESC
                    LIMIT 15";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getPropertyAgeAnalysis()
    {
        try {
            $sql = "SELECT
                        CASE
                            WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 'Last 30 days'
                            WHEN created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 'Last 3 months'
                            WHEN created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) THEN 'Last 6 months'
                            WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 'Last year'
                            ELSE 'Older than 1 year'
                        END as age_group,
                        COUNT(*) as count
                    FROM properties
                    WHERE status = 'available'
                    GROUP BY age_group
                    ORDER BY MIN(created_at)";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserActivityAnalysis()
    {
        // Placeholder - would implement actual user activity analysis
        return [
            'daily_active_users' => 150,
            'weekly_active_users' => 450,
            'monthly_active_users' => 1200,
            'user_retention_7day' => '65%',
            'user_retention_30day' => '35%'
        ];
    }

    private function getGeographicDistribution()
    {
        try {
            $sql = "SELECT state, COUNT(*) as user_count
                    FROM users
                    GROUP BY state
                    ORDER BY user_count DESC
                    LIMIT 10";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUserRetentionMetrics()
    {
        // Placeholder - would implement actual retention metrics
        return [
            'day_1_retention' => '85%',
            'day_7_retention' => '65%',
            'day_30_retention' => '35%',
            'avg_lifetime_value' => '₹25,000'
        ];
    }

    private function getCommissionAnalysis()
    {
        // Placeholder - would implement actual commission analysis
        return [
            'total_commission_paid' => 250000,
            'avg_commission_per_sale' => 15000,
            'top_earning_associate' => '₹75,000',
            'commission_growth_rate' => '25%'
        ];
    }

    private function getPaymentMethodAnalysis()
    {
        // Placeholder - would implement actual payment method analysis
        return [
            ['method' => 'Credit Card', 'count' => 450, 'amount' => 2250000],
            ['method' => 'UPI', 'count' => 320, 'amount' => 1600000],
            ['method' => 'Net Banking', 'count' => 180, 'amount' => 900000]
        ];
    }

    private function getProfitMarginAnalysis()
    {
        // Placeholder - would implement actual profit margin analysis
        return [
            'avg_margin' => '12%',
            'highest_margin' => '25%',
            'lowest_margin' => '5%',
            'margin_trend' => 'increasing'
        ];
    }

    private function getMLMNetworkOverview()
    {
        try {
            $sql = "SELECT
                        COUNT(*) as total_associates,
                        SUM(total_commission) as total_earnings,
                        AVG(level) as avg_level,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_associates
                    FROM associate_mlm";

            $stmt = $this->db->query($sql);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMLMLevelDistribution()
    {
        try {
            $sql = "SELECT level, COUNT(*) as count
                    FROM associate_mlm
                    GROUP BY level
                    ORDER BY level";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getMLMCommissionTrends()
    {
        // Placeholder - would implement actual MLM commission trends
        return [
            ['month' => '2024-01', 'commission' => 45000],
            ['month' => '2024-02', 'commission' => 52000],
            ['month' => '2024-03', 'commission' => 61000]
        ];
    }
}
