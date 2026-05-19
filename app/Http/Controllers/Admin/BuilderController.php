<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class BuilderController extends BaseController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/builders/index', []);
    }
}