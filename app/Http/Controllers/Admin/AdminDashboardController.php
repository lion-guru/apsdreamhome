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
     * The index method is now inherited from AdminController.
     * We keep this file to avoid breaking existing references.
     */
}
