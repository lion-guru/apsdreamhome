<?php

/**
 * Master Cron Job Script for APS Dream Home
 * Automates EMI reminders, Lead follow-ups, and other scheduled tasks.
 * Command to run via cPanel/Crontab (Every Hour): php /path/to/apsdreamhome/cron.php
 */

// Define base path and load autoloader
define('BASE_PATH', dirname(__DIR__, 3));
require_once BASE_PATH . '/vendor/autoload.php';

// Add backward compatibility alias for Database safely
if (!class_exists('App\Core\Database', false)) {
    class_alias('App\Core\Database\Database', 'App\Core\Database');
}

use App\Services\EMIAutomationService;
use App\Services\LeadFollowUpService;

error_log('APS Dream Home Cron: Execution started at ' . date('Y-m-d H:i:s'));

echo "Starting APS Dream Home Background Automation...\n";

// 1. EMI Automation (Status Updates, Reminders, Defaults, Reports)
try {
    echo "-> Running EMI Automation...\n";
    $emiService = new EMIAutomationService();
    $emiResults = $emiService->runAll();
    echo "   [OK] EMI automation processed successfully.\n";
} catch (\Exception $e) {
    echo "   [ERROR] EMI Automation Failed: " . $e->getMessage() . "\n";
    error_log("Cron EMI Automation Error: " . $e->getMessage());
}

// 2. Lead Follow-ups
try {
    echo "-> Running Lead Follow-ups...\n";
    $leadService = new LeadFollowUpService();

    // Abandoned registration follow-ups
    $regResult = $leadService->sendFollowUpForIncompleteRegistrations();
    echo "   [OK] Incomplete Reg: " . ($regResult['message'] ?? 'Done') . "\n";

    // General new lead follow-ups
    $leadResult = $leadService->sendFollowUpForNewLeads();
    echo "   [OK] New Leads: " . ($leadResult['message'] ?? 'Done') . "\n";
} catch (\Exception $e) {
    echo "   [ERROR] Lead Follow-ups Failed: " . $e->getMessage() . "\n";
    error_log("Cron Lead Follow-up Error: " . $e->getMessage());
}

// 3. AI Property Aggregator (Fetch external properties automatically)
try {
    echo "-> Running AI Property Aggregator...\n";
    $aggregatorService = new \App\Services\AIAggregatorService();
    $aggResults = $aggregatorService->runAggregator(2); // Fetch 2 properties per cron run to avoid rate limits
    echo "   [OK] AI Aggregator: " . $aggResults['success'] . " properties fetched and spun.\n";
    if ($aggResults['failed'] > 0) {
        foreach ($aggResults['logs'] as $log) {
            if (strpos($log, 'Failed:') === 0) {
                echo "   [DEBUG] " . $log . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "   [ERROR] AI Aggregator Failed: " . $e->getMessage() . "\n";
}

echo "Background Automation Completed at " . date('Y-m-d H:i:s') . "\n";
