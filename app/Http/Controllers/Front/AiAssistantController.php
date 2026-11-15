<?php
// Customer AI Assistant — home buyer helper (plots, EMI, RERA, site visits).

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class AiAssistantController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/customer';
    }

    public function index()
    {
        $this->render('_shared/ai_coach', [
            'page_title' => 'AI Assistant — APS Dream Home',
            'page_description' => 'Your personal plot advisor — plots, EMI, RERA and site visits in Hinglish',
            'role' => 'customer',
            'current_page' => 'ai_assistant',
        ]);
    }
}
