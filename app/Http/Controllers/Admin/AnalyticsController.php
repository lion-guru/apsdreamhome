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
            $filters = $this->parseFilters($_GET);
            $summary = $this->commissionService->getSummary($filters);
            $levelBreakdown = $this->commissionService->getLevelBreakdown($filters);
            $topBeneficiaries = $this->commissionService->getTopBeneficiaries($filters, (int)($_GET['limit'] ?? 10));
            $topReferrers = $this->commissionService->getTopReferrers($filters, (int)($_GET['limit'] ?? 10));
            $timeline = $this->commissionService->getTimeline($filters, $_GET['group_by'] ?? 'day');

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
            $filters = $this->parseFilters($_GET);
            $limit = max(1, (int)($_GET['limit'] ?? 50));
            $offset = max(0, (int)($_GET['offset'] ?? 0));
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
}
