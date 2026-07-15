<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class ReferralController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function dashboard()
    {
        // Referral dashboard
    }

    public function share()
    {
        // Track share
    }
}