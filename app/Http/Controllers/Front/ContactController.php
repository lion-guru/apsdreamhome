<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;

class ContactController extends PageController
{
    public function contact()
    {
        return parent::contact();
    }

    public function serviceInterest()
    {
        return parent::serviceInterest();
    }

    public function handleQuickInquiry()
    {
        return parent::handleQuickInquiry();
    }

    public function scheduleMeeting()
    {
        return parent::scheduleMeeting();
    }

    public function handleScheduleMeeting()
    {
        return parent::handleScheduleMeeting();
    }

    public function support()
    {
        return parent::support();
    }
}