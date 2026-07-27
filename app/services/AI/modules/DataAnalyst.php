<?php

namespace App\Services\AI\Modules;

use PDO;

/**
 * DataAnalyst - Real data analysis, property valuation trends, market metrics.
 * Queries actual DB tables instead of returning hardcoded data.
 */
class DataAnalyst
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: \App\Core\Database\Database::getInstance();
    }

    /**
     * Analyze data from various sources
     */
    public function analyzeData(string $source, array $params = []): array
    {
        return [
            'source'          => $source,
            'timestamp'       => date('Y-m-d H:i:s'),
            'insights'        => $this->generateInsights($source, $params),
            'metrics'         => $this->calculateMetrics($source, $params),
            'recommendations' => $this->generateRecommendations($source, $params),
        ];
    }

    /**
     * Get real property market metrics from DB
     */
    public function getMarketMetrics(string $location = ''): array
    {
        try {
            $locationFilter = '';
            $params = [];
            if ($location) {
                $locationFilter = "WHERE location LIKE ? OR city LIKE ?";
                $params = ["%{$location}%", "%{$location}%"];
            }

            $sql = "SELECT
                        COUNT(*) AS total_properties,
                        COUNT(CASE WHEN status = 'available' THEN 1 END) AS available,
                        COUNT(CASE WHEN status = 'sold' THEN 1 END) AS sold,
                        AVG(COALESCE(price, plot_cost)) AS avg_price,
                        MIN(COALESCE(price, plot_cost)) AS min_price,
                        MAX(COALESCE(price, plot_cost)) AS max_price,
                        AVG(COALESCE(area_sqft, plot_size)) AS avg_area
                     FROM user_properties $locationFilter";
            $row = $this->db->fetch($sql, $params) ?: [];

            $sqlBookings = "SELECT COUNT(*) AS total, SUM(total_amount) AS total_value FROM plot_bookings WHERE status IN ('confirmed','completed')";
            $bookings = $this->db->fetch($sqlBookings, []) ?: [];

            $avgPrice = (float)($row['avg_price'] ?? 0);
            $avgArea = (float)($row['avg_area'] ?? 0);

            return [
                'total_properties'  => (int)($row['total_properties'] ?? 0),
                'available'         => (int)($row['available'] ?? 0),
                'sold'              => (int)($row['sold'] ?? 0),
                'avg_price'         => round($avgPrice),
                'min_price'         => (int)($row['min_price'] ?? 0),
                'max_price'         => (int)($row['max_price'] ?? 0),
                'avg_area_sqft'     => round($avgArea),
                'price_per_sqft'    => $avgArea > 0 ? round($avgPrice / $avgArea) : 0,
                'total_bookings'    => (int)($bookings['total'] ?? 0),
                'booking_value'     => (int)($bookings['total_value'] ?? 0),
                'absorption_rate'   => $row['total_properties'] > 0
                    ? round(((int)($row['sold'] ?? 0) / (int)$row['total_properties']) * 100, 1)
                    : 0,
            ];
        } catch (\Throwable $e) {
            error_log("DataAnalyst::getMarketMetrics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get colony performance analytics
     */
    public function getColonyPerformance(): array
    {
        try {
            $sql = "SELECT c.id, c.name, c.total_plots, c.available_plots, c.starting_price,
                           c.status, c.pipeline_stage,
                           (c.total_plots - c.available_plots) AS sold_plots,
                           CASE WHEN c.total_plots > 0
                                THEN ROUND(((c.total_plots - c.available_plots) / c.total_plots) * 100, 1)
                                ELSE 0 END AS sell_through_pct
                    FROM colonies c
                    WHERE c.total_plots > 0
                    ORDER BY sell_through_pct DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (\Throwable $e) {
            error_log("DataAnalyst::getColonyPerformance error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get lead source effectiveness
     */
    public function getSourceEffectiveness(): array
    {
        try {
            $sql = "SELECT
                        COALESCE(source, 'unknown') AS source,
                        COUNT(*) AS total_leads,
                        COUNT(CASE WHEN status = 'won' THEN 1 END) AS won,
                        COUNT(CASE WHEN status = 'lost' THEN 1 END) AS lost,
                        ROUND(AVG(score), 1) AS avg_score,
                        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) AS last_30d
                    FROM leads
                    GROUP BY source
                    ORDER BY total_leads DESC";
            $rows = $this->db->fetchAll($sql) ?: [];
            foreach ($rows as &$r) {
                $r['conversion_rate'] = $r['total_leads'] > 0
                    ? round(($r['won'] / $r['total_leads']) * 100, 1)
                    : 0;
            }
            return $rows;
        } catch (\Throwable $e) {
            error_log("DataAnalyst::getSourceEffectiveness error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get MLM commission trend (last 6 months)
     */
    public function getCommissionTrend(): array
    {
        try {
            $sql = "SELECT
                        DATE_FORMAT(created_at, '%Y-%m') AS month,
                        type,
                        COUNT(*) AS count,
                        SUM(amount) AS total_amount
                    FROM mlm_commission_ledger
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY month, type
                    ORDER BY month ASC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (\Throwable $e) {
            error_log("DataAnalyst::getCommissionTrend error: " . $e->getMessage());
            return [];
        }
    }

    private function generateInsights(string $source, array $params): array
    {
        $insights = [];
        try {
            $metrics = $this->getMarketMetrics($params['location'] ?? '');
            if (!empty($metrics)) {
                if ($metrics['absorption_rate'] > 50) {
                    $insights[] = "Strong absorption rate ({$metrics['absorption_rate']}%) indicates high demand.";
                } elseif ($metrics['absorption_rate'] < 20) {
                    $insights[] = "Low absorption rate ({$metrics['absorption_rate']}%) — consider price adjustment or marketing push.";
                }
                if ($metrics['available'] > $metrics['sold']) {
                    $insights[] = "More inventory available ({$metrics['available']}) than sold ({$metrics['sold']}). Focus on sales.";
                }
                if ($metrics['price_per_sqft'] > 0) {
                    $insights[] = "Average price per sqft: ₹{$metrics['price_per_sqft']}";
                }
            }

            $sources = $this->getSourceEffectiveness();
            if (!empty($sources)) {
                $best = $sources[0];
                $insights[] = "Top performing source: {$best['source']} with {$best['conversion_rate']}% conversion rate.";
            }
        } catch (\Throwable $e) {
            $insights[] = "Unable to generate insights: " . $e->getMessage();
        }
        return $insights ?: ["Insufficient data for analysis. Add more properties and leads to generate insights."];
    }

    private function calculateMetrics(string $source, array $params): array
    {
        $metrics = $this->getMarketMetrics($params['location'] ?? '');
        return [
            'demand_score'      => min(($metrics['absorption_rate'] ?? 0) / 100, 1.0),
            'supply_index'      => $metrics['total_properties'] > 0
                ? round(($metrics['available'] ?? 0) / $metrics['total_properties'], 2)
                : 0,
            'avg_price_sqft'    => $metrics['price_per_sqft'] ?? 0,
            'total_bookings'    => $metrics['total_bookings'] ?? 0,
            'booking_value'     => $metrics['booking_value'] ?? 0,
        ];
    }

    private function generateRecommendations(string $source, array $params): array
    {
        $recs = [];
        $metrics = $this->getMarketMetrics($params['location'] ?? '');

        if (($metrics['absorption_rate'] ?? 0) < 30) {
            $recs[] = "Consider offering EMI-based payment plans to boost absorption.";
        }
        if (($metrics['available'] ?? 0) > 20) {
            $recs[] = "High inventory — run targeted WhatsApp/email campaigns for remaining plots.";
        }
        $recs[] = "Focus on digital marketing channels for cost-effective lead generation.";
        $recs[] = "Collect and analyze customer feedback to improve offerings.";

        return $recs;
    }
}
