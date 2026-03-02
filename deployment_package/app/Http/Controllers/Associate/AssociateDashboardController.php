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
     * Dashboard method
     * @return void
     */
    public function dashboard()
    {
        // TODO: Implement dashboard functionality
        return $this->view('dashboard');
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
