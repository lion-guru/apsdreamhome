<?php

namespace App\Http\Controllers\Admin;

class AdminAIController extends AdminController
{
    public function training()
    {
        $this->requireAdmin();
        return $this->render('admin/ai/training', [
            'page_title' => 'AI Training - APS Dream Home',
            'page_heading' => 'AI Chatbot Training'
        ]);
    }
}
