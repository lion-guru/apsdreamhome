<?php

namespace App\Http\Controllers\Admin;

class SocialMediaController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'Social Media Management';
        $this->data['posts'] = [];
        $this->render('admin/social-media/index');
    }

    public function schedule()
    {
        $this->data['page_title'] = 'Schedule Posts';
        $this->render('admin/social-media/schedule');
    }

    public function analytics()
    {
        $this->data['page_title'] = 'Social Analytics';
        $this->render('admin/social-media/analytics');
    }

    public function accounts()
    {
        $this->data['page_title'] = 'Connected Accounts';
        $this->render('admin/social-media/accounts');
    }

    public function store()
    {
        $this->middleware('admin.auth');
        $this->redirect('/admin/social-media');
    }
}