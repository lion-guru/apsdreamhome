<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;

/**
 * DashboardController for Employee users
 * Handles employee dashboard operations
 */
class DashboardController extends BaseController
{
    /**
     * Index method - main dashboard
     * @return void
     */
    public function index()
    {
        $data = [
            'page_title' => 'Employee Dashboard - APS Dream Home',
            'page_description' => 'Manage your daily operations and tasks'
        ];
        return $this->render('dashboard/index', $data, 'layouts/base');
    }

    /**
     * Dashboard method
     * @return void
     */
    public function dashboard()
    {
        return $this->index();
    }
}
