<?php

namespace App\Http\Controllers\Admin;

class AdminSecurityController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/security/index', [
            'page_title' => 'Security - APS Dream Home',
            'page_heading' => 'Security Center'
        ]);
    }
}
