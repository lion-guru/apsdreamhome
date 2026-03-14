<?php
/**
 * Test Script for Phase 4 Sync API
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Mock DB connection if needed or use real one
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance()->getPdo();

echo "--- Testing SyncService ---\n";
$syncService = new \App\Services\SyncService();

// Test 1: Initial Sync (Far in the past)
$lastSync = '2000-01-01 00:00:00';
$package = $syncService->getSyncPackage($lastSync);
echo "Initial sync record count: " . count($package['data']['properties']) . " properties found.\n";

// Test 2: Incremental Sync
// Update a property to trigger sync
$stmt = $db->prepare("UPDATE properties SET bedrooms = bedrooms, sync_updated_at = NOW() WHERE id = 1");
$stmt->execute();

$currentSync = date('Y-m-d H:i:s', strtotime('-5 seconds'));
$package2 = $syncService->getSyncPackage($currentSync);
echo "Incremental sync record count: " . count($package2['data']['properties']) . " properties found (expected 1).\n";

if (count($package2['data']['properties']) > 0) {
    echo "✅ Incremental Sync working correctly.\n";
} else {
    echo "❌ Incremental Sync failed to detect change.\n";
}

echo "\n--- Testing MLM Summary API ---\n";
$perfCalculator = new \App\Services\PerformanceRankCalculator();
$summary = $perfCalculator->calculateRank(1); // Assuming user ID 1 exists
if ($summary['success']) {
    echo "✅ MLM Summary fetched successfully. Rank: " . $summary['rank'] . "\n";
} else {
    echo "❌ MLM Summary fetch failed.\n";
}

echo "\n--- Phase 4 Verification Complete ---\n";
