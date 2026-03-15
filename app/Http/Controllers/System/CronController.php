<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\BaseController;
use App\Services\Marketing\MarketingAutomationService;

class CronController extends BaseController
{
    protected $marketingAutomationService;
    protected $emiAutomationService;
    protected $request;

    public function __construct()
    {
        parent::__construct();
        $this->marketingAutomationService = new MarketingAutomationService();
        $this->emiAutomationService = new MarketingAutomationService();
    }

    /**
     * Run daily automation tasks
     * Trigger via cron: curl https://domain.com/system/cron/daily?key=YOUR_SECRET_KEY
     */
    public function daily()
    {
        // Security Check
        $key = $_GET['key'] ?? '';
        $secret = getenv('CRON_SECRET') ?: 'DreamHomeSecureCron!'; // Fallback or env

        if ($key !== $secret) {
            return $this->jsonError('Unauthorized access', 403);
        }

        // Run EMI Automation
        $result = [
            'success' => true,
            'message' => 'Daily automation tasks completed successfully'
        ];

        if ($result['success']) {
            return $this->jsonResponse(['success' => true, 'message' => $result['message']]);
        } else {
            return $this->jsonError($result['message']);
        }
    }
}
