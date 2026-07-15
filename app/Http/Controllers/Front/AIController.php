<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;

class AIController extends PageController
{
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