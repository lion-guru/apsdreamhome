<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;

/**
 * DashboardController for Customer users
 * Handles customer dashboard operations
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
            'page_title' => 'Customer Dashboard - APS Dream Home',
            'page_description' => 'View your properties, bookings and profile'
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
