<?php

namespace App\Http\Controllers\Admin;

class AIAnalyticsController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'AI Analytics';
        $this->data['analytics'] = [];
        $this->render('admin/ai/analytics');
    }

    public function reports()
    {
        $this->data['page_title'] = 'AI Reports';
        $this->render('admin/ai/reports');
    }

    public function insights()
    {
        $this->data['page_title'] = 'AI Insights';
        $this->render('admin/ai/insights');
    }
}