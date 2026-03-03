<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Property Portfolio Management Dashboard Model
 * Handles property portfolios, valuations, analytics, alerts, and reporting
 */
class Portfolio extends Model
{
    protected $table = 'property_portfolios';

    /**
     * Create a new property portfolio
     */
    public function createPortfolio(array $portfolioData): array
    {
        $portfolioRecord = [
            'portfolio_name' => $portfolioData['portfolio_name'],
            'portfolio_description' => $portfolioData['portfolio_description'] ?? null,
            'portfolio_type' => $portfolioData['portfolio_type'] ?? 'investment',
            'owner_id' => $portfolioData['owner_id'],
            'owner_type' => $portfolioData['owner_type'] ?? 'associate',
            'total_properties' => 0,
            'total_value' => 0,
            'portfolio_roi' => 0,
            'monthly_income' => 0,
            'monthly_expenses' => 0,
            'net_monthly_income' => 0,
            'is_active' => 1,
            'portfolio_goals' => json_encode($portfolioData['portfolio_goals'] ?? []),
            'risk_profile' => $portfolioData['risk_profile'] ?? 'moderate',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $portfolioId = $this->insert($portfolioRecord);

        return [
            'success' => true,
            'portfolio_id' => $portfolioId,
            'message' => 'Property portfolio created successfully'
        ];
    }

    /**
     * Add property to portfolio
     */
    public function addPropertyToPortfolio(int $portfolioId, int $propertyId, array $propertyData): array
    {
        // Check if property is already in portfolio
        $existing = $this->query(
            "SELECT id FROM portfolio_properties WHERE portfolio_id = ? AND property_id = ?",
            [$portfolioId, $propertyId]
        )->fetch();

        if ($existing) {
            return ['success' => false, 'message' => 'Property is already in this portfolio'];
        }

        $propertyRecord = [
            'portfolio_id' => $portfolioId,
            'property_id' => $propertyId,
            'acquisition_date' => $propertyData['acquisition_date'] ?? date('Y-m-d'),
            'acquisition_cost' => $propertyData['acquisition_cost'],
            'current_value' => $propertyData['current_value'] ?? $propertyData['acquisition_cost'],
            'ownership_percentage' => $propertyData['ownership_percentage'] ?? 100.00,
            'property_status' => $propertyData['property_status'] ?? 'owned',
            'rental_income' => $propertyData['rental_income'] ?? 0,
            'monthly_expenses' => $propertyData['monthly_expenses'] ?? 0,
            'net_income' => $propertyData['rental_income'] - $propertyData['monthly_expenses'],
            'last_valuation_date' => $propertyData['last_valuation_date'] ?? null,
            'next_valuation_date' => $propertyData['next_valuation_date'] ?? date('Y-m-d', strtotime('+1 year')),
            'notes' => $propertyData['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->insertInto('portfolio_properties', $propertyRecord);

        // Update portfolio totals
        $this->updatePortfolioTotals($portfolioId);

        return [
            'success' => true,
            'message' => 'Property added to portfolio successfully'
        ];
    }

    /**
     * Get portfolio dashboard data
     */
    public function getPortfolioDashboard(int $portfolioId): ?array
    {
        $portfolio = $this->find($portfolioId);
        if (!$portfolio) {
            return null;
        }

        $portfolio = $portfolio->toArray();

        $properties = $this->getPortfolioProperties($portfolioId);
        $analytics = $this->getPortfolioAnalytics($portfolioId);
        $alerts = $this->getPortfolioAlerts($portfolioId);
        $goals = $this->getPortfolioGoals($portfolioId);
        $performance = $this->calculatePortfolioPerformance($portfolioId);

        $portfolio['properties'] = $properties;
        $portfolio['analytics'] = $analytics;
        $portfolio['alerts'] = $alerts;
        $portfolio['goals'] = $goals;
        $portfolio['performance'] = $performance;
        $portfolio['portfolio_goals'] = json_decode($portfolio['portfolio_goals'], true);

        return $portfolio;
    }

    /**
     * Get portfolio properties with details
     */
    private function getPortfolioProperties(int $portfolioId): array
    {
        $properties = $this->query(
            "SELECT pp.*, p.title as property_title, p.location, p.city, p.property_type,
                    p.area, p.price, p.status, p.description,
                    pv.valuation_amount as latest_valuation,
                    pv.valuation_date as latest_valuation_date
             FROM portfolio_properties pp
             LEFT JOIN properties p ON pp.property_id = p.id
             LEFT JOIN property_valuations pv ON pp.property_id = pv.property_id
                 AND pv.valuation_date = (
                     SELECT MAX(valuation_date) FROM property_valuations
                     WHERE property_id = pp.property_id
                 )
             WHERE pp.portfolio_id = ?
             ORDER BY pp.acquisition_date DESC",
            [$portfolioId]
        )->fetchAll();

        foreach ($properties as &$property) {
            $property['valuation_history'] = $this->getPropertyValuationHistory($property['property_id']);
            $property['profit_loss'] = $property['latest_valuation'] - $property['acquisition_cost'];
            $property['roi_percentage'] = $property['acquisition_cost'] > 0
                ? (($property['latest_valuation'] - $property['acquisition_cost']) / $property['acquisition_cost']) * 100
                : 0;
        }

        return $properties;
    }

    /**
     * Get property valuation history
     */
    private function getPropertyValuationHistory(int $propertyId): array
    {
        return $this->query(
            "SELECT * FROM property_valuations
             WHERE property_id = ?
             ORDER BY valuation_date DESC LIMIT 10",
            [$propertyId]
        )->fetchAll();
    }

    /**
     * Get portfolio analytics
     */
    private function getPortfolioAnalytics(int $portfolioId): array
    {
        $analytics = $this->query(
            "SELECT * FROM portfolio_analytics
             WHERE portfolio_id = ?
             ORDER BY analytics_date DESC LIMIT 30",
            [$portfolioId]
        )->fetchAll();

        // Calculate trends
        $latest = $analytics[0] ?? null;
        $previous = $analytics[1] ?? null;

        $trends = [];
        if ($latest && $previous) {
            $trends = [
                'value_change' => $latest['total_value'] - $previous['total_value'],
                'value_change_percentage' => $previous['total_value'] > 0
                    ? (($latest['total_value'] - $previous['total_value']) / $previous['total_value']) * 100
                    : 0,
                'income_change' => $latest['monthly_income'] - $previous['monthly_income'],
                'income_change_percentage' => $previous['monthly_income'] > 0
                    ? (($latest['monthly_income'] - $previous['monthly_income']) / $previous['monthly_income']) * 100
                    : 0,
                'roi_change' => $latest['portfolio_roi'] - $previous['portfolio_roi']
            ];
        }

        return [
            'history' => $analytics,
            'latest' => $latest,
            'trends' => $trends
        ];
    }

    /**
     * Get portfolio alerts
     */
    private function getPortfolioAlerts(int $portfolioId): array
    {
        return $this->query(
            "SELECT * FROM portfolio_alerts
             WHERE portfolio_id = ? AND is_acknowledged = 0
             ORDER BY severity DESC, created_at DESC LIMIT 20",
            [$portfolioId]
        )->fetchAll();
    }

    /**
     * Get portfolio goals
     */
    private function getPortfolioGoals(int $portfolioId): array
    {
        $goals = $this->query(
            "SELECT * FROM portfolio_goals
             WHERE portfolio_id = ?
             ORDER BY priority DESC, target_date ASC",
            [$portfolioId]
        )->fetchAll();

        foreach ($goals as &$goal) {
            $goal['milestones'] = json_decode($goal['milestones'], true);
            $goal['progress_percentage'] = $goal['target_value'] > 0
                ? min(100, round(($goal['current_value'] / $goal['target_value']) * 100, 2))
                : 0;
        }

        return $goals;
    }

    /**
     * Calculate portfolio performance
     */
    private function calculatePortfolioPerformance(int $portfolioId): array
    {
        $properties = $this->getPortfolioProperties($portfolioId);

        $performance = [
            'total_properties' => count($properties),
            'total_acquisition_cost' => array_sum(array_column($properties, 'acquisition_cost')),
            'total_current_value' => array_sum(array_column($properties, 'latest_valuation')),
            'total_appreciation' => 0,
            'monthly_rental_income' => array_sum(array_column($properties, 'rental_income')),
            'monthly_expenses' => array_sum(array_column($properties, 'monthly_expenses')),
            'net_monthly_income' => 0,
            'average_roi' => 0,
            'property_types' => [],
            'location_distribution' => [],
            'status_distribution' => []
        ];

        foreach ($properties as $property) {
            $performance['total_appreciation'] += ($property['latest_valuation'] - $property['acquisition_cost']);
            $performance['net_monthly_income'] += $property['net_income'];

            // Categorize by type
            $type = $property['property_type'] ?? 'Unknown';
            $performance['property_types'][$type] = ($performance['property_types'][$type] ?? 0) + 1;

            // Categorize by location
            $location = $property['city'] ?? 'Unknown';
            $performance['location_distribution'][$location] = ($performance['location_distribution'][$location] ?? 0) + 1;

            // Categorize by status
            $status = $property['property_status'];
            $performance['status_distribution'][$status] = ($performance['status_distribution'][$status] ?? 0) + 1;
        }

        // Calculate average ROI
        if ($performance['total_acquisition_cost'] > 0) {
            $performance['average_roi'] = ($performance['total_appreciation'] / $performance['total_acquisition_cost']) * 100;
        }

        // Calculate occupancy rate
        $rentedProperties = $performance['status_distribution']['rented'] ?? 0;
        $performance['occupancy_rate'] = $performance['total_properties'] > 0
            ? round(($rentedProperties / $performance['total_properties']) * 100, 2)
            : 0;

        return $performance;
    }

    /**
     * Update portfolio totals
     */
    private function updatePortfolioTotals(int $portfolioId): void
    {
        $properties = $this->getPortfolioProperties($portfolioId);

        $totals = [
            'total_properties' => count($properties),
            'total_value' => array_sum(array_column($properties, 'latest_valuation')),
            'monthly_income' => array_sum(array_column($properties, 'rental_income')),
            'monthly_expenses' => array_sum(array_column($properties, 'monthly_expenses')),
            'net_monthly_income' => array_sum(array_column($properties, 'net_income'))
        ];

        // Calculate ROI
        $totalAcquisitionCost = array_sum(array_column($properties, 'acquisition_cost'));
        $totalAppreciation = $totals['total_value'] - $totalAcquisitionCost;
        $totals['portfolio_roi'] = $totalAcquisitionCost > 0 ? ($totalAppreciation / $totalAcquisitionCost) * 100 : 0;

        $this->update($portfolioId, $totals);
    }

    /**
     * Add property valuation
     */
    public function addPropertyValuation(array $valuationData): array
    {
        $valuationRecord = [
            'property_id' => $valuationData['property_id'],
            'valuation_date' => $valuationData['valuation_date'],
            'valuation_amount' => $valuationData['valuation_amount'],
            'valuation_method' => $valuationData['valuation_method'] ?? 'manual',
            'appraiser_name' => $valuationData['appraiser_name'] ?? null,
            'appraiser_company' => $valuationData['appraiser_company'] ?? null,
            'valuation_report_url' => $valuationData['valuation_report_url'] ?? null,
            'market_trends' => json_encode($valuationData['market_trends'] ?? []),
            'confidence_level' => $valuationData['confidence_level'] ?? 85.0,
            'notes' => $valuationData['notes'] ?? null,
            'created_by' => $valuationData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $valuationId = $this->insertInto('property_valuations', $valuationRecord);

        // Update portfolio properties with new valuation
        $this->query(
            "UPDATE portfolio_properties SET
             current_value = ?, last_valuation_date = ?, next_valuation_date = ?
             WHERE property_id = ?",
            [
                $valuationData['valuation_amount'],
                $valuationData['valuation_date'],
                date('Y-m-d', strtotime($valuationData['valuation_date'] . ' +1 year')),
                $valuationData['property_id']
            ]
        );

        // Update portfolio totals
        $portfolioProperty = $this->query(
            "SELECT portfolio_id FROM portfolio_properties WHERE property_id = ?",
            [$valuationData['property_id']]
        )->fetch();

        if ($portfolioProperty) {
            $this->updatePortfolioTotals($portfolioProperty['portfolio_id']);
        }

        return [
            'success' => true,
            'valuation_id' => $valuationId,
            'message' => 'Property valuation added successfully'
        ];
    }

    /**
     * Generate portfolio report
     */
    public function generatePortfolioReport(int $portfolioId, array $reportConfig): array
    {
        $portfolio = $this->getPortfolioDashboard($portfolioId);
        if (!$portfolio) {
            return ['success' => false, 'message' => 'Portfolio not found'];
        }

        $startDate = $reportConfig['start_date'];
        $endDate = $reportConfig['end_date'];
        $reportType = $reportConfig['report_type'] ?? 'monthly_performance';

        $reportData = [
            'portfolio_info' => $portfolio,
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => $reportConfig['generated_by'],
            'metrics' => $this->getReportMetrics($portfolioId, $startDate, $endDate),
            'charts' => $this->getReportCharts($portfolioId, $startDate, $endDate),
            'insights' => $this->getReportInsights($portfolio, $startDate, $endDate)
        ];

        // Save report
        $reportId = $this->savePortfolioReport($portfolioId, $reportConfig, $reportData);

        $reportData['report_id'] = $reportId;

        return [
            'success' => true,
            'report_id' => $reportId,
            'report_data' => $reportData,
            'message' => 'Portfolio report generated successfully'
        ];
    }

    /**
     * Get report metrics
     */
    private function getReportMetrics(int $portfolioId, string $startDate, string $endDate): array
    {
        // Get analytics data for the period
        $analytics = $this->query(
            "SELECT * FROM portfolio_analytics
             WHERE portfolio_id = ? AND analytics_date BETWEEN ? AND ?
             ORDER BY analytics_date ASC",
            [$portfolioId, $startDate, $endDate]
        )->fetchAll();

        return [
            'total_properties' => $analytics ? $analytics[0]['total_properties'] : 0,
            'portfolio_value' => $analytics ? end($analytics)['total_value'] : 0,
            'value_change' => $this->calculateValueChange($analytics),
            'monthly_income' => $analytics ? array_sum(array_column($analytics, 'monthly_income')) / count($analytics) : 0,
            'portfolio_roi' => $analytics ? end($analytics)['portfolio_roi'] : 0,
            'occupancy_rate' => $analytics ? end($analytics)['occupancy_rate'] : 0
        ];
    }

    /**
     * Get report charts data
     */
    private function getReportCharts(int $portfolioId, string $startDate, string $endDate): array
    {
        $analytics = $this->query(
            "SELECT analytics_date, total_value, monthly_income, portfolio_roi
             FROM portfolio_analytics
             WHERE portfolio_id = ? AND analytics_date BETWEEN ? AND ?
             ORDER BY analytics_date ASC",
            [$portfolioId, $startDate, $endDate]
        )->fetchAll();

        return [
            'portfolio_value_trend' => [
                'labels' => array_column($analytics, 'analytics_date'),
                'data' => array_column($analytics, 'total_value')
            ],
            'income_trend' => [
                'labels' => array_column($analytics, 'analytics_date'),
                'data' => array_column($analytics, 'monthly_income')
            ],
            'roi_trend' => [
                'labels' => array_column($analytics, 'analytics_date'),
                'data' => array_column($analytics, 'portfolio_roi')
            ]
        ];
    }

    /**
     * Get report insights
     */
    private function getReportInsights(array $portfolio, string $startDate, string $endDate): array
    {
        $insights = [];

        $performance = $portfolio['performance'];

        // Value appreciation insight
        if ($performance['total_appreciation'] > 0) {
            $insights[] = [
                'type' => 'positive',
                'title' => 'Portfolio Value Appreciation',
                'description' => "Your portfolio has appreciated by ₹" . number_format($performance['total_appreciation'], 0) .
                               " (" . number_format($performance['average_roi'], 2) . "% ROI)"
            ];
        }

        // Occupancy insight
        if ($performance['occupancy_rate'] < 90) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Occupancy Rate',
                'description' => "Current occupancy rate is " . $performance['occupancy_rate'] . "%. Consider marketing vacant properties."
            ];
        }

        // Diversification insight
        $propertyTypeCount = count($performance['property_types']);
        if ($propertyTypeCount < 3) {
            $insights[] = [
                'type' => 'info',
                'title' => 'Portfolio Diversification',
                'description' => "Your portfolio has {$propertyTypeCount} property types. Consider diversifying to reduce risk."
            ];
        }

        return $insights;
    }

    /**
     * Calculate value change
     */
    private function calculateValueChange(array $analytics): array
    {
        if (empty($analytics)) return ['absolute' => 0, 'percentage' => 0];

        $startValue = $analytics[0]['total_value'];
        $endValue = end($analytics)['total_value'];

        return [
            'absolute' => $endValue - $startValue,
            'percentage' => $startValue > 0 ? (($endValue - $startValue) / $startValue) * 100 : 0
        ];
    }

    /**
     * Save portfolio report
     */
    private function savePortfolioReport(int $portfolioId, array $config, array $data): int
    {
        $reportRecord = [
            'portfolio_id' => $portfolioId,
            'report_type' => $config['report_type'] ?? 'monthly_performance',
            'report_title' => $config['report_title'] ?? 'Portfolio Performance Report',
            'report_period_start' => $config['start_date'],
            'report_period_end' => $config['end_date'],
            'report_data' => json_encode($data),
            'report_summary' => $config['summary'] ?? null,
            'generated_by' => $config['generated_by'],
            'is_scheduled' => $config['is_scheduled'] ?? 0,
            'schedule_config' => json_encode($config['schedule_config'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insertInto('portfolio_reports', $reportRecord);
    }

    /**
     * Get market data for location
     */
    public function getMarketData(string $location, string $propertyType, string $startDate, string $endDate): array
    {
        return $this->query(
            "SELECT * FROM property_market_data
             WHERE location = ? AND property_type = ? AND data_date BETWEEN ? AND ?
             ORDER BY data_date ASC",
            [$location, $propertyType, $startDate, $endDate]
        )->fetchAll();
    }

    /**
     * Create portfolio goal
     */
    public function createPortfolioGoal(array $goalData): array
    {
        $goalRecord = [
            'portfolio_id' => $goalData['portfolio_id'],
            'goal_name' => $goalData['goal_name'],
            'goal_type' => $goalData['goal_type'],
            'target_value' => $goalData['target_value'],
            'target_date' => $goalData['target_date'],
            'priority' => $goalData['priority'] ?? 'medium',
            'milestones' => json_encode($goalData['milestones'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $goalId = $this->insertInto('portfolio_goals', $goalRecord);

        return [
            'success' => true,
            'goal_id' => $goalId,
            'message' => 'Portfolio goal created successfully'
        ];
    }

    /**
     * Update goal progress
     */
    public function updateGoalProgress(int $goalId): array
    {
        $goal = $this->query("SELECT * FROM portfolio_goals WHERE id = ?", [$goalId])->fetch();
        if (!$goal) {
            return ['success' => false, 'message' => 'Goal not found'];
        }

        $portfolio = $this->getPortfolioDashboard($goal['portfolio_id']);
        if (!$portfolio) {
            return ['success' => false, 'message' => 'Portfolio not found'];
        }

        $currentValue = 0;
        switch ($goal['goal_type']) {
            case 'total_value':
                $currentValue = $portfolio['performance']['total_current_value'];
                break;
            case 'monthly_income':
                $currentValue = $portfolio['performance']['monthly_rental_income'];
                break;
            case 'property_count':
                $currentValue = $portfolio['performance']['total_properties'];
                break;
        }

        $progressPercentage = $goal['target_value'] > 0
            ? min(100, round(($currentValue / $goal['target_value']) * 100, 2))
            : 0;

        $status = $progressPercentage >= 100 ? 'achieved' : 'active';

        $this->query(
            "UPDATE portfolio_goals SET
             current_value = ?, progress_percentage = ?, status = ?, updated_at = NOW()
             WHERE id = ?",
            [$currentValue, $progressPercentage, $status, $goalId]
        );

        return [
            'success' => true,
            'current_value' => $currentValue,
            'progress_percentage' => $progressPercentage,
            'status' => $status
        ];
    }

    /**
     * Get user portfolios
     */
    public function getUserPortfolios(int $userId, string $userType): array
    {
        return $this->query(
            "SELECT * FROM property_portfolios
             WHERE owner_id = ? AND owner_type = ? AND is_active = 1
             ORDER BY created_at DESC",
            [$userId, $userType]
        )->fetchAll();
    }
}
