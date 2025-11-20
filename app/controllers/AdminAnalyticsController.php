<?php
/**
 * AdminAnalyticsController
 * Phase 2 – commission analytics dashboard endpoints.
 */

require_once __DIR__ . '/../services/CommissionService.php';

class AdminAnalyticsController
{
    private CommissionService $commissionService;

    public function __construct()
    {
        $this->commissionService = new CommissionService();
    }

    public function index(): void
    {
        $this->ensureAdmin();
        $filters = $this->defaultFilters();
        require __DIR__ . '/../views/admin/mlm_analytics.php';
    }

    public function data(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        try {
            $filters = $this->parseFilters($_GET);
            $summary = $this->commissionService->getSummary($filters);
            $levelBreakdown = $this->commissionService->getLevelBreakdown($filters);
            $topBeneficiaries = $this->commissionService->getTopBeneficiaries($filters, (int)($_GET['limit'] ?? 10));
            $topReferrers = $this->commissionService->getTopReferrers($filters, (int)($_GET['limit'] ?? 10));
            $timeline = $this->commissionService->getTimeline($filters, $_GET['group_by'] ?? 'day');

            echo json_encode([
                'success' => true,
                'filters' => $filters,
                'summary' => $summary,
                'level_breakdown' => $levelBreakdown,
                'top_beneficiaries' => $topBeneficiaries,
                'top_referrers' => $topReferrers,
                'timeline' => $timeline,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function ledger(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        try {
            $filters = $this->parseFilters($_GET);
            $limit = max(1, (int)($_GET['limit'] ?? 50));
            $offset = max(0, (int)($_GET['offset'] ?? 0));
            $records = $this->commissionService->getLedger($filters, $limit, $offset);

            echo json_encode([
                'success' => true,
                'records' => $records,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function export(): void
    {
        $this->ensureAdmin();

        $format = strtolower($_GET['format'] ?? 'csv');
        $filters = $this->parseFilters($_GET);
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
                fputcsv($out, ['No data for selected filters']);
            }
            fclose($out);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Unsupported export format',
            ]);
        }
    }

    private function ensureAdmin(): void
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASE_URL . 'admin/');
            exit();
        }
    }

    private function defaultFilters(): array
    {
        return [
            'status' => ['pending', 'approved', 'paid'],
            'date_from' => date('Y-m-01'),
            'date_to' => date('Y-m-d'),
        ];
    }

    private function parseFilters(array $input): array
    {
        $filters = [];

        if (!empty($input['status'])) {
            $filters['status'] = array_filter(array_map('trim', explode(',', (string)$input['status'])));
        }

        if (!empty($input['commission_type'])) {
            $filters['commission_type'] = array_filter(array_map('trim', explode(',', (string)$input['commission_type'])));
        }

        if (!empty($input['beneficiary_id'])) {
            $filters['beneficiary_id'] = (int)$input['beneficiary_id'];
        }

        if (!empty($input['source_user_id'])) {
            $filters['source_user_id'] = (int)$input['source_user_id'];
        }

        if (!empty($input['date_from'])) {
            $filters['date_from'] = $input['date_from'];
        }

        if (!empty($input['date_to'])) {
            $filters['date_to'] = $input['date_to'];
        }

        return $filters;
    }
}
