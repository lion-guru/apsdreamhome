<?php

namespace App\Http\Controllers\Admin;

class AdminPerformanceController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/performance/index', [
            'page_title' => 'Performance - APS Dream Home',
            'page_heading' => 'System Performance'
        ]);
    }
}
