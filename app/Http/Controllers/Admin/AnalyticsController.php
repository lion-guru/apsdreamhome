<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CommissionService;
use Exception;
use Throwable;

/**
 * AnalyticsController
 * Phase 2 – commission analytics dashboard endpoints.
 */
class AnalyticsController extends AdminController
{
    private $commissionService;

    public function __construct()
    {
        parent::__construct();
        $this->commissionService = new CommissionService();
    }

    public function index(): void
    {
        $this->data['page_title'] = $this->mlSupport->translate('MLM Commission Analytics');
        $this->data['filters'] = $this->defaultFilters();
        $this->render('admin/mlm_analytics');
    }

    public function data()
    {
        try {
            $filters = $this->parseFilters($this->request->all());
            $summary = $this->commissionService->getSummary($filters);
            $levelBreakdown = $this->commissionService->getLevelBreakdown($filters);
            $limit = (int)($this->request->get('limit') ?? 10);
            $topBeneficiaries = $this->commissionService->getTopBeneficiaries($filters, $limit);
            $topReferrers = $this->commissionService->getTopReferrers($filters, $limit);
            $timeline = $this->commissionService->getTimeline($filters, $this->request->get('group_by') ?? 'day');

            return $this->jsonResponse([
                'success' => true,
                'filters' => $filters,
                'summary' => $summary,
                'level_breakdown' => $levelBreakdown,
                'top_beneficiaries' => $topBeneficiaries,
                'top_referrers' => $topReferrers,
                'timeline' => $timeline,
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function ledger()
    {
        try {
            $filters = $this->parseFilters($this->request->all());
            $limit = max(1, (int)($this->request->get('limit') ?? 50));
            $offset = max(0, (int)($this->request->get('offset') ?? 0));
            $records = $this->commissionService->getLedger($filters, $limit, $offset);

            return $this->jsonResponse([
                'success' => true,
                'records' => $records,
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function export(): void
    {
        $format = strtolower($this->request->get('format') ?? 'csv');
        $filters = $this->parseFilters($this->request->all());
        $rows = $this->commissionService->exportLedger($filters);

        if ($format === 'csv') {
            $filename = 'commission_ledger_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=' . $filename);

            $out = fopen('php://output', 'w');
            if (!empty($rows)) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            } else {
                fputcsv($out, ['message']);
                fputcsv($out, ['No records found for the given filters.']);
            }
            fclose($out);
            exit;
        }
    }

    private function defaultFilters(): array
    {
        return [
            'status' => null,
            'date_from' => date('Y-m-01'), // start of current month
            'date_to' => date('Y-m-d'),
            'beneficiary_id' => null,
            'level' => null,
        ];
    }

    private function parseFilters(array $input): array
    {
        return [
            'status' => !empty($input['status']) ? $input['status'] : null,
            'date_from' => !empty($input['date_from']) ? $input['date_from'] : null,
            'date_to' => !empty($input['date_to']) ? $input['date_to'] : null,
            'beneficiary_id' => !empty($input['beneficiary_id']) ? (int)$input['beneficiary_id'] : null,
            'level' => isset($input['level']) && $input['level'] !== '' ? (int)$input['level'] : null,
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
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\AnalyticsController.php

function getMetrics()
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
function getChartData()
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
function getPerformanceData()
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
function getRecentActivity()
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
function getUserEngagement()
    {
        return [
            'daily_active_users' => 342,
            'page_views' => 8947,
            'avg_session_time' => '4m 32s',
            'bounce_rate' => 32.4,
            'conversion_rate' => 5.8
        ];
    }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Http\Controllers\Api\AnalyticsController.php

function calculateTrend($current, $previous)
    {
        if (!$previous) return $current ? 100 : 0;
        return \round((($current - $previous) / $previous) * 100, 1);
    }