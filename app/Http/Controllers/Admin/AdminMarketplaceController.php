<?php

namespace App\Http\Controllers\Admin;

class AdminMarketplaceController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/marketplace/index', [
            'page_title' => 'Marketplace - APS Dream Home',
            'page_heading' => 'Marketplace'
        ]);
    }
}
