<?php

namespace App\Http\Controllers\Admin;

class AdminDeveloperController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/developer/index', [
            'page_title' => 'Developer Portal - APS Dream Home',
            'page_heading' => 'Developer Portal'
        ]);
    }
}
