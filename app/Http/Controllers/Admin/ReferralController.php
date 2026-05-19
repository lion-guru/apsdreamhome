<?php

namespace App\Http\Controllers\Admin;

class ReferralController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'Referral Management';
        $this->data['referrals'] = [];
        $this->render('admin/referrals/index');
    }

    public function show($id)
    {
        $this->data['page_title'] = 'Referral Details';
        $this->data['referral_id'] = $id;
        $this->render('admin/referrals/show');
    }

    public function create()
    {
        $this->data['page_title'] = 'Create Referral';
        $this->render('admin/referrals/create');
    }

    public function store()
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/referrals');
    }

    public function approve($id)
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/referrals');
    }

    public function reject($id)
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/referrals');
    }
}