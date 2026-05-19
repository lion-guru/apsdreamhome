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
}