<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ReceivablesAgingService;
use Exception;

class ReceivablesAgingController extends AdminController
{
    protected $agingService;

    public function __construct()
    {
        parent::__construct();
        $this->agingService = new ReceivablesAgingService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');
            $colonyId = (int)($_GET['colony_id'] ?? 0);
            $data = $this->agingService->getAging($asOfDate, $colonyId);
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies ORDER BY name") ?: [];
            return $this->render('admin/reports/receivables_aging', [
                'page_title' => 'Receivables Aging Report - APS Dream Home',
                'active_page' => 'reports',
                'as_of_date' => $asOfDate,
                'colony_id' => $colonyId,
                'colonies' => $colonies,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/reports/financial');
        }
    }
}
