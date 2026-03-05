<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Models;

use App\Core\Model;

class SystemAnalytics extends Model
{
    /**
     * Get system performance metrics
     */
    public function getSystemMetrics(string $period = 'daily'): array
    {
        $startDate = $this->getPeriodStartDate(date('Y-m-d'), $period);
        
        $metrics = [
            'cpu_usage' => $this->getCpuUsage($startDate),
            'memory_usage' => $this->getMemoryUsage($startDate),
            'disk_usage' => $this->getDiskUsage($startDate),
            'database_performance' => $this->getDatabasePerformance($startDate),
            'api_response_times' => $this->getApiResponseTimes($startDate),
            'error_rates' => $this->getErrorRates($startDate),
            'user_activity' => $this->getUserActivity($startDate)
        ];
        
        return $metrics;
    }
    
    /**
     * Get user engagement metrics
     */
    public function getUserEngagementMetrics(string $period = 'daily'): array
    {
        $startDate = $this->getPeriodStartDate(date('Y-m-d'), $period);
        
        return [
            'active_users' => $this->getActiveUsers($startDate),
            'page_views' => $this->getPageViews($startDate),
            'session_duration' => $this->getAverageSessionDuration($startDate),
            'bounce_rate' => $this->getBounceRate($startDate),
            'conversion_rate' => $this->getConversionRate($startDate),
            'user_retention' => $this->getUserRetention($startDate)
        ];
    }
    
    /**
     * Get business metrics
     */
    public function getBusinessMetrics(string $period = 'daily'): array
    {
        $startDate = $this->getPeriodStartDate(date('Y-m-d'), $period);
        
        return [
            'revenue' => $this->getRevenue($startDate),
            'leads_generated' => $this->getLeadsGenerated($startDate),
            'properties_listed' => $this->getPropertiesListed($startDate),
            'properties_sold' => $this->getPropertiesSold($startDate),
            'commission_earned' => $this->getCommissionEarned($startDate),
            'customer_satisfaction' => $this->getCustomerSatisfaction($startDate)
        ];
    }
    
    /**
     * Generate comprehensive report
     */
    public function generateReport(string $reportType, array $parameters = []): array
    {
        switch ($reportType) {
            case 'system_performance':
                return $this->getSystemMetrics($parameters['period'] ?? 'daily');
            case 'user_engagement':
                return $this->getUserEngagementMetrics($parameters['period'] ?? 'daily');
            case 'business_metrics':
                return $this->getBusinessMetrics($parameters['period'] ?? 'daily');
            default:
                return [];
        }
    }
    
