<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use Exception;

/**
 * ColonyAnalyticsService — Revenue, cost, profit analysis per colony.
 */
class ColonyAnalyticsService
{
    /** @var Database */
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            $this->db = null;
        }
    }

    /**
     * Get full analytics for a colony.
     */
    public function getColonyAnalytics(int $colonyId): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            // Plot stats
            $plotStats = $this->db->fetchOne("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked,
                    SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                    SUM(CASE WHEN status = 'hold' THEN 1 ELSE 0 END) as hold,
                    SUM(area_sqft) as total_area,
                    SUM(total_price) as total_revenue,
                    SUM(negotiated_price) as negotiated_total,
                    AVG(price_per_sqft) as avg_price_sqft,
                    MIN(total_price) as min_price,
                    MAX(total_price) as max_price
                FROM plots WHERE colony_id = ?
            ", [$colonyId]) ?: [];

            // Development costs breakdown
            $devCosts = $this->db->fetchAll("
                SELECT
                    cost_type,
                    COUNT(*) as entries,
                    SUM(amount) as gross_amount,
                    SUM(gst_amount) as total_gst,
                    SUM(tds_amount) as total_tds,
                    SUM(paid_amount) as paid,
                    SUM(balance_amount) as balance
                FROM colony_development_costs
                WHERE colony_id = ?
                GROUP BY cost_type
                ORDER BY gross_amount DESC
            ", [$colonyId]) ?: [];

            $devTotal = $this->db->fetchOne("
                SELECT
                    SUM(amount) as gross,
                    SUM(gst_amount) as gst,
                    SUM(tds_amount) as tds,
                    SUM(paid_amount) as paid,
                    SUM(balance_amount) as balance
                FROM colony_development_costs WHERE colony_id = ?
            ", [$colonyId]) ?: [];

            // Land cost
            $landCost = $colony['estimated_land_cost'] ?? 0;

            // Revenue calculations
            $totalRevenue = floatval($plotStats['total_revenue'] ?? 0);
            $totalDevCost = floatval($devTotal['gross'] ?? 0) + floatval($devTotal['gst'] ?? 0);
            $totalCost    = $landCost + $totalDevCost;
            $grossProfit  = $totalRevenue - $totalCost;
            $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

            // Plot type breakdown
            $typeBreakdown = $this->db->fetchAll("
                SELECT
                    plot_type,
                    COUNT(*) as count,
                    SUM(area_sqft) as area,
                    SUM(total_price) as value,
                    AVG(price_per_sqft) as avg_price
                FROM plots WHERE colony_id = ?
                GROUP BY plot_type
            ", [$colonyId]) ?: [];

            // Block breakdown
            $blockBreakdown = $this->db->fetchAll("
                SELECT
                    block,
                    COUNT(*) as count,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                    SUM(total_price) as value,
                    AVG(price_per_sqft) as avg_price
                FROM plots WHERE colony_id = ?
                GROUP BY block
                ORDER BY block
            ", [$colonyId]) ?: [];

            // Sales velocity (bookings in last 30 days)
            $salesVelocity = $this->db->fetchOne("
                SELECT COUNT(*) as booked_30d
                FROM plots
                WHERE colony_id = ? AND status IN ('booked', 'sold')
                AND booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ", [$colonyId]) ?: ['booked_30d' => 0];

            // RERA milestones progress
            $milestoneProgress = $this->db->fetchOne("
                SELECT
                    COALESCE(COUNT(*), 0) as total,
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as done,
                    COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) as `active`,
                    COALESCE(SUM(CASE WHEN status = 'delayed' THEN 1 ELSE 0 END), 0) as delayed_count
                FROM rera_milestones
                WHERE project_id = (SELECT rera_project_id FROM colonies WHERE id = ?)
            ", [$colonyId]) ?: ['total' => 0, 'done' => 0, 'active' => 0, 'delayed' => 0];

            // Colony ROI projection
            $roiProjection = $this->calculateROIProjection($colonyId, $landCost, $totalDevCost, $totalRevenue);

            return [
                'success'          => true,
                'colony'           => $colony,
                'plot_stats'       => $plotStats,
                'dev_costs'        => $devCosts,
                'dev_total'        => $devTotal,
                'land_cost'        => $landCost,
                'total_cost'       => $totalCost,
                'total_revenue'    => $totalRevenue,
                'gross_profit'     => $grossProfit,
                'profit_margin'    => round($profitMargin, 1),
                'type_breakdown'   => $typeBreakdown,
                'block_breakdown'  => $blockBreakdown,
                'sales_velocity'   => $salesVelocity,
                'milestone_progress' => $milestoneProgress,
                'roi_projection'   => $roiProjection,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get cross-colony comparison analytics.
     */
    public function getCrossColonyComparison(): array
    {
        try {
            $colonies = $this->db->fetchAll("
                SELECT
                    c.id,
                    c.name,
                    c.pipeline_stage,
                    c.total_area_acres,
                    c.estimated_land_cost,
                    c.starting_price,
                    c.total_plots,
                    c.available_plots,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id AND status = 'sold') as sold_plots,
                    (SELECT SUM(total_price) FROM plots WHERE colony_id = c.id) as total_revenue,
                    (SELECT SUM(amount) FROM colony_development_costs WHERE colony_id = c.id) as dev_cost,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id) as plot_count,
                    (SELECT COUNT(*) FROM plots WHERE colony_id = c.id AND total_price > 0) as priced_count
                FROM colonies c
                WHERE c.is_active = 1
                ORDER BY c.name
            ") ?: [];

            // Summary totals
            $totalRevenue = 0;
            $totalDevCost = 0;
            $totalPlots   = 0;
            $totalSold    = 0;

            foreach ($colonies as &$c) {
                $landCost  = floatval($c['estimated_land_cost'] ?? 0);
                $devCost   = floatval($c['dev_cost'] ?? 0);
                $revenue   = floatval($c['total_revenue'] ?? 0);
                $profit    = $revenue - $landCost - $devCost;
                $margin    = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

                $c['land_cost']     = $landCost;
                $c['total_cost']    = $landCost + $devCost;
                $c['gross_profit']  = $profit;
                $c['profit_margin'] = round($margin, 1);
                $c['occupancy_pct'] = $c['plot_count'] > 0
                    ? round((($c['plot_count'] - ($c['available_plots'] ?? 0)) / $c['plot_count']) * 100)
                    : 0;

                $totalRevenue += $revenue;
                $totalDevCost += $devCost;
                $totalPlots   += $c['plot_count'];
                $totalSold    += $c['sold_plots'];
            }

            $totalLandCost = array_sum(array_column($colonies, 'estimated_land_cost'));

            return [
                'success'       => true,
                'colonies'      => $colonies,
                'summary'       => [
                    'total_colonies'  => count($colonies),
                    'total_revenue'   => $totalRevenue,
                    'total_land_cost' => $totalLandCost,
                    'total_dev_cost'  => $totalDevCost,
                    'total_cost'      => $totalLandCost + $totalDevCost,
                    'total_profit'    => $totalRevenue - $totalLandCost - $totalDevCost,
                    'total_plots'     => $totalPlots,
                    'total_sold'      => $totalSold,
                    'overall_margin'  => $totalRevenue > 0
                        ? round((($totalRevenue - $totalLandCost - $totalDevCost) / $totalRevenue) * 100, 1)
                        : 0,
                ],
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate ROI projection for a colony.
     */
    private function calculateROIProjection(int $colonyId, float $landCost, float $devCost, float $revenue): array
    {
        try {
            $totalCost = $landCost + $devCost;
            $profit    = $revenue - $totalCost;
            $roi       = $totalCost > 0 ? ($profit / $totalCost) * 100 : 0;

            // Revenue realization
            $pricedPlots = $this->db->fetchOne(
                "SELECT COUNT(*) as total, SUM(total_price) as value FROM plots WHERE colony_id = ? AND total_price > 0",
                [$colonyId]
            ) ?: ['total' => 0, 'value' => 0];

            $totalPlotCount = $this->db->fetchOne(
                "SELECT COUNT(*) as c FROM plots WHERE colony_id = ?",
                [$colonyId]
            ) ?: ['c' => 0];

            $potentialRevenue = floatval($pricedPlots['value'] ?? 0);
            $realizedRevenue  = $revenue;
            $realizationPct   = $potentialRevenue > 0 ? ($realizedRevenue / $potentialRevenue) * 100 : 0;

            // Break-even
            $breakEvenPlots = $totalCost > 0 && floatval($pricedPlots['value'] ?? 0) > 0
                ? ceil($totalCost / (floatval($pricedPlots['value'] ?? 1) / max($totalPlotCount['c'] ?? 1, 1)))
                : 0;

            return [
                'total_cost'         => $totalCost,
                'potential_revenue'  => $potentialRevenue,
                'realized_revenue'   => $realizedRevenue,
                'profit'             => $profit,
                'roi_pct'            => round($roi, 1),
                'realization_pct'    => round($realizationPct, 1),
                'break_even_plots'   => $breakEvenPlots,
            ];
        } catch (Exception $e) {
            return [];
        }
    }
}
