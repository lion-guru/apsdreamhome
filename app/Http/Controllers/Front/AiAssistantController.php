<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class AiAssistantController extends BaseController
{
    use TenantAwareTrait;
    public function index()
    {
        $this->data['page_title'] = 'AI Assistant - ' . APP_NAME;
        $this->data['meta_description'] = 'Chat with our AI assistant for property recommendations, pricing details, site visits, and answers to all your real estate questions.';
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Assistant', 'url' => BASE_URL . 'ai/assistant']
        ];

        $this->render('pages/ai/assistant');
    }
}
