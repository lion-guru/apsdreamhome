<?php

namespace App\Http\Controllers\Admin;

class AdminComplianceController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/compliance/index', [
            'page_title' => 'Compliance - APS Dream Home',
            'page_heading' => 'Compliance Management'
        ]);
    }
}
