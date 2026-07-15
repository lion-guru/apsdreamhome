<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class LeadController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function index()
    {
        // List leads
    }

    public function submit()
    {
        // Submit lead
    }

    public function inquiry()
    {
        // Submit inquiry
    }

    public function listInquiries()
    {
        // List inquiries
    }

    public function changeStatus($id)
    {
        // Change lead status
    }

    public function scheduleFollowup($id)
    {
        // Schedule followup
    }

    public function addActivity($id)
    {
        // Add activity
    }

    public function convert($id)
    {
        // Convert lead
    }

    public function markLost($id)
    {
        // Mark lost
    }

    public function statistics()
    {
        // Statistics
    }

    public function logCall($id)
    {
        // Log call
    }
}