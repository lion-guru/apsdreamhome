<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class AnalyticsController extends BaseController
{
    public function index()
    {
        // Temporarily disable login for testing
        // $this->requireLogin();
        
        $metrics = $this->getMetrics();
        $chartData = $this->getChartData();
        $performanceData = $this->getPerformanceData();
        $recentActivity = $this->getRecentActivity();
        $userEngagement = $this->getUserEngagement();
        
        $this->render('pages/analytics-dashboard', [
            'page_title' => 'Analytics Dashboard - APS Dream Home',
            'page_description' => 'Comprehensive analytics and performance monitoring',
            'metrics' => $metrics,
            'chart_data' => $chartData,
            'performance_data' => $performanceData,
            'recent_activity' => $recentActivity,
            'user_engagement' => $userEngagement
        ]);
    }
    
    /**
     * Get key metrics
     */
    private function getMetrics()
    {
        return [
            'total_users' => 2847,
            'active_properties' => 59,
            'bookings_month' => 147,
            'revenue' => 2400000,
            'user_growth' => 12.5,
            'property_growth' => 8.3,
            'booking_change' => -2.1,
            'revenue_growth' => 18.7
        ];
    }
    
    /**
     * Get chart data
     */
    private function getChartData()
    {
        return [
            'trend' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'property_views' => [1200, 1900, 3000, 5000, 4000, 6000],
                'bookings' => [100, 150, 200, 350, 280, 420]
            ],
            'property_types' => [
                'labels' => ['Apartments', 'Villas', 'Commercial', 'Land'],
                'data' => [35, 25, 20, 20]
            ],
            'revenue' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [1800000, 2200000, 2800000, 3500000, 4200000, 4800000]
            ]
        ];
    }
    
    /**
     * Get performance data
     */
    private function getPerformanceData()
    {
        return [
            'server_uptime' => 90,
            'memory_usage' => 70,
            'cpu_usage' => 50,
            'database_performance' => 85,
            'api_response_time' => 95,
            'error_rate' => 5
        ];
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        return [
            [
                'type' => 'whatsapp_campaign',
                'description' => 'Bulk WhatsApp campaign sent to 500+ users for new property listings',
                'timestamp' => '10 minutes ago',
                'icon' => 'whatsapp',
                'color' => 'whatsapp'
            ],
            [
                'type' => 'email_newsletter',
                'description' => 'Monthly newsletter with property updates sent to 2,847 subscribers',
                'timestamp' => '2 hours ago',
                'icon' => 'envelope',
                'color' => 'email'
            ],
            [
                'type' => 'ai_training',
                'description' => 'AI agent completed training on 15 new property listings',
                'timestamp' => '4 hours ago',
                'icon' => 'robot',
                'color' => 'ai'
            ],
            [
                'type' => 'system_update',
                'description' => 'Database optimization completed - 23% performance improvement',
                'timestamp' => '6 hours ago',
                'icon' => 'cog',
                'color' => 'system'
            ]
        ];
    }
    
    /**
     * Get user engagement data
     */
    private function getUserEngagement()
    {
        return [
            'daily_active_users' => 342,
            'page_views' => 8947,
            'avg_session_time' => '4m 32s',
            'bounce_rate' => 32.4,
            'conversion_rate' => 5.8
        ];
    }
    
    public function getRevenueAnalytics()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "total_revenue" => 1500000,
                "monthly_revenue" => 125000,
                "growth_rate" => 15.5,
                "revenue_by_property_type" => [
                    "apartments" => 600000,
                    "houses" => 500000,
                    "villas" => 400000
                ]
            ]
        ]);
    }
    
    public function getTrafficAnalytics()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "total_visitors" => 50000,
                "unique_visitors" => 35000,
                "page_views" => 150000,
                "bounce_rate" => 35.2,
                "avg_session_duration" => 245
            ]
        ]);
    }
    
    public function getConversionAnalytics()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "total_conversions" => 250,
                "conversion_rate" => 3.5,
                "conversions_by_source" => [
                    "organic" => 120,
                    "paid" => 80,
                    "social" => 30,
                    "referral" => 20
                ]
            ]
        ]);
    }
}
?>