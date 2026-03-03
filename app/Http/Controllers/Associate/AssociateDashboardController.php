<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

/**
 * AssociateDashboardController Controller
 * Handles AssociateDashboardController related operations
 */
class AssociateDashboardController extends BaseController
{
    /**
     * Index method - main dashboard
     * @return void
     */
    public function index()
    {
        $data = [
            'page_title' => 'Associate Dashboard - APS Dream Home',
            'page_description' => 'Manage your properties, referrals, and earnings'
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

    /**
     * Profile method
     * @return void
     */
    public function profile()
    {
        // TODO: Implement profile functionality
        return $this->view('profile');
    }

    /**
     * Settings method
     * @return void
     */
    public function settings()
    {
        // TODO: Implement settings functionality
        return $this->view('settings');
    }

}
