<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Advanced System Analytics Dashboard Model
 * Handles system-wide analytics, dashboards, reports, and alerts
 */
class SystemAnalytics extends Model
{
    protected $table = 'system_analytics_metrics';

    /**
     * Calculate and store system metrics
     */
    public function calculateSystemMetrics(string $period = 'monthly', string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $metrics = [];

        // User metrics
        $metrics = array_merge($metrics, $this->calculateUserMetrics($date, $period));
        $metrics = array_merge($metrics, $this->calculatePropertyMetrics($date, $period));
        $metrics = array_merge($metrics, $this->calculateFinanceMetrics($date, $period));
        $metrics = array_merge($metrics, $this->calculateCrmMetrics($date, $period));
        $metrics = array_merge($metrics, $this->calculateCommunicationMetrics($date, $period));
        $metrics = array_merge($metrics, $this->calculatePerformanceMetrics($date, $period));

        // Store metrics
        foreach ($metrics as $metric) {
            $this->storeMetric($metric);
        }

        return [
            'success' => true,
            'metrics_calculated' => count($metrics),
            'period' => $period,
            'date' => $date
        ];
    }

    /**
     * Calculate user-related metrics
     */
    private function calculateUserMetrics(string $date, string $period): array
    {
        $startDate = $this->getPeriodStartDate($date, $period);
        $endDate = $date;

        $metrics = [];

        // Total users
        $totalUsers = $this->query("SELECT COUNT(*) as count FROM users WHERE created_at <= ?", [$endDate])->fetch()['count'];
        $metrics[] = [
            'category' => 'users',
            'name' => 'Total Users',
            'key' => 'total_users',
            'value' => $totalUsers,
            'period_type' => $period,
            'period_date' => $date
        ];

        // New users in period
        $newUsers = $this->query(
            "SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'users',
            'name' => 'New Users',
            'key' => 'new_users',
            'value' => $newUsers,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Active users (logged in within last 30 days)
        $activeUsers = $this->query(
            "SELECT COUNT(DISTINCT user_id) as count FROM user_sessions
             WHERE last_activity >= ? AND user_type = 'customer'",
            [date('Y-m-d H:i:s', strtotime('-30 days'))]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'users',
            'name' => 'Active Users (30 days)',
            'key' => 'active_users_30d',
            'value' => $activeUsers,
            'period_type' => $period,
            'period_date' => $date
        ];

        return $metrics;
    }

    /**
     * Calculate property-related metrics
     */
    private function calculatePropertyMetrics(string $date, string $period): array
    {
        $startDate = $this->getPeriodStartDate($date, $period);
        $endDate = $date;

        $metrics = [];

        // Total properties
        $totalProperties = $this->query("SELECT COUNT(*) as count FROM properties WHERE status != 'deleted'")->fetch()['count'];
        $metrics[] = [
            'category' => 'properties',
            'name' => 'Total Properties',
            'key' => 'total_properties',
            'value' => $totalProperties,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Active properties
        $activeProperties = $this->query("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")->fetch()['count'];
        $metrics[] = [
            'category' => 'properties',
            'name' => 'Active Properties',
            'key' => 'active_properties',
            'value' => $activeProperties,
            'period_type' => $period,
            'period_date' => $date
        ];

        // New properties in period
        $newProperties = $this->query(
            "SELECT COUNT(*) as count FROM properties WHERE created_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'properties',
            'name' => 'New Properties',
            'key' => 'new_properties',
            'value' => $newProperties,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Average property price
        $avgPrice = $this->query(
            "SELECT AVG(price) as avg_price FROM properties WHERE status = 'active' AND price > 0"
        )->fetch()['avg_price'];
        $metrics[] = [
            'category' => 'properties',
            'name' => 'Average Property Price',
            'key' => 'avg_property_price',
            'value' => $avgPrice,
            'period_type' => $period,
            'period_date' => $date
        ];

        return $metrics;
    }

    /**
     * Calculate finance-related metrics
     */
    private function calculateFinanceMetrics(string $date, string $period): array
    {
        $startDate = $this->getPeriodStartDate($date, $period);
        $endDate = $date;

        $metrics = [];

        // Total revenue (from invoices)
        $totalRevenue = $this->query(
            "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM invoices WHERE status = 'paid'"
        )->fetch()['revenue'];
        $metrics[] = [
            'category' => 'finance',
            'name' => 'Total Revenue',
            'key' => 'total_revenue',
            'value' => $totalRevenue,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Monthly revenue
        $monthlyRevenue = $this->query(
            "SELECT COALESCE(SUM(total_amount), 0) as revenue FROM invoices
             WHERE status = 'paid' AND paid_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['revenue'];
        $metrics[] = [
            'category' => 'finance',
            'name' => 'Monthly Revenue',
            'key' => 'monthly_revenue',
            'value' => $monthlyRevenue,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Outstanding invoices
        $outstandingInvoices = $this->query(
            "SELECT COALESCE(SUM(total_amount - COALESCE(paid_amount, 0)), 0) as outstanding
             FROM invoices WHERE status IN ('sent', 'viewed') AND due_date >= CURDATE()"
        )->fetch()['outstanding'];
        $metrics[] = [
            'category' => 'finance',
            'name' => 'Outstanding Invoices',
            'key' => 'outstanding_invoices',
            'value' => $outstandingInvoices,
            'period_type' => $period,
            'period_date' => $date
        ];

        return $metrics;
    }

    /**
     * Calculate CRM-related metrics
     */
    private function calculateCrmMetrics(string $date, string $period): array
    {
        $startDate = $this->getPeriodStartDate($date, $period);
        $endDate = $date;

        $metrics = [];

        // Total leads
        $totalLeads = $this->query("SELECT COUNT(*) as count FROM leads")->fetch()['count'];
        $metrics[] = [
            'category' => 'crm',
            'name' => 'Total Leads',
            'key' => 'total_leads',
            'value' => $totalLeads,
            'period_type' => $period,
            'period_date' => $date
        ];

        // New leads in period
        $newLeads = $this->query(
            "SELECT COUNT(*) as count FROM leads WHERE created_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'crm',
            'name' => 'New Leads',
            'key' => 'new_leads',
            'value' => $newLeads,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Lead conversion rate
        $convertedLeads = $this->query(
            "SELECT COUNT(*) as count FROM leads WHERE status = 'converted' AND updated_at <= ?",
            [$endDate]
        )->fetch()['count'];

        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
        $metrics[] = [
            'category' => 'crm',
            'name' => 'Lead Conversion Rate',
            'key' => 'lead_conversion_rate',
            'percentage' => $conversionRate,
            'period_type' => $period,
            'period_date' => $date
        ];

        return $metrics;
    }

    /**
     * Calculate communication metrics
     */
    private function calculateCommunicationMetrics(string $date, string $period): array
    {
        $startDate = $this->getPeriodStartDate($date, $period);
        $endDate = $date;

        $metrics = [];

        // Email campaigns sent
        $emailsSent = $this->query(
            "SELECT COUNT(*) as count FROM campaign_recipients WHERE sent_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'communication',
            'name' => 'Emails Sent',
            'key' => 'emails_sent',
            'count' => $emailsSent,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Email open rate
        $opens = $this->query(
            "SELECT COUNT(*) as count FROM email_tracking
             WHERE event_type = 'open' AND created_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['count'];

        $openRate = $emailsSent > 0 ? ($opens / $emailsSent) * 100 : 0;
        $metrics[] = [
            'category' => 'communication',
            'name' => 'Email Open Rate',
            'key' => 'email_open_rate',
            'percentage' => $openRate,
            'period_type' => $period,
            'period_date' => $date
        ];

        // WhatsApp messages sent
        $whatsappSent = $this->query(
            "SELECT COUNT(*) as count FROM communication_logs
             WHERE channel = 'whatsapp' AND sent_at BETWEEN ? AND ?",
            [$startDate, $endDate]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'communication',
            'name' => 'WhatsApp Messages Sent',
            'key' => 'whatsapp_messages_sent',
            'count' => $whatsappSent,
            'period_type' => $period,
            'period_date' => $date
        ];

        return $metrics;
    }

    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics(string $date, string $period): array
    {
        $startDate = $this->getPeriodStartDate($date, $period);
        $endDate = $date;

        $metrics = [];

        // Average response time (mock data - would come from actual logging)
        $avgResponseTime = 1.2; // seconds
        $metrics[] = [
            'category' => 'performance',
            'name' => 'Average Response Time',
            'key' => 'avg_response_time',
            'value' => $avgResponseTime,
            'period_type' => $period,
            'period_date' => $date
        ];

        // System uptime (mock data)
        $systemUptime = 99.8; // percentage
        $metrics[] = [
            'category' => 'performance',
            'name' => 'System Uptime',
            'key' => 'system_uptime',
            'percentage' => $systemUptime,
            'period_type' => $period,
            'period_date' => $date
        ];

        // Active sessions
        $activeSessions = $this->query(
            "SELECT COUNT(*) as count FROM user_sessions
             WHERE last_activity >= ?",
            [date('Y-m-d H:i:s', strtotime('-30 minutes'))]
        )->fetch()['count'];
        $metrics[] = [
            'category' => 'performance',
            'name' => 'Active Sessions',
            'key' => 'active_sessions',
            'count' => $activeSessions,
            'period_type' => $period,
            'period_date' => $date
        ];

        return $metrics;
    }

    /**
     * Store metric in database
     */
    private function storeMetric(array $metricData): void
    {
        // Check if metric already exists for this period
        $existing = $this->query(
            "SELECT id FROM system_analytics_metrics
             WHERE metric_key = ? AND period_type = ? AND period_date = ?",
            [$metricData['key'], $metricData['period_type'], $metricData['period_date']]
        )->fetch();

        $data = [
            'metric_category' => $metricData['category'],
            'metric_name' => $metricData['name'],
            'metric_key' => $metricData['key'],
            'period_type' => $metricData['period_type'],
            'period_date' => $metricData['period_date'],
            'calculated_at' => date('Y-m-d H:i:s')
        ];

        // Add the appropriate value field
        if (isset($metricData['value'])) {
            $data['metric_value'] = $metricData['value'];
        } elseif (isset($metricData['count'])) {
            $data['metric_count'] = $metricData['count'];
        } elseif (isset($metricData['percentage'])) {
            $data['metric_percentage'] = $metricData['percentage'];
        }

        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData(string $dashboardSlug, array $filters = []): ?array
    {
        $dashboard = $this->query(
            "SELECT * FROM analytics_dashboards WHERE dashboard_slug = ? AND is_active = 1",
            [$dashboardSlug]
        )->fetch();

        if (!$dashboard) {
            return null;
        }

        $dashboard = $dashboard->toArray();
        $layoutConfig = json_decode($dashboard['layout_config'], true);
        $widgets = [];

        foreach ($layoutConfig['widgets'] ?? [] as $widgetSlug) {
            $widgetData = $this->getWidgetData($widgetSlug, $filters);
            if ($widgetData) {
                $widgets[] = $widgetData;
            }
        }

        return [
            'dashboard' => $dashboard,
            'widgets' => $widgets,
            'filters' => $filters,
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get widget data
     */
    private function getWidgetData(string $widgetSlug, array $filters = []): ?array
    {
        $widget = $this->query(
            "SELECT * FROM dashboard_widgets_config WHERE widget_name = ? AND is_active = 1",
            [$widgetSlug]
        )->fetch();

        if (!$widget) {
            return null;
        }

        $widget = $widget->toArray();
        $queryConfig = json_decode($widget['query_config'], true);
        $chartConfig = json_decode($widget['chart_config'], true);

        // Get data based on widget configuration
        $data = $this->fetchWidgetData($queryConfig, $filters);

        return [
            'widget' => $widget,
            'data' => $data,
            'chart_config' => $chartConfig
        ];
    }

    /**
     * Fetch data for widget
     */
    private function fetchWidgetData(array $queryConfig, array $filters = []): array
    {
        $metricKey = $queryConfig['metric_key'];
        $period = $queryConfig['period'] ?? 'monthly';

        // Get the latest metric value
        $metric = $this->query(
            "SELECT * FROM system_analytics_metrics
             WHERE metric_key = ? AND period_type = ?
             ORDER BY period_date DESC LIMIT 1",
            [$metricKey, $period]
        )->fetch();

        if (!$metric) {
            return ['value' => 0, 'trend' => 'stable'];
        }

        $metric = $metric->toArray();
        $value = $metric['metric_value'] ?? $metric['metric_count'] ?? $metric['metric_percentage'] ?? 0;

        // Calculate trend (compare with previous period)
        $previousMetric = $this->query(
            "SELECT * FROM system_analytics_metrics
             WHERE metric_key = ? AND period_type = ?
             ORDER BY period_date DESC LIMIT 1 OFFSET 1",
            [$metricKey, $period]
        )->fetch();

        $trend = 'stable';
        if ($previousMetric) {
            $previousValue = $previousMetric->toArray()['metric_value'] ?? $previousMetric->toArray()['metric_count'] ?? $previousMetric->toArray()['metric_percentage'] ?? 0;

            if ($previousValue > 0) {
                $change = (($value - $previousValue) / $previousValue) * 100;
                if ($change > 5) $trend = 'up';
                elseif ($change < -5) $trend = 'down';
            }
        }

        return [
            'value' => $value,
            'trend' => $trend,
            'metric' => $metric
        ];
    }

    /**
     * Generate analytics report
     */
    public function generateReport(int $reportId, array $parameters = []): array
    {
        $report = $this->query("SELECT * FROM analytics_reports WHERE id = ?", [$reportId])->fetch();

        if (!$report) {
            return ['success' => false, 'message' => 'Report not found'];
        }

        $report = $report->toArray();

        // Start execution tracking
        $executionId = $this->startReportExecution($reportId, $parameters);

        try {
            // Generate report data based on report configuration
            $reportData = $this->generateReportData($report, $parameters);

            // Complete execution
            $this->completeReportExecution($executionId, $reportData);

            return [
                'success' => true,
                'report_id' => $reportId,
                'execution_id' => $executionId,
                'data' => $reportData,
                'message' => 'Report generated successfully'
            ];

        } catch (\Exception $e) {
            $this->failReportExecution($executionId, $e->getMessage());
            return [
                'success' => false,
                'message' => 'Report generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate report data
     */
    private function generateReportData(array $report, array $parameters): array
    {
        $dataSources = json_decode($report['data_sources'], true);
        $reportData = [];

        foreach ($dataSources as $dataSource) {
            $reportData[$dataSource] = $this->fetchReportData($dataSource, $parameters);
        }

        return [
            'report_info' => $report,
            'parameters' => $parameters,
            'data' => $reportData,
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => $this->generateReportSummary($reportData)
        ];
    }

    /**
     * Fetch data for report
     */
    private function fetchReportData(string $dataSource, array $parameters): array
    {
        // This would implement specific data fetching logic based on data source
        switch ($dataSource) {
            case 'user_metrics':
                return $this->getUserMetricsReport($parameters);
            case 'financial_metrics':
                return $this->getFinancialMetricsReport($parameters);
            case 'property_metrics':
                return $this->getPropertyMetricsReport($parameters);
            default:
                return [];
        }
    }

    /**
     * Generate report summary
     */
    private function generateReportSummary(array $reportData): array
    {
        // Generate insights and summary from report data
        $summary = [
            'total_metrics' => 0,
            'key_insights' => [],
            'trends' => []
        ];

        // Analyze data and generate insights
        foreach ($reportData as $section => $data) {
            if (is_array($data)) {
                $summary['total_metrics'] += count($data);

                // Add basic insights
                if ($section === 'user_metrics' && isset($data['active_users'])) {
                    $summary['key_insights'][] = "Active users: " . $data['active_users'];
                }
            }
        }

        return $summary;
    }

    /**
     * Check and trigger alerts
     */
    public function checkAlerts(): array
    {
        $alerts = $this->query("SELECT * FROM analytics_alerts WHERE is_active = 1")->fetchAll();
        $triggeredAlerts = [];

        foreach ($alerts as $alert) {
            $alert = $alert->toArray();

            if ($this->shouldTriggerAlert($alert)) {
                $triggeredAlerts[] = $this->triggerAlert($alert);
            }
        }

        return [
            'alerts_checked' => count($alerts),
            'alerts_triggered' => count($triggeredAlerts),
            'triggered_alerts' => $triggeredAlerts
        ];
    }

    /**
     * Check if alert should be triggered
     */
    private function shouldTriggerAlert(array $alert): bool
    {
        $metric = $this->query(
            "SELECT * FROM system_analytics_metrics
             WHERE metric_key = ? ORDER BY calculated_at DESC LIMIT 1",
            [$alert['metric_key']]
        )->fetch();

        if (!$metric) {
            return false;
        }

        $metric = $metric->toArray();
        $currentValue = $metric['metric_value'] ?? $metric['metric_count'] ?? $metric['metric_percentage'] ?? 0;

        return $this->evaluateCondition($currentValue, $alert);
    }

    /**
     * Evaluate alert condition
     */
    private function evaluateCondition(float $value, array $alert): bool
    {
        $operator = $alert['condition_operator'];
        $threshold = $alert['condition_value'];

        switch ($operator) {
            case 'gt': return $value > $threshold;
            case 'gte': return $value >= $threshold;
            case 'lt': return $value < $threshold;
            case 'lte': return $value <= $threshold;
            case 'eq': return $value == $threshold;
            case 'neq': return $value != $threshold;
            case 'between':
                $threshold2 = $alert['condition_value2'];
                return $value >= $threshold && $value <= $threshold2;
            default: return false;
        }
    }

    /**
     * Trigger alert
     */
    private function triggerAlert(array $alert): array
    {
        // Create trigger record
        $triggerId = $this->insertInto('alert_triggers', [
            'alert_id' => $alert['id'],
            'trigger_value' => $alert['condition_value'],
            'trigger_message' => "Alert triggered: {$alert['alert_name']}",
            'triggered_at' => date('Y-m-d H:i:s')
        ]);

        // Send notifications (would implement actual notification sending)
        $this->sendAlertNotifications($alert);

        // Update last triggered
        $this->query(
            "UPDATE analytics_alerts SET last_triggered = NOW() WHERE id = ?",
            [$alert['id']]
        );

        return [
            'alert_id' => $alert['id'],
            'alert_name' => $alert['alert_name'],
            'trigger_id' => $triggerId,
            'severity' => $alert['alert_severity'],
            'message' => "Alert triggered: {$alert['alert_name']}"
        ];
    }

    /**
     * Send alert notifications
     */
    private function sendAlertNotifications(array $alert): void
    {
        $channels = json_decode($alert['notification_channels'], true);
        $recipients = json_decode($alert['notification_recipients'], true);

        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    // Implement email sending
                    break;
                case 'sms':
                    // Implement SMS sending
                    break;
                case 'dashboard':
                    // Mark for dashboard display
                    break;
            }
        }
    }

    // Helper methods

    private function getPeriodStartDate(string $date, string $period): string
    {
        switch ($period) {
            case 'daily':
                return $date;
            case 'weekly':
                return date('Y-m-d', strtotime($date . ' -6 days'));
            case 'monthly':
                return date('Y-m-01', strtotime($date));
            case 'quarterly':
                $quarter = ceil(date('n', strtotime($date)) / 3);
                $year = date('Y', strtotime($date));
                return date('Y-m-d', strtotime("{$year}-" . (($quarter - 1) * 3 + 1) . "-01"));
            case 'yearly':
                return date('Y-01-01', strtotime($date));
            default:
                return date('Y-m-01', strtotime($date));
        }
    }

    private function startReportExecution(int $reportId, array $parameters): int
    {
        return $this->insertInto('report_executions', [
            'report_id' => $reportId,
            'parameters' => json_encode($parameters),
            'execution_start' => date('Y-m-d H:i:s')
        ]);
    }

    private function completeReportExecution(int $executionId, array $data): void
    {
        $this->query(
            "UPDATE report_executions SET
             execution_status = 'completed',
             execution_end = NOW(),
             result_data = ?,
             execution_duration = TIMESTAMPDIFF(SECOND, execution_start, NOW())
             WHERE id = ?",
            [json_encode($data), $executionId]
        );
    }

    private function failReportExecution(int $executionId, string $error): void
    {
        $this->query(
            "UPDATE report_executions SET
             execution_status = 'failed',
             execution_end = NOW(),
             error_message = ?,
             execution_duration = TIMESTAMPDIFF(SECOND, execution_start, NOW())
             WHERE id = ?",
            [$error, $executionId]
        );
    }

    // Placeholder methods for report data
    private function getUserMetricsReport(array $parameters): array { return []; }
    private function getFinancialMetricsReport(array $parameters): array { return []; }
    private function getPropertyMetricsReport(array $parameters): array { return []; }
}
