<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\AI\PredictiveAnalyticsService;

$analytics = new PredictiveAnalyticsService();

echo "=== Predictive Analytics Tests ===\n\n";

// Test 1: Lead Scoring (use valid lead IDs)
echo "1. Testing Lead Scoring...\n";
$validLeads = [11045, 11046, 11047, 11048, 11049];
foreach ($validLeads as $leadId) {
    $result = $analytics->scoreLead($leadId);
    echo "Lead #{$leadId} Score: ";
    if ($result['success']) {
        echo "Score={$result['score']}, Grade={$result['grade']}, Rec={$result['recommendation']}\n";
    } else {
        echo "Error: {$result['error']}\n";
    }
}

// Test 2: Batch Lead Scoring
echo "\n2. Testing Batch Lead Scoring...\n";
$result = $analytics->predictConversions(['status' => 'qualified', 'limit' => 10]);
echo "Batch Scoring (qualified): Total: {$result['total_analyzed']}, Hot: {$result['hot']}, Warm: {$result['warm']}, Cold: {$result['cold']}\n";

$result = $analytics->predictConversions(['status' => 'new', 'limit' => 10]);
echo "Batch Scoring (new): Total: {$result['total_analyzed']}, Hot: {$result['hot']}, Warm: {$result['warm']}, Cold: {$result['cold']}\n";

// Test 3: Colony Price Trend
echo "\n3. Testing Colony Price Trends...\n";
$colonies = [2, 3, 6, 7]; // Suryoday Colony, Braj Radha Nagri, Raghunath Nagri, APS Motiram Township
foreach ($colonies as $colonyId) {
    $result = $analytics->predictPriceTrend($colonyId, 6);
    if ($result['success']) {
        echo "Colony {$colonyId} Trend: {$result['trend']} ({$result['monthly_change_pct']}%/mo, confidence: {$result['confidence']}%)\n";
    } else {
        echo "Colony {$colonyId}: {$result['error']}\n";
    }
}

// Test 4: All Colony Trends
echo "\n4. Testing All Colony Trends...\n";
$result = $analytics->getAllColonyTrends();
echo "All Colony Trends: ";
if ($result['success']) {
    echo "Total: {$result['total_analyzed']}\n";
    echo "Summary: Rising={$result['summary']['rising']}, Falling={$result['summary']['falling']}, Stable={$result['summary']['stable']}\n";
    if (!empty($result['trends'])) {
        foreach (array_slice($result['trends'], 0, 5) as $t) {
            echo "  - {$t['colony_name']}: {$t['trend']} ({$t['monthly_change_pct']}%/mo)\n";
        }
    }
} else {
    echo "Error: {$result['error']}\n";
}

echo "\n=== All Predictive Analytics Tests Completed ===\n";