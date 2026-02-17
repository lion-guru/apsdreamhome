<?php

namespace App\Http\Controllers\Admin;

class AdminDashboardController extends AdminController
{
    /**
     * Legacy Dashboard Controller
     * Now inherits from AdminController to consolidate logic
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        parent::index();
    }

    /**
     * Alias for index (Dashboard)
     */
    public function dashboard()
    {
        parent::dashboard();
    }
}
