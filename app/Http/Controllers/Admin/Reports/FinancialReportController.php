<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Admin\AdminController;

class FinancialReportController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $this->data['page_title'] = 'Financial Reports - APS Dream Home';
            $this->render('admin/reports/financial', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load financial reports');
            $this->redirect('/admin/dashboard');
        }
    }
}
