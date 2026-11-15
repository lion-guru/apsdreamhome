<?php
// Agent AI Coach — personal coach for channel partners (external APS agents).

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AiCoachController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/agent';
    }

    public function index()
    {
        $this->render('_shared/ai_coach', [
            'page_title' => 'AI Coach — APS Agent Portal',
            'page_description' => 'Your personal AI coach for pipelines, calls and commission',
            'role' => 'agent',
            'current_page' => 'ai_coach',
        ]);
    }
}
