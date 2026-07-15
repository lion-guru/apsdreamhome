<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class DealController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function getDeals()
    {
        // Get deals
    }

    public function getDealDetail($id)
    {
        // Deal detail
    }

    public function createDeal()
    {
        // Create deal
    }

    public function updateDeal($id)
    {
        // Update deal
    }

    public function closeDeal($id)
    {
        // Close deal
    }
}