    /**
     * Check system alerts
     */
    public function checkSystemAlerts(): array
    {
        $alerts = $this->query("SELECT * FROM system_alerts WHERE active = 1")->fetchAll();
        $triggeredAlerts = [];
        
        foreach ($alerts as $alert) {
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
    
    // Helper methods
    private function getCpuUsage(string $startDate): float
    {
        $result = $this->query(
            "SELECT AVG(cpu_usage) as avg_cpu FROM system_metrics WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['avg_cpu'] : 0;
    }
    
    private function getMemoryUsage(string $startDate): float
    {
        $result = $this->query(
            "SELECT AVG(memory_usage) as avg_memory FROM system_metrics WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['avg_memory'] : 0;
    }
    
    private function getDiskUsage(string $startDate): float
    {
        $result = $this->query(
            "SELECT AVG(disk_usage) as avg_disk FROM system_metrics WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['avg_disk'] : 0;
    }
    
    private function getDatabasePerformance(string $startDate): array
    {
        $result = $this->query(
            "SELECT AVG(query_time) as avg_time, COUNT(*) as query_count FROM database_metrics WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return [
            'avg_query_time' => $result ? (float)$result['avg_time'] : 0,
            'query_count' => $result ? (int)$result['query_count'] : 0
        ];
    }
    
    private function getApiResponseTimes(string $startDate): array
    {
        $result = $this->query(
            "SELECT AVG(response_time) as avg_time, MIN(response_time) as min_time, MAX(response_time) as max_time FROM api_metrics WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return [
            'avg_response_time' => $result ? (float)$result['avg_time'] : 0,
            'min_response_time' => $result ? (float)$result['min_time'] : 0,
            'max_response_time' => $result ? (float)$result['max_time'] : 0
        ];
    }
    
    private function getErrorRates(string $startDate): array
    {
        $result = $this->query(
            "SELECT COUNT(*) as total_errors, COUNT(DISTINCT error_type) as error_types FROM error_logs WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return [
            'total_errors' => $result ? (int)$result['total_errors'] : 0,
            'error_types' => $result ? (int)$result['error_types'] : 0
        ];
    }
    
    private function getUserActivity(string $startDate): array
    {
        $result = $this->query(
            "SELECT COUNT(*) as active_sessions, COUNT(DISTINCT user_id) as unique_users FROM user_sessions WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return [
            'active_sessions' => $result ? (int)$result['active_sessions'] : 0,
            'unique_users' => $result ? (int)$result['unique_users'] : 0
        ];
    }
    
    private function getActiveUsers(string $startDate): int
    {
        $result = $this->query(
            "SELECT COUNT(DISTINCT user_id) as active_users FROM user_activity WHERE activity_date >= ?",
            [$startDate]
        )->fetch();
        return $result ? (int)$result['active_users'] : 0;
    }
    
    private function getPageViews(string $startDate): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as page_views FROM page_views WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (int)$result['page_views'] : 0;
    }
    
    private function getAverageSessionDuration(string $startDate): float
    {
        $result = $this->query(
            "SELECT AVG(session_duration) as avg_duration FROM user_sessions WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['avg_duration'] : 0;
    }
    
    private function getBounceRate(string $startDate): float
    {
        $result = $this->query(
            "SELECT (SUM(CASE WHEN page_views = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as bounce_rate FROM user_sessions WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['bounce_rate'] : 0;
    }
    
    private function getConversionRate(string $startDate): float
    {
        $result = $this->query(
            "SELECT (SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as conversion_rate FROM user_sessions WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['conversion_rate'] : 0;
    }
    
    private function getUserRetention(string $startDate): float
    {
        $result = $this->query(
            "SELECT (SUM(CASE WHEN return_visits > 0 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as retention_rate FROM user_activity WHERE activity_date >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['retention_rate'] : 0;
    }
    
    private function getRevenue(string $startDate): float
    {
        $result = $this->query(
            "SELECT SUM(amount) as total_revenue FROM transactions WHERE transaction_date >= ? AND status = 'completed'",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['total_revenue'] : 0;
    }
    
    private function getLeadsGenerated(string $startDate): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as leads FROM leads WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (int)$result['leads'] : 0;
    }
    
    private function getPropertiesListed(string $startDate): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as properties FROM properties WHERE listed_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (int)$result['properties'] : 0;
    }
    
    private function getPropertiesSold(string $startDate): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as sold_properties FROM properties WHERE sold_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (int)$result['sold_properties'] : 0;
    }
    
    private function getCommissionEarned(string $startDate): float
    {
        $result = $this->query(
            "SELECT SUM(commission_amount) as total_commission FROM commissions WHERE earned_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['total_commission'] : 0;
    }
    
    private function getCustomerSatisfaction(string $startDate): float
    {
        $result = $this->query(
            "SELECT AVG(rating) as avg_satisfaction FROM customer_feedback WHERE created_at >= ?",
            [$startDate]
        )->fetch();
        return $result ? (float)$result['avg_satisfaction'] : 0;
    }
    
    private function shouldTriggerAlert(array $alert): bool
    {
        $metric = $this->query(
            "SELECT * FROM system_analytics_metrics WHERE metric_name = ?",
            [$alert['metric_key']]
        )->fetch();
        
        if (!$metric) {
            return false;
        }
        
        $metric = $metric->toArray();
        $currentValue = $metric['metric_value'] ?? $metric['metric_count'] ?? $metric['metric_percentage'] ?? 0;
        
        return $this->evaluateCondition($currentValue, $alert);
    }
    
    private function evaluateCondition(float $value, array $alert): bool
    {
        $operator = $alert['condition_operator'];
        $threshold = $alert['condition_value'];
        
        switch ($operator) {
            case 'gt':
                return $value > $threshold;
            case 'gte':
                return $value >= $threshold;
            case 'lt':
                return $value < $threshold;
            case 'lte':
                return $value <= $threshold;
            case 'eq':
                return $value == $threshold;
            case 'neq':
                return $value != $threshold;
            case 'between':
                $threshold2 = $alert['condition_value2'];
                return $value >= $threshold && $value <= $threshold2;
            default:
                return false;
        }
    }
    
    private function triggerAlert(array $alert): array
    {
        // Create trigger record
        $triggerId = $this->insertInto('alert_triggers', [
            'alert_id' => $alert['id'],
            'trigger_value' => $alert['condition_value'],
            'trigger_message' => "Alert triggered: {$alert['alert_name']}",
            'triggered_at' => date('Y-m-d H:i:s')
        ]);
        
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
    
    private function getPeriodStartDate(string $date, string $period): string
    {
        switch ($period) {
            case 'daily':
                return $date;
            case 'weekly':
                return date('Y-m-d', strtotime($date . ' -7 days'));
            case 'monthly':
                return date('Y-m-d', strtotime($date));
            case 'quarterly':
                $quarter = ceil(date('n', strtotime($date)) / 3);
                $year = date('Y', strtotime($date));
                return date('Y-m-d', strtotime("{$year}-" . (($quarter - 1) * 3 + 1) . "-01"));
            case 'yearly':
                return date('Y-m-d', strtotime($date));
            default:
                return date('Y-m-d', strtotime($date));
        }
    }
}
