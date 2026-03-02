<?php

namespace App\Services\Legacy;

/**
 * CRM Analytics and Reporting System
 * Advanced analytics and reporting for CRM data
 */

class CRMAnalyticsManager
{
    private $db;
    private $logger;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
    }

    /**
     * Get comprehensive CRM analytics
     */
    public function getComprehensiveAnalytics($filters = [])
    {
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
    private function getLeadAnalytics($filters = [])
    {
        $analytics = [];

        // Lead conversion funnel
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
            SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_leads,
            SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
            SUM(CASE WHEN status = 'proposal_sent' OR status = 'proposal' THEN 1 ELSE 0 END) as proposal_sent_leads,
            SUM(CASE WHEN status = 'won' OR status = 'closed_won' THEN 1 ELSE 0 END) as won_leads,
            SUM(CASE WHEN status = 'lost' OR status = 'closed_lost' THEN 1 ELSE 0 END) as lost_leads
            FROM leads";

        $params = [];
        if (!empty($filters['date_from'])) {
            $sql .= " WHERE created_at >= ?";
            $params[] = $filters['date_from'];
        }

        $funnel = $this->db->fetch($sql, $params);

        $analytics['conversion_funnel'] = [
            'total_leads' => $funnel['total_leads'] ?? 0,
            'contacted_rate' => ($funnel['total_leads'] ?? 0) > 0 ? round(($funnel['contacted_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'qualified_rate' => ($funnel['total_leads'] ?? 0) > 0 ? round(($funnel['qualified_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'proposal_rate' => ($funnel['total_leads'] ?? 0) > 0 ? round(($funnel['proposal_sent_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'win_rate' => ($funnel['total_leads'] ?? 0) > 0 ? round(($funnel['won_leads'] / $funnel['total_leads']) * 100, 2) : 0,
            'loss_rate' => ($funnel['total_leads'] ?? 0) > 0 ? round(($funnel['lost_leads'] / $funnel['total_leads']) * 100, 2) : 0
        ];

        // Lead sources performance
        $sql = "SELECT ls.name as source_name,
                       COUNT(l.id) as lead_count,
                       SUM(CASE WHEN l.status = 'won' OR l.status = 'closed_won' THEN 1 ELSE 0 END) as converted_count,
                       AVG(l.lead_score) as avg_lead_score,
                       AVG(l.conversion_probability) as avg_conversion_prob
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.name = l.source
                GROUP BY ls.id, ls.name
                ORDER BY lead_count DESC";

        $results = $this->db->fetchAll($sql);
        $analytics['lead_sources_performance'] = [];
        foreach ($results as $row) {
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

        $analytics['lead_quality_metrics'] = $this->db->fetch($sql);

        // Lead velocity (time to conversion)
        $sql = "SELECT
            AVG(DATEDIFF(l.updated_at, l.created_at)) as avg_time_to_conversion
            FROM leads l
            WHERE l.status = 'won' OR l.status = 'closed_won'";

        $analytics['lead_velocity'] = $this->db->fetch($sql);

        return $analytics;
    }

    /**
     * Get sales analytics
     */
    private function getSalesAnalytics($filters = [])
    {
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

        $analytics['pipeline_analysis'] = $this->db->fetchAll($sql);

        // Sales performance by user
        $sql = "SELECT u.name as user_name,
                       COUNT(o.id) as opportunity_count,
                       SUM(CASE WHEN o.stage = 'won' OR o.stage = 'closed_won' THEN 1 ELSE 0 END) as won_count,
                       SUM(o.expected_value) as total_value,
                       AVG(o.probability_percentage) as avg_probability
                FROM users u
                LEFT JOIN opportunities o ON u.id = o.assigned_to
                GROUP BY u.id, u.name
                ORDER BY won_count DESC";

        $results = $this->db->fetchAll($sql);
        $analytics['sales_performance'] = [];
        foreach ($results as $row) {
            $row['win_rate'] = $row['opportunity_count'] > 0 ? round(($row['won_count'] / $row['opportunity_count']) * 100, 2) : 0;
            $analytics['sales_performance'][] = $row;
        }

        // Opportunity conversion analysis
        $sql = "SELECT
            COUNT(*) as total_opportunities,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won_opportunities,
            SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_opportunities,
            AVG(DATEDIFF(updated_at, created_at)) as avg_sales_cycle,
            SUM(expected_value) as total_pipeline_value,
            SUM(CASE WHEN status = 'won' THEN expected_value ELSE 0 END) as won_value
            FROM opportunities
            WHERE status = 'won' OR status = 'lost'";

        $conversionAnalysis = $this->db->fetch($sql);
        $conversionAnalysis['conversion_rate'] = ($conversionAnalysis['total_opportunities'] ?? 0) > 0 ?
            round(($conversionAnalysis['won_opportunities'] / $conversionAnalysis['total_opportunities']) * 100, 2) : 0;
        $analytics['conversion_analysis'] = $conversionAnalysis;

        // Monthly sales trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as opportunities_created,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as opportunities_won,
            SUM(expected_value) as pipeline_value,
            SUM(CASE WHEN status = 'won' THEN expected_value ELSE 0 END) as won_value
            FROM opportunities
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";

        $results = $this->db->fetchAll($sql);
        $analytics['monthly_trends'] = [];
        foreach ($results as $row) {
            $row['win_rate'] = $row['opportunities_created'] > 0 ?
                round(($row['opportunities_won'] / $row['opportunities_created']) * 100, 2) : 0;
            $analytics['monthly_trends'][] = $row;
        }

        return $analytics;
    }

    /**
     * Get customer analytics
     */
    private function getCustomerAnalytics($filters = [])
    {
        $analytics = [];

        // Customer acquisition
        $sql = "SELECT
            COUNT(*) as total_customers,
            SUM(CASE WHEN customer_status = 'active' THEN 1 ELSE 0 END) as active_customers,
            SUM(CASE WHEN customer_status = 'vip' THEN 1 ELSE 0 END) as vip_customers,
            AVG(total_purchase_value) as avg_customer_value,
            AVG(DATEDIFF(NOW(), created_at)/365) as avg_customer_lifespan_years
            FROM customer_profiles";

        $analytics['customer_acquisition'] = $this->db->fetch($sql);

        // Customer segmentation
        $sql = "SELECT
            customer_type,
            COUNT(*) as customer_count,
            AVG(total_purchase_value) as avg_value,
            SUM(total_purchase_value) as total_value
            FROM customer_profiles
            GROUP BY customer_type";

        $analytics['customer_segmentation'] = $this->db->fetchAll($sql);

        // Customer lifetime value
        $sql = "SELECT
            cp.customer_type,
            COUNT(*) as customer_count,
            AVG(cp.total_purchase_value) as avg_clv,
            AVG(DATEDIFF(NOW(), cp.created_at)/365) as avg_lifespan_years
            FROM customer_profiles cp
            GROUP BY cp.customer_type";

        $results = $this->db->fetchAll($sql);
        $analytics['customer_lifetime_value'] = [];
        foreach ($results as $row) {
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

        $satisfaction = $this->db->fetch($sql);
        $satisfaction['satisfaction_rate'] = ($satisfaction['total_interactions'] ?? 0) > 0 ?
            round(($satisfaction['satisfied_customers'] / $satisfaction['total_interactions']) * 100, 2) : 0;
        $analytics['customer_satisfaction'] = $satisfaction;

        // Repeat customers
        $sql = "SELECT
            COUNT(DISTINCT cp.id) as total_customers,
            COUNT(DISTINCT CASE WHEN cp.total_purchases > 1 THEN cp.id END) as repeat_customers,
            AVG(cp.total_purchases) as avg_purchases_per_customer
            FROM customer_profiles cp";

        $repeatCustomers = $this->db->fetch($sql);
        $repeatCustomers['repeat_customer_rate'] = ($repeatCustomers['total_customers'] ?? 0) > 0 ?
            round(($repeatCustomers['repeat_customers'] / $repeatCustomers['total_customers']) * 100, 2) : 0;
        $analytics['repeat_customers'] = $repeatCustomers;

        return $analytics;
    }

    /**
     * Get campaign analytics
     */
    private function getCampaignAnalytics($filters = [])
    {
        $analytics = [];

        // Campaign performance
        $sql = "SELECT
            type as campaign_type,
            COUNT(*) as total_campaigns,
            total_sent,
            total_opened,
            total_clicked,
            total_converted,
            AVG(conversion_rate) as avg_conversion_rate
            FROM campaigns
            GROUP BY type";

        $results = $this->db->fetchAll($sql);
        $analytics['campaign_performance'] = [];
        foreach ($results as $row) {
            $row['open_rate'] = ($row['total_sent'] ?? 0) > 0 ? round(($row['total_opened'] / $row['total_sent']) * 100, 2) : 0;
            $row['click_rate'] = ($row['total_sent'] ?? 0) > 0 ? round(($row['total_clicked'] / $row['total_sent']) * 100, 2) : 0;
            $analytics['campaign_performance'][] = $row;
        }

        // Campaign ROI
        $sql = "SELECT
            c.name as campaign_name,
            c.budget,
            SUM(l.total_purchase_value) as revenue_generated,
            COUNT(DISTINCT l.id) as leads_converted
            FROM campaigns c
            LEFT JOIN leads l ON c.campaign_id = l.campaign_id AND l.status = 'won'
            GROUP BY c.campaign_id, c.name
            ORDER BY revenue_generated DESC";

        $results = $this->db->fetchAll($sql);
        $analytics['campaign_roi'] = [];
        foreach ($results as $row) {
            $row['roi'] = ($row['budget'] ?? 0) > 0 ? round((($row['revenue_generated'] - $row['budget']) / $row['budget']) * 100, 2) : 0;
            $row['cost_per_conversion'] = ($row['leads_converted'] ?? 0) > 0 ? round($row['budget'] / $row['leads_converted'], 2) : 0;
            $analytics['campaign_roi'][] = $row;
        }

        // Best performing campaigns
        $sql = "SELECT
            name as campaign_name,
            type as campaign_type,
            total_sent,
            conversion_rate,
            total_converted,
            budget
            FROM campaigns
            WHERE status = 'completed'
            ORDER BY conversion_rate DESC
            LIMIT 10";

        $analytics['best_campaigns'] = $this->db->fetchAll($sql);

        return $analytics;
    }

    /**
     * Get performance analytics
     */
    private function getPerformanceAnalytics($filters = [])
    {
        $analytics = [];

        // User performance
        $sql = "SELECT
            u.name as user_name,
            u.role as role,
            COUNT(DISTINCT l.id) as leads_handled,
            COUNT(DISTINCT o.id) as opportunities_created,
            COUNT(DISTINCT CASE WHEN o.status = 'won' THEN o.id END) as deals_won,
            SUM(CASE WHEN o.status = 'won' THEN o.expected_value END) as revenue_generated,
            AVG(l.lead_score) as avg_lead_quality
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.id, u.name, u.role
            ORDER BY revenue_generated DESC";

        $results = $this->db->fetchAll($sql);
        $analytics['user_performance'] = [];
        foreach ($results as $row) {
            $row['conversion_rate'] = ($row['opportunities_created'] ?? 0) > 0 ?
                round(($row['deals_won'] / $row['opportunities_created']) * 100, 2) : 0;
            $analytics['user_performance'][] = $row;
        }

        // Team performance
        $sql = "SELECT
            u.role as team_name,
            COUNT(DISTINCT u.id) as team_size,
            COUNT(DISTINCT l.id) as total_leads,
            COUNT(DISTINCT o.id) as total_opportunities,
            COUNT(DISTINCT CASE WHEN o.status = 'won' THEN o.id END) as total_wins,
            SUM(CASE WHEN o.status = 'won' THEN o.expected_value END) as total_revenue
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.role";

        $results = $this->db->fetchAll($sql);
        $analytics['team_performance'] = [];
        foreach ($results as $row) {
            $row['avg_performance'] = ($row['team_size'] ?? 0) > 0 ? round($row['total_wins'] / $row['team_size'], 2) : 0;
            $row['conversion_rate'] = ($row['total_opportunities'] ?? 0) > 0 ?
                round(($row['total_wins'] / $row['total_opportunities']) * 100, 2) : 0;
            $analytics['team_performance'][] = $row;
        }

        // Response time analysis
        $sql = "SELECT
            u.name as user_name,
            AVG(TIMESTAMPDIFF(HOUR, la.created_at, l.updated_at)) as avg_response_time_hours,
            COUNT(la.id) as activities_count
            FROM users u
            LEFT JOIN lead_activities la ON u.id = la.created_by
            LEFT JOIN leads l ON la.lead_id = l.id
            GROUP BY u.id, u.name
            ORDER BY avg_response_time_hours";

        $analytics['response_time_analysis'] = $this->db->fetchAll($sql);

        return $analytics;
    }

    /**
     * Get trend analysis
     */
    private function getTrendAnalysis($filters = [])
    {
        $analytics = [];

        // Lead generation trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m-%d') as date,
            COUNT(*) as leads_generated,
            AVG(lead_score) as avg_lead_score,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as leads_converted
            FROM leads
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date";

        $results = $this->db->fetchAll($sql);
        $analytics['lead_trends'] = [];
        foreach ($results as $row) {
            $row['conversion_rate'] = $row['leads_generated'] > 0 ?
                round(($row['leads_converted'] / $row['leads_generated']) * 100, 2) : 0;
            $analytics['lead_trends'][] = $row;
        }

        // Sales trends
        $sql = "SELECT
            DATE_FORMAT(created_at, '%Y-%m-%d') as date,
            COUNT(*) as opportunities_created,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as deals_closed,
            SUM(expected_value) as pipeline_value,
            SUM(CASE WHEN status = 'won' THEN expected_value END) as revenue
            FROM opportunities
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ORDER BY date";

        $results = $this->db->fetchAll($sql);
        $analytics['sales_trends'] = [];
        foreach ($results as $row) {
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

        $analytics['customer_trends'] = $this->db->fetchAll($sql);

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

        $results = $this->db->fetchAll($sql);
        $analytics['support_trends'] = [];
        foreach ($results as $row) {
            $row['resolution_rate'] = $row['tickets_created'] > 0 ?
                round(($row['tickets_resolved'] / $row['tickets_created']) * 100, 2) : 0;
            $analytics['support_trends'][] = $row;
        }

        return $analytics;
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport($reportType, $filters = [])
    {
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
    private function generateLeadReport($filters = [])
    {
        $report = [
            'title' => 'Lead Generation Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Lead summary
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
            SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as converted_leads,
            AVG(lead_score) as avg_lead_score
            FROM leads";

        if (!empty($filters['date_from'])) {
            $sql .= " WHERE created_at >= ?";
            $params[] = $filters['date_from'];
        }

        $report['summary'] = $this->db->fetch($sql, $params);

        // Lead source breakdown
        $sql = "SELECT source as source_name, COUNT(id) as lead_count
                FROM leads
                GROUP BY source
                ORDER BY lead_count DESC";

        $report['lead_sources'] = $this->db->fetchAll($sql);

        // Lead status breakdown
        $sql = "SELECT status as lead_status, COUNT(*) as count
                FROM leads
                GROUP BY status
                ORDER BY count DESC";

        $report['lead_status'] = $this->db->fetchAll($sql);

        return $report;
    }

    /**
     * Generate sales report
     */
    private function generateSalesReport($filters = [])
    {
        $report = [
            'title' => 'Sales Performance Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Sales summary
        $sql = "SELECT
            COUNT(*) as total_opportunities,
            SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won_deals,
            SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_deals,
            SUM(expected_value) as total_pipeline_value,
            SUM(CASE WHEN status = 'won' THEN expected_value END) as won_value
            FROM opportunities";

        $report['summary'] = $this->db->fetch($sql);

        // Pipeline stage breakdown
        $sql = "SELECT s.stage_name, COUNT(o.id) as opportunity_count, SUM(o.expected_value) as total_value
                FROM sales_pipeline_stages s
                LEFT JOIN opportunities o ON s.id = o.pipeline_stage_id
                GROUP BY s.id, s.stage_name
                ORDER BY s.stage_order";

        $report['pipeline_stages'] = $this->db->fetchAll($sql);

        // Sales by user
        $sql = "SELECT u.name as user_name, COUNT(o.id) as opportunities, SUM(CASE WHEN o.status = 'won' THEN 1 ELSE 0 END) as won_deals
                FROM users u
                LEFT JOIN opportunities o ON u.id = o.assigned_to
                GROUP BY u.id, u.name
                ORDER BY won_deals DESC
                LIMIT 5";

        $report['sales_by_user'] = $this->db->fetchAll($sql);

        return $report;
    }

    /**
     * Generate customer report
     */
    private function generateCustomerReport($filters = [])
    {
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

        $report['summary'] = $this->db->fetch($sql);

        // Customer segmentation
        $sql = "SELECT customer_type, COUNT(*) as count, SUM(total_purchase_value) as total_value
                FROM customer_profiles
                GROUP BY customer_type";

        $report['customer_segments'] = $this->db->fetchAll($sql);

        // Top customers
        $sql = "SELECT first_name, last_name, total_purchase_value, total_purchases, customer_type
                FROM customer_profiles
                ORDER BY total_purchase_value DESC
                LIMIT 10";

        $report['top_customers'] = $this->db->fetchAll($sql);

        return $report;
    }

    /**
     * Generate campaign report
     */
    private function generateCampaignReport($filters = [])
    {
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

        $report['summary'] = $this->db->fetch($sql);

        // Campaign performance by type
        $sql = "SELECT
            campaign_type,
            COUNT(*) as campaign_count,
            AVG(conversion_rate) as avg_conversion_rate,
            SUM(total_sent) as total_sent,
            SUM(total_converted) as total_converted
            FROM campaigns
            GROUP BY campaign_type";

        $report['performance_by_type'] = $this->db->fetchAll($sql);

        // Best performing campaigns
        $sql = "SELECT campaign_name, campaign_type, total_sent, conversion_rate, total_converted
                FROM campaigns
                ORDER BY conversion_rate DESC
                LIMIT 10";

        $report['best_campaigns'] = $this->db->fetchAll($sql);

        return $report;
    }

    /**
     * Generate performance report
     */
    private function generatePerformanceReport($filters = [])
    {
        $report = [
            'title' => 'Team Performance Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'filters' => $filters
        ];

        // Overall performance
        $sql = "SELECT
            COUNT(o.id) as total_opportunities,
            SUM(CASE WHEN o.status = 'won' THEN 1 ELSE 0 END) as total_wins
            FROM opportunities o";

        $totalStats = $this->db->fetch($sql);
        $report['overall_performance'] = $totalStats;

        // User comparison
        $sql = "SELECT
            u.name as user_name,
            u.role as role,
            COUNT(o.id) as opportunities,
            SUM(CASE WHEN o.status = 'won' THEN 1 ELSE 0 END) as deals_won,
            SUM(CASE WHEN o.status = 'won' THEN o.expected_value END) as revenue_generated
            FROM users u
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.id, u.name, u.role";

        $results = $this->db->fetchAll($sql);
        $report['user_performance'] = [];
        foreach ($results as $row) {
            $row['market_share'] = ($totalStats['total_wins'] ?? 0) > 0 ?
                round(($row['deals_won'] / $totalStats['total_wins']) * 100, 2) : 0;
            $report['user_performance'][] = $row;
        }

        // Team comparison
        $sql = "SELECT
            u.role as team_name,
            COUNT(o.id) as opportunities,
            SUM(CASE WHEN o.status = 'won' THEN 1 ELSE 0 END) as total_wins,
            SUM(CASE WHEN o.status = 'won' THEN o.expected_value END) as total_revenue
            FROM users u
            LEFT JOIN opportunities o ON u.id = o.assigned_to
            GROUP BY u.role";

        $report['team_performance'] = $this->db->fetchAll($sql);

        return $report;
    }

    /**
     * Export data to CSV
     */
    public function exportToCSV($data, $filename)
    {
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
    public function getDashboardMetrics()
    {
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
    public function getTotalCount($tableName)
    {
        $sql = "SELECT COUNT(*) as count FROM $tableName";
        $data = $this->db->fetch($sql);
        return $data['count'] ?? 0;
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue()
    {
        $sql = "SELECT SUM(expected_value) as revenue FROM opportunities WHERE status = 'won'";
        $data = $this->db->fetch($sql);
        return $data['revenue'] ?? 0;
    }

    /**
     * Get conversion rate
     */
    public function getConversionRate()
    {
        $sql = "SELECT
            COUNT(*) as total_leads,
            SUM(CASE WHEN status = 'won' OR status = 'converted' THEN 1 ELSE 0 END) as converted_leads
            FROM leads";
        $data = $this->db->fetch($sql);

        return ($data['total_leads'] ?? 0) > 0 ?
            round(($data['converted_leads'] / $data['total_leads']) * 100, 2) : 0;
    }

    /**
     * Get average deal size
     */
    public function getAverageDealSize()
    {
        $sql = "SELECT AVG(expected_value) as avg_deal FROM opportunities WHERE status = 'won'";
        $data = $this->db->fetch($sql);
        return $data['avg_deal'] ?? 0;
    }

    /**
     * Get average sales cycle
     */
    public function getAverageSalesCycle()
    {
        $sql = "SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_cycle
                FROM opportunities WHERE status = 'won'";
        $data = $this->db->fetch($sql);
        return $data['avg_cycle'] ?? 0;
    }

    /**
     * Get active campaigns count
     */
    public function getActiveCampaignsCount()
    {
        $sql = "SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'";
        $data = $this->db->fetch($sql);
        return $data['count'] ?? 0;
    }
}
