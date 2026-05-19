<?php

namespace App\Http\Controllers\Admin;

class AIChatbotController extends AdminController
{
    public function index()
    {
        $this->data['page_title'] = 'AI Chatbot';
        $this->data['conversations'] = [];
        $this->render('admin/ai/chatbot');
    }

    public function settings()
    {
        $this->data['page_title'] = 'Chatbot Settings';
        $this->render('admin/ai/chatbot-settings');
    }

    public function analytics()
    {
        $this->data['page_title'] = 'Chatbot Analytics';
        $this->render('admin/ai/chatbot-analytics');
    }

    public function train()
    {
        $this->data['page_title'] = 'Train Chatbot';
        $this->render('admin/ai/chatbot-train');
    }
}