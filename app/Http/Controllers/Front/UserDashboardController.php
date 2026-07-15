<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;

class UserDashboardController extends PageController
{
    public function userSavedSearches()
    {
        return parent::userSavedSearches();
    }

    public function userNotifications()
    {
        return parent::userNotifications();
    }

    public function userInvestments()
    {
        return parent::userInvestments();
    }

    public function userEditProfile()
    {
        return parent::userEditProfile();
    }
}