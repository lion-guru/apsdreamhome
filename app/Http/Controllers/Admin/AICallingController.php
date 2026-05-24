<?php

namespace App\Http\Controllers\Admin;

class AICallingController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'AI Calling';
        $this->data['calls'] = [];
        $this->render('admin/ai/calling');
    }

    public function campaign()
    {
        $this->data['page_title'] = 'Calling Campaign';
        $this->render('admin/ai/calling-campaign');
    }

    public function history()
    {
        $this->data['page_title'] = 'Call History';
        $this->render('admin/ai/call-history');
    }

    public function analytics()
    {
        $this->data['page_title'] = 'Calling Analytics';
        $this->render('admin/ai/calling-analytics');
    }

    public function dashboard()
    {
        $this->data['page_title'] = 'AI Calling Dashboard';
        $this->data['calls'] = [];
        $this->render('admin/ai/calling-dashboard');
    }

    public function schedule()
    {
        $this->data['page_title'] = 'Calling Schedule';
        $this->render('admin/ai/calling-schedule');
    }

    public function sessions()
    {
        $this->data['page_title'] = 'Call Sessions';
        $this->render('admin/ai/calling-sessions');
    }

    public function extractedLeads()
    {
        $this->data['page_title'] = 'Extracted Leads';
        $this->render('admin/ai/extracted-leads');
    }

    public function training()
    {
        $this->data['page_title'] = 'AI Calling Training';
        $this->render('admin/ai/calling-training');
    }
}