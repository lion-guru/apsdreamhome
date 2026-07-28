<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class AIController extends PageController
{
    use TenantAwareTrait;
    public function aiChatbotPage()
    {
        return parent::aiChatbotPage();
    }

    public function aiValuation()
    {
        return parent::aiValuation();
    }

    public function userAiSuggestions()
    {
        return parent::userAiSuggestions();
    }

    public function whatsappChat()
    {
        return parent::whatsappChat();
    }

    public function virtualTour()
    {
        return parent::virtualTour();
    }
}