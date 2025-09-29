<?php
/**
 * CRM Analytics and Reporting System
 * Advanced analytics and reporting for CRM data
 */

class CRMAnalyticsManager {
    private $conn;
    private $logger;
    private $crmManager;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->crmManager = new CRMManager($conn, $logger);
    }

    /**
     * Get comprehensive CRM analytics
     */
    public function getComprehensiveAnalytics($filters = []) {
        $analytics = [];

        // Lead Analytics
        $analytics['lead_analytics'] = $this->getLeadAnalytics($filters);

        // Sales Analytics
        $analytics['sales_analytics'] = $this->getSalesAnalytics($filters);

        // Customer Analytics
        $analytics['customer_analytics'] = $this->getCustomerAnalytics($filters);

        // Campaign Analytics
        $analytics['campaign_analytics'] = $this->getCampaignAnalytics($filters);

        // Performance Analytics
        $analytics['performance_analytics'] = $this->getPerformanceAnalytics($filters);

        // Trend Analysis
        $analytics['trend_analysis'] = $this->getTrendAnalysis($filters);

        return $analytics;
    }

    /**
     * Get lead analytics
     */
    private function getLeadAnalytics($filters = []) {
        $analytics = [];

        // Lead conversion funnel
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN lead_status = 'new' THEN 1 ELSE 0 END) as new_leads,
            SUM(CASE WHEN lead_status = 'contacted' THEN 1 ELSE 0 END) as contacted_leads,
            SUM(CASE WHEN lead_status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
            SUM(CASE WHEN lead_status = 'proposal_sent' THEN 1 ELSE 0 END) as proposal_sent_leads,
            SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as won_leads,
            SUM(CASE WHEN lead_status = 'lost' THEN 1 ELSE 0 END) as lost_leads
            FROM leads";

        if (!empty($filters['date_from'])) {
            $sql .= " WHERE created_at >= '" . $filters['date_from'] . "'";
        }

        $result = $this->conn->query($sql);
        $funnel = $result->fetch_assoc();

        $analytics['conversion_funnel'] = [
            'total_leads' => $funnel['total_leads'],
            'contacted_rate' => $funnel['total_leads'] > 0 ? round(($funnel['contacted_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'qualified_rate' => $funnel['total_leads'] > 0 ? round(($funnel['qualified_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'proposal_rate' => $funnel['total_leads'] > 0 ? round(($funnel['proposal_sent_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'win_rate' => $funnel['total_leads'] > 0 ? round(($funnel['won_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'loss_rate' => $funnel['total_leads'] > 0 ? round(($funnel['lost_leads'] / $funnel['total_leads']) * 100, 2) : 0
        ];

        // Lead sources performance
        $sql = "SELECT ls.source_name,
                       COUNT(l.id) as lead_count,
                       SUM(CASE WHEN l.lead_status = 'won' THEN 1 ELSE 0 END) as converted_count,
                       AVG(l.lead_score) as avg_lead_score,
                       AVG(l.conversion_probability) as avg_conversion_prob
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.id = l.lead_source_id
                GROUP BY ls.id, ls.source_name
                ORDER BY lead_count DESC";

        $result = $this->conn->query($sql);
        $analytics['lead_sources_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['conversion_rate'] = $row['lead_count'] > 0 ? round(($row['converted_count'] / $row['lead_count']) * 100, 2) : 0;
            $analytics['lead_sources_performance'][] = $row;
        }

        // Lead quality metrics
        $sql = "SELECT
            AVG(lead_score) as avg_lead_score,
            MIN(lead_score) as min_lead_score,
            MAX(lead_score) as max_lead_score,
            AVG(conversion_probability) as avg_conversion_probability
            FROM leads";

        $result = $this->conn->query($sql);
        $qualityMetrics = $result->fetch_assoc();
        $analytics['lead_quality_metrics'] = $qualityMetrics;

        // Lead velocity (time to conversion)
        $sql = "SELECT
            AVG(DATEDIFF(l.updated_at, l.created_at)) as avg_time_to_conversion
            FROM leads l
            WHERE l.lead_status = 'won'";

        $result = $this->conn->query($sql);
        $analytics['lead_velocity'] = $result->fetch_assoc();

        return $analytics;
    }

    /**
     * Get sales analytics
     */
    private function getSalesAnalytics($filters = []) {
        $analytics = [];

        // Sales pipeline analysis
        $sql = "SELECT s.stage_name,
                       COUNT(o.id) as opportunity_count,
                       SUM(o.expected_value) as total_value,
                       SUM(o.expected_value * o.probability_percentage / 100) as weighted_value,
                       AVG(o.probability_percentage) as avg_probability,
                       AVG(DATEDIFF(NOW(), o.created_at)) as avg_days_in_stage
                FROM sales_pipeline_stages s
                LEFT JOIN opportunities o ON s.id = o.pipeline_stage_id
                GROUP BY s.id, s.stage_name
                ORDER BY s.stage_order";

        $result = $this->conn->query($sql);
        $analytics['pipeline_analysis'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['pipeline_analysis'][] = $row;
        }

        // Sales performance by user
        $sql = "SELECT u.full_name as user_name,
                       COUNT(o.id) as opportunity_count,
                       SUM(CASE WHEN o.pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_count,
                       SUM(o.expected_value) as total_value,
                       AVG(o.probability_percentage) as avg_probability
                FROM users u
                LEFT JOIN opportunities o ON u.id = o.assigned_to
                GROUP BY u.id, u.full_name
                ORDER BY won_count DESC";

        $result = $this->conn->query($sql);
        $analytics['sales_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['win_rate'] = $row['opportunity_count'] > 0 ? round(($row['won_count'] / $row['opportunity_count']) * 100, 2) : 0;
            $analytics['sales_performance'][] = $row;
        }

        // Opportunity conversion analysis
        $sql = "SELECT
            COUNT(*) as total_opportunities,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_opportunities,
            SUM(CASE WHEN pipeline_stage_id = 6 THEN 1 ELSE 0 END) as lost_opportunities,
            AVG(DATEDIFF(actual_closure_date, created_at)) as avg_sales_cycle,
            SUM(expected_value) as total_pipeline_value,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN expected_value ELSE 0 END) as won_value
            FROM opportunities
            WHERE actual_closure_date IS NOT NULL";

        $result = $this->conn->query($sql);
        $conversionAnalysis = $result->fetch_assoc();
        $conversionAnalysis['conversion_rate'] = $conversionAnalysis['total_opportunities'] > 0 ?
            round(($conversionAnalysis['won_opportunities'] / $conversionAnalysis['total_opportunities']) * 100, 2) : 0;
        $analytics['conversion_analysis'] = $conversionAnalysis;

        // Monthly sales trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as opportunities_created,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as opportunities_won,
            SUM(expected_value) as pipeline_value,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN expected_value ELSE 0 END) as won_value
            FROM opportunities
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";

        $result = $this->conn->query($sql);
        $analytics['monthly_trends'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['win_rate'] = $row['opportunities_created'] > 0 ?
                round(($row['opportunities_won'] / $row['opportunities_created']) * 100, 2) : 0;
            $analytics['monthly_trends'][] = $row;
        }

        return $analytics;
    }

    /**
     * Get customer analytics
     */
    private function getCustomerAnalytics($filters = []) {
        $analytics = [];

        // Customer acquisition
        $sql = "SELECT
            COUNT(*) as total_customers,
            SUM(CASE WHEN customer_status = 'active' THEN 1 ELSE 0 END) as active_customers,
            SUM(CASE WHEN customer_status = 'vip' THEN 1 ELSE 0 END) as vip_customers,
            AVG(total_purchase_value) as avg_customer_value,
            AVG(DATEDIFF(NOW(), created_at)/365) as avg_customer_lifespan_years
            FROM customer_profiles";

        $result = $this->conn->query($sql);
        $analytics['customer_acquisition'] = $result->fetch_assoc();

        // Customer segmentation
        $sql = "SELECT
            customer_type,
            COUNT(*) as customer_count,
            AVG(total_purchase_value) as avg_value,
            SUM(total_purchase_value) as total_value
            FROM customer_profiles
            GROUP BY customer_type";

        $result = $this->conn->query($sql);
        $analytics['customer_segmentation'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['customer_segmentation'][] = $row;
        }

        // Customer lifetime value
        $sql = "SELECT
            cp.customer_type,
            COUNT(*) as customer_count,
            AVG(cp.total_purchase_value) as avg_clv,
            AVG(DATEDIFF(NOW(), cp.created_at)/365) as avg_lifespan_years
            FROM customer_profiles cp
            GROUP BY cp.customer_type";

        $result = $this->conn->query($sql);
        $analytics['customer_lifetime_value'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['projected_ltv'] = $row['avg_clv'] * $row['avg_lifespan_years'];
            $analytics['customer_lifetime_value'][] = $row;
        }

        // Customer satisfaction
        $sql = "SELECT
            AVG(satisfaction_rating) as avg_satisfaction,
            COUNT(CASE WHEN satisfaction_rating >= 4 THEN 1 END) as satisfied_customers,
            COUNT(CASE WHEN satisfaction_rating <= 2 THEN 1 END) as dissatisfied_customers,
            COUNT(*) as total_interactions
            FROM customer_interactions
            WHERE satisfaction_rating > 0";

        $result = $this->conn->query($sql);
        $satisfaction = $result->fetch_assoc();
        $satisfaction['satisfaction_rate'] = $satisfaction['total_interactions'] > 0 ?
            round(($satisfaction['satisfied_customers'] / $satisfaction['total_interactions']) * 100, 2) : 0;
        $analytics['customer_satisfaction'] = $satisfaction;

        // Repeat customers
        $sql = "SELECT
            COUNT(DISTINCT cp.id) as total_customers,
            COUNT(DISTINCT CASE WHEN cp.total_purchases > 1 THEN cp.id END) as repeat_customers,
            AVG(cp.total_purchases) as avg_purchases_per_customer
            FROM customer_profiles cp";

        $result = $this->conn->query($sql);
        $repeatCustomers = $result->fetch_assoc();
        $repeatCustomers['repeat_customer_rate'] = $repeatCustomers['total_customers'] > 0 ?
            round(($repeatCustomers['repeat_customers'] / $repeatCustomers['total_customers']) * 100, 2) : 0;
        $analytics['repeat_customers'] = $repeatCustomers;

        return $analytics;
    }

    /**
     * Get campaign analytics
     */
    private function getCampaignAnalytics($filters = []) {
        $analytics = [];

        // Campaign performance
        $sql = "SELECT
            campaign_type,
            COUNT(*) as total_campaigns,
            SUM(total_sent) as total_sent,
            SUM(total_opened) as total_opened,
            SUM(total_clicked) as total_clicked,
            SUM(total_converted) as total_converted,
            AVG(conversion_rate) as avg_conversion_rate
            FROM campaigns
            GROUP BY campaign_type";

        $result = $this->conn->query($sql);
        $analytics['campaign_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['open_rate'] = $row['total_sent'] > 0 ? round(($row['total_opened'] / $row['total_sent']) * 100, 2) : 0;
            $row['click_rate'] = $row['total_sent'] > 0 ? round(($row['total_clicked'] / $row['total_sent']) * 100, 2) : 0;
            $analytics['campaign_performance'][] = $row;
        }

        // Campaign ROI
        $sql = "SELECT
            c.campaign_name,
            c.budget,
            SUM(l.total_purchase_value) as revenue_generated,
            COUNT(DISTINCT l.id) as leads_converted
            FROM campaigns c
            LEFT JOIN leads l ON c.id = l.campaign_id AND l.lead_status = 'won'
            GROUP BY c.id, c.campaign_name
            ORDER BY revenue_generated DESC";

        $result = $this->conn->query($sql);
        $analytics['campaign_roi'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['roi'] = $row['budget'] > 0 ? round((($row['revenue_generated'] - $row['budget']) / $row['budget']) * 100, 2) : 0;
            $row['cost_per_conversion'] = $row['leads_converted'] > 0 ? round($row['budget'] / $row['leads_converted'], 2) : 0;
            $analytics['campaign_roi'][] = $row;
        }

        // Best performing campaigns
        $sql = "SELECT
            campaign_name,
            campaign_type,
            total_sent,
            conversion_rate,
            total_converted,
            budget
            FROM campaigns
            WHERE status = 'completed'
            ORDER BY conversion_rate DESC
            LIMIT 10";

        $result = $this->conn->query($sql);
        $analytics['best_campaigns'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['best_campaigns'][] = $row;
        }

        return $analytics;
    }

    /**
     * Get performance analytics
     */
    private function getPerformanceAnalytics($filters = []) {
        $analytics = [];

        // User performance
        $sql = "SELECT
            u.full_name as user_name,
            u.role,
            COUNT(DISTINCT l.id) as leads_handled,
            COUNT(DISTINCT o.id) as opportunities_created,
            COUNT(DISTINCT CASE WHEN o.pipeline_stage_id = 5 THEN o.id END) as deals_won,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN o.expected_value END) as revenue_generated,
            AVG(l.lead_score) as avg_lead_quality
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.id, u.full_name, u.role
            ORDER BY revenue_generated DESC";

        $result = $this->conn->query($sql);
        $analytics['user_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['conversion_rate'] = $row['opportunities_created'] > 0 ?
                round(($row['deals_won'] / $row['opportunities_created']) * 100, 2) : 0;
            $analytics['user_performance'][] = $row;
        }

        // Team performance
        $sql = "SELECT
            u.role as team_name,
            COUNT(DISTINCT u.id) as team_size,
            COUNT(DISTINCT l.id) as total_leads,
            COUNT(DISTINCT o.id) as total_opportunities,
            COUNT(DISTINCT CASE WHEN o.pipeline_stage_id = 5 THEN o.id END) as total_wins,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN o.expected_value END) as total_revenue
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.role";

        $result = $this->conn->query($sql);
        $analytics['team_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['avg_performance'] = $row['team_size'] > 0 ? round($row['total_wins'] / $row['team_size'], 2) : 0;
            $row['conversion_rate'] = $row['total_opportunities'] > 0 ?
                round(($row['total_wins'] / $row['total_opportunities']) * 100, 2) : 0;
            $analytics['team_performance'][] = $row;
        }

        // Response time analysis
        $sql = "SELECT
            u.full_name as user_name,
            AVG(TIMESTAMPDIFF(HOUR, la.created_at, l.updated_at)) as avg_response_time_hours,
            COUNT(la.id) as activities_count
            FROM users u
            LEFT JOIN lead_activities la ON u.id = la.created_by
            LEFT JOIN leads l ON la.lead_id = l.id
            GROUP BY u.id, u.full_name
            ORDER BY avg_response_time_hours";

        $result = $this->conn->query($sql);
        $analytics['response_time_analysis'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['response_time_analysis'][] = $row;
        }

        return $analytics;
    }

    /**
     * Get trend analysis
     */
    private function getTrendAnalysis($filters = []) {
        $analytics = [];

        // Lead generation trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m-%d') as date,
            COUNT(*) as leads_generated,
            AVG(lead_score) as avg_lead_score,
            SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as leads_converted
            FROM leads
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date";

        $result = $this->conn->query($sql);
        $analytics['lead_trends'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['conversion_rate'] = $row['leads_generated'] > 0 ?
                round(($row['leads_converted'] / $row['leads_generated']) * 100, 2) : 0;
            $analytics['lead_trends'][] = $row;
        }

        // Sales trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m-%d') as date,
            COUNT(*) as opportunities_created,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as deals_closed,
            SUM(expected_value) as pipeline_value,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN expected_value END) as revenue
            FROM opportunities
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date";

        $result = $this->conn->query($sql);
        $analytics['sales_trends'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['close_rate'] = $row['opportunities_created'] > 0 ?
                round(($row['deals_closed'] / $row['opportunities_created']) * 100, 2) : 0;
            $analytics['sales_trends'][] = $row;
        }

        // Customer acquisition trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m-%d') as date,
            COUNT(*) as customers_acquired,
            AVG(total_purchase_value) as avg_purchase_value,
            SUM(total_purchase_value) as total_revenue
            FROM customer_profiles
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date";

        $result = $this->conn->query($sql);
        $analytics['customer_trends'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['customer_trends'][] = $row;
        }

        // Support ticket trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m-%d') as date,
            COUNT(*) as tickets_created,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as tickets_resolved,
            AVG(satisfaction_rating) as avg_satisfaction
            FROM support_tickets
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date";

        $result = $this->conn->query($sql);
        $analytics['support_trends'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['resolution_rate'] = $row['tickets_created'] > 0 ?
                round(($row['tickets_resolved'] / $row['tickets_created']) * 100, 2) : 0;
            $analytics['support_trends'][] = $row;
        }

        return $analytics;
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport($reportType, $filters = []) {
        switch ($reportType) {
            case 'lead_report':
                return $this->generateLeadReport($filters);
            case 'sales_report':
                return $this->generateSalesReport($filters);
            case 'customer_report':
                return $this->generateCustomerReport($filters);
            case 'campaign_report':
                return $this->generateCampaignReport($filters);
            case 'performance_report':
                return $this->generatePerformanceReport($filters);
            default:
                return ['error' => 'Invalid report type'];
        }
    }

    /**
     * Generate lead report
     */
    private function generateLeadReport($filters = []) {
        $report = [
            'title' => 'Lead Generation Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Lead summary
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN lead_status = 'new' THEN 1 ELSE 0 END) as new_leads,
            SUM(CASE WHEN lead_status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
            SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as converted_leads,
            AVG(lead_score) as avg_lead_score
            FROM leads";

        if (!empty($filters['date_from'])) {
            $sql .= " WHERE created_at >= '" . $filters['date_from'] . "'";
        }

        $result = $this->conn->query($sql);
        $report['summary'] = $result->fetch_assoc();

        // Lead source breakdown
        $sql = "SELECT ls.source_name, COUNT(l.id) as lead_count
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.id = l.lead_source_id
                GROUP BY ls.id, ls.source_name
                ORDER BY lead_count DESC";

        $result = $this->conn->query($sql);
        $report['lead_sources'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['lead_sources'][] = $row;
        }

        // Lead status breakdown
        $sql = "SELECT lead_status, COUNT(*) as count
                FROM leads
                GROUP BY lead_status
                ORDER BY count DESC";

        $result = $this->conn->query($sql);
        $report['lead_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['lead_status'][] = $row;
        }

        return $report;
    }

    /**
     * Generate sales report
     */
    private function generateSalesReport($filters = []) {
        $report = [
            'title' => 'Sales Performance Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Sales summary
        $sql = "SELECT
            COUNT(*) as total_opportunities,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_deals,
            SUM(CASE WHEN pipeline_stage_id = 6 THEN 1 ELSE 0 END) as lost_deals,
            SUM(expected_value) as total_pipeline_value,
            SUM(CASE WHEN pipeline_stage_id = 5 THEN expected_value END) as won_value
            FROM opportunities";

        $result = $this->conn->query($sql);
        $report['summary'] = $result->fetch_assoc();

        // Pipeline stage breakdown
        $sql = "SELECT s.stage_name, COUNT(o.id) as opportunity_count, SUM(o.expected_value) as total_value
                FROM sales_pipeline_stages s
                LEFT JOIN opportunities o ON s.id = o.pipeline_stage_id
                GROUP BY s.id, s.stage_name
                ORDER BY s.stage_order";

        $result = $this->conn->query($sql);
        $report['pipeline_stages'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['pipeline_stages'][] = $row;
        }

        // Sales by user
        $sql = "SELECT u.full_name as user_name, COUNT(o.id) as opportunities, SUM(CASE WHEN o.pipeline_stage_id = 5 THEN 1 ELSE 0 END) as won_deals
                FROM users u
                LEFT JOIN opportunities o ON u.id = o.assigned_to
                GROUP BY u.id, u.full_name
                ORDER BY won_deals DESC";

        $result = $this->conn->query($sql);
        $report['sales_by_user'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['sales_by_user'][] = $row;
        }

        return $report;
    }

    /**
     * Generate customer report
     */
    private function generateCustomerReport($filters = []) {
        $report = [
            'title' => 'Customer Analysis Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Customer summary
        $sql = "SELECT
            COUNT(*) as total_customers,
            SUM(CASE WHEN customer_status = 'active' THEN 1 ELSE 0 END) as active_customers,
            SUM(CASE WHEN customer_status = 'vip' THEN 1 ELSE 0 END) as vip_customers,
            AVG(total_purchase_value) as avg_customer_value
            FROM customer_profiles";

        $result = $this->conn->query($sql);
        $report['summary'] = $result->fetch_assoc();

        // Customer segmentation
        $sql = "SELECT customer_type, COUNT(*) as count, SUM(total_purchase_value) as total_value
                FROM customer_profiles
                GROUP BY customer_type";

        $result = $this->conn->query($sql);
        $report['customer_segments'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['customer_segments'][] = $row;
        }

        // Top customers
        $sql = "SELECT first_name, last_name, total_purchase_value, total_purchases, customer_type
                FROM customer_profiles
                ORDER BY total_purchase_value DESC
                LIMIT 10";

        $result = $this->conn->query($sql);
        $report['top_customers'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['top_customers'][] = $row;
        }

        return $report;
    }

    /**
     * Generate campaign report
     */
    private function generateCampaignReport($filters = []) {
        $report = [
            'title' => 'Campaign Performance Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Campaign summary
        $sql = "SELECT
            COUNT(*) as total_campaigns,
            SUM(total_sent) as total_sent,
            SUM(total_converted) as total_converted,
            AVG(conversion_rate) as avg_conversion_rate
            FROM campaigns";

        $result = $this->conn->query($sql);
        $report['summary'] = $result->fetch_assoc();

        // Campaign performance by type
        $sql = "SELECT
            campaign_type,
            COUNT(*) as campaign_count,
            AVG(conversion_rate) as avg_conversion_rate,
            SUM(total_sent) as total_sent,
            SUM(total_converted) as total_converted
            FROM campaigns
            GROUP BY campaign_type";

        $result = $this->conn->query($sql);
        $report['performance_by_type'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['performance_by_type'][] = $row;
        }

        // Best performing campaigns
        $sql = "SELECT campaign_name, campaign_type, total_sent, conversion_rate, total_converted
                FROM campaigns
                ORDER BY conversion_rate DESC
                LIMIT 10";

        $result = $this->conn->query($sql);
        $report['best_campaigns'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['best_campaigns'][] = $row;
        }

        return $report;
    }

    /**
     * Generate performance report
     */
    private function generatePerformanceReport($filters = []) {
        $report = [
            'title' => 'Team Performance Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Overall performance
        $sql = "SELECT
            COUNT(DISTINCT u.id) as total_users,
            COUNT(DISTINCT l.id) as total_leads,
            COUNT(DISTINCT o.id) as total_opportunities,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN 1 ELSE 0 END) as total_wins
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to";

        $result = $this->conn->query($sql);
        $report['overall_performance'] = $result->fetch_assoc();

        // User performance
        $sql = "SELECT
            u.full_name as user_name,
            u.role,
            COUNT(DISTINCT l.id) as leads_assigned,
            COUNT(DISTINCT o.id) as opportunities_created,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN 1 ELSE 0 END) as deals_won,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN o.expected_value END) as revenue_generated
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.id, u.full_name, u.role";

        $result = $this->conn->query($sql);
        $report['user_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $row['conversion_rate'] = $row['opportunities_created'] > 0 ?
                round(($row['deals_won'] / $row['opportunities_created']) * 100, 2) : 0;
            $report['user_performance'][] = $row;
        }

        // Team performance
        $sql = "SELECT
            u.role as team_name,
            COUNT(DISTINCT u.id) as team_size,
            AVG(CASE WHEN l.id IS NOT NULL THEN 1 ELSE 0 END) as avg_leads_per_user,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN 1 ELSE 0 END) as total_wins,
            SUM(CASE WHEN o.pipeline_stage_id = 5 THEN o.expected_value END) as total_revenue
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.role";

        $result = $this->conn->query($sql);
        $report['team_performance'] = [];
        while ($row = $result->fetch_assoc()) {
            $report['team_performance'][] = $row;
        }

        return $report;
    }

    /**
     * Export data to CSV
     */
    public function exportToCSV($data, $filename) {
        $filepath = '/tmp/' . $filename . '.csv';

        $file = fopen($filepath, 'w');

        if (is_array($data) && !empty($data)) {
            // Write headers
            fputcsv($file, array_keys($data[0]));

            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }

        fclose($file);

        return $filepath;
    }

    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics() {
        return [
            'total_leads' => $this->getTotalCount('leads'),
            'total_opportunities' => $this->getTotalCount('opportunities'),
            'total_customers' => $this->getTotalCount('customer_profiles'),
            'total_revenue' => $this->getTotalRevenue(),
            'conversion_rate' => $this->getConversionRate(),
            'avg_deal_size' => $this->getAverageDealSize(),
            'sales_cycle' => $this->getAverageSalesCycle()
        ];
    }

    /**
     * Get total count for a table
     */
    public function getTotalCount($tableName) {
        $sql = "SELECT COUNT(*) as count FROM $tableName";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['count'] ?? 0;
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue() {
        $sql = "SELECT SUM(expected_value) as revenue FROM opportunities WHERE pipeline_stage_id = 5";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['revenue'] ?? 0;
    }

    /**
     * Get conversion rate
     */
    public function getConversionRate() {
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN lead_status = 'won' THEN 1 ELSE 0 END) as converted_leads
            FROM leads";
        $result = $this->conn->query($sql);
        $data = $result->fetch_assoc();

        return $data['total_leads'] > 0 ?
            round(($data['converted_leads'] / $data['total_leads']) * 100, 2) : 0;
    }

    /**
     * Get average deal size
     */
    public function getAverageDealSize() {
        $sql = "SELECT AVG(expected_value) as avg_deal FROM opportunities WHERE pipeline_stage_id = 5";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['avg_deal'] ?? 0;
    }

    /**
     * Get average sales cycle
     */
    public function getAverageSalesCycle() {
        $sql = "SELECT AVG(DATEDIFF(actual_closure_date, created_at)) as avg_cycle
                FROM opportunities WHERE actual_closure_date IS NOT NULL";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['avg_cycle'] ?? 0;
    }

    /**
     * Get active campaigns count
     */
    public function getActiveCampaignsCount() {
        $sql = "SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['count'] ?? 0;
    }
}
?>
