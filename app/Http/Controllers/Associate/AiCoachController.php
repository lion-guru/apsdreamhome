<?php
// Associate AI Coach — field sales assistant for APS associates.

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

class AiCoachController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/associate';
    }

    public function index()
    {
        $this->render('_shared/ai_coach', [
            'page_title' => 'AI Coach — APS Associate Portal',
            'page_description' => 'Your personal AI coach for field sales and commissions',
            'role' => 'associate',
            'current_page' => 'ai_coach',
        ]);
    }
}
