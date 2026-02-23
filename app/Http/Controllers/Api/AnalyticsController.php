<?php

namespace App\Http\Controllers\Api;

use \Exception;

class AnalyticsController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:admin');
    }

    /**
     * Get alert analytics
     */
    public function alertAnalytics()
    {
        try {
            $range = $this->request()->input('range', '24h');
            switch ($range) {
                case '7d':
                    $interval = '7 DAY';
                    $groupBy = 'DATE(created_at)';
                    break;
                case '30d':
                    $interval = '30 DAY';
                    $groupBy = 'DATE(created_at)';
                    break;
                default: // 24h
                    $interval = '24 HOUR';
                    $groupBy = 'HOUR(created_at)';
                    break;
            }

            $alertModel = $this->model('SystemAlert');

            // Summary
            $summary = $alertModel->getSummary($interval);

            // Trends (previous period)
            $prevSummary = $alertModel->getPreviousSummary($interval);

            // Calculate trends
            $trends = [
                'critical' => $this->calculateTrend($summary['critical'], $prevSummary['critical']),
                'warning' => $this->calculateTrend($summary['warning'], $prevSummary['warning']),
                'resolution' => $this->calculateTrend($prevSummary['avg_resolution'], $summary['avg_resolution']),
                'alertRate' => $this->calculateTrend($summary['hourly_rate'], $prevSummary['hourly_rate'])
            ];

            // Chart data
            $chartData = $alertModel->getChartData($groupBy, $interval);

            // Distribution
            $distribution = $alertModel->getDistribution($interval);

            return $this->jsonSuccess([
                'summary' => $summary,
                'trends' => $trends,
                'chart' => [
                    'labels' => \array_column($chartData, 'label'),
                    'critical' => \array_map('intval', \array_column($chartData, 'critical')),
                    'warning' => \array_map('intval', \array_column($chartData, 'warning'))
                ],
                'distribution' => $distribution,
                'resolution_time' => $summary['avg_resolution']
            ]);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    private function calculateTrend($current, $previous)
    {
        if (!$previous) return $current ? 100 : 0;
        return \round((($current - $previous) / $previous) * 100, 1);
    }
}
