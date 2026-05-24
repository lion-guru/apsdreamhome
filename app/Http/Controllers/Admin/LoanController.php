<?php

namespace App\Http\Controllers\Admin;

class LoanController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $this->data['page_title'] = 'Loan Management - APS Dream Home';
            $this->render('admin/loans/index', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load loans');
            $this->redirect('/admin/dashboard');
        }
    }
}
