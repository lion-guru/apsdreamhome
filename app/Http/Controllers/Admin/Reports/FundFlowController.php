<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Admin\AdminController;
use App\Services\FundFlowService;
use Exception;

class FundFlowController extends AdminController
{
    protected $fundFlowService;

    public function __construct()
    {
        parent::__construct();
        $this->fundFlowService = new FundFlowService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                $startDate = date('Y-m-01');
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                $endDate = date('Y-m-d');
            }
            if ($endDate < $startDate) {
                $tmp = $startDate;
                $startDate = $endDate;
                $endDate = $tmp;
            }
            $data = $this->fundFlowService->getFundFlow($startDate, $endDate);
            return $this->render('admin/reports/fund_flow', [
                'page_title' => 'Fund Flow Statement - APS Dream Home',
                'active_page' => 'reports',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/reports/financial');
        }
    }
}
