<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;

class ReportController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->data['active_page'] = 'reports';
        $this->data['page_title'] = 'Reports';
        
        $this->render('admin/reports/index', $this->data);
    }
}
