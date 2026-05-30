<?php

namespace App\Http\Controllers\Admin;

class BuilderController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/builders/index', []);
    }
}