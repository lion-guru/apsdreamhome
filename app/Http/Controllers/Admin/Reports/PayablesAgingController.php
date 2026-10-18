<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Admin\AdminController;
use App\Services\PayablesAgingService;
use Exception;

class PayablesAgingController extends AdminController
{
    protected $agingService;

    public function __construct()
    {
        parent::__construct();
        $this->agingService = new PayablesAgingService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');
            $vendorId = (int)($_GET['vendor_id'] ?? 0);
            $data = $this->agingService->getAging($asOfDate, $vendorId);
            $vendors = $this->db->fetchAll("SELECT id, vendor_name FROM vendors ORDER BY vendor_name") ?: [];
            return $this->render('admin/reports/payables_aging', [
                'page_title' => 'Payables Aging Report - APS Dream Home',
                'active_page' => 'reports',
                'as_of_date' => $asOfDate,
                'vendor_id' => $vendorId,
                'vendors' => $vendors,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/reports/financial');
        }
    }
}
