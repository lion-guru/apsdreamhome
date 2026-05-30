<?php

namespace App\Http\Controllers\Admin;

class AgentController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/agents/index', []);
    }
}