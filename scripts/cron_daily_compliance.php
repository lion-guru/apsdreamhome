<?php
/**
 * Daily Compliance Cron Job
 * Run: php scripts/cron_daily_compliance.php
 * Schedule: 0 2 * * * php /path/to/scripts/cron_daily_compliance.php
 */

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

use App\Services\Booking\BookingComplianceService;

echo "[" . date('Y-m-d H:i:s') . "] APS Dream Home - Daily Compliance Cron\n";
echo "========================================================\n\n";

echo "1. Checking 25% Token Compliance...\n";
$bookingService = new BookingComplianceService();
$tokenResult = $bookingService->enforceTokenRule();
echo "   Released plots: {$tokenResult['released_plots']}\n";
echo "   Warnings: {$tokenResult['warnings']}\n\n";

echo "2. Releasing expired 48h plot holds...\n";
$holdResult = $bookingService->releaseExpiredHolds();
echo "   Released plots: {$holdResult['released_plots']}\n";
echo "   Skipped (token paid): {$holdResult['skipped_paid']}\n";
echo "   Warnings: {$holdResult['warnings']}\n\n";

echo "[" . date('Y-m-d H:i:s') . "] Compliance cron completed.\n";?>