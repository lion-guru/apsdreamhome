<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\ExecutiveAIService;

$service = new ExecutiveAIService();

echo "=== Testing New Executive AI Actions ===\n\n";

// Test export_leads
echo "1. Testing export_leads (CSV)...\n";
$result = $service->executeAction('export_leads', ['format' => 'csv', 'status' => 'new'], 'ceo', 1);
echo "export_leads: ";
if ($result['success']) {
    echo "Success! Format: {$result['format']}, Count: {$result['count']}\n";
    echo "First 200 chars: " . substr($result['content'], 0, 200) . "...\n";
} else {
    echo "Failed: {$result['error']}\n";
}

echo "\n2. Testing export_leads (JSON)...\n";
$result = $service->executeAction('export_leads', ['format' => 'json', 'status' => 'contacted'], 'ceo', 1);
echo "export_leads (JSON): ";
if ($result['success']) {
    echo "Success! Format: {$result['format']}, Count: {$result['count']}\n";
} else {
    echo "Failed: {$result['error']}\n";
}

// Test create_reminder
echo "\n3. Testing create_reminder...\n";
$result = $service->executeAction('create_reminder', [
    'lead_id' => 49475,
    'title' => 'Follow up call',
    'description' => 'Call to discuss property options',
    'due_date' => date('Y-m-d', strtotime('+2 days')),
    'due_time' => '10:00:00',
    'type' => 'follow_up'
], 'ceo', 1);
echo "create_reminder: ";
print_r($result);

// Test get_analytics
echo "\n4. Testing get_analytics...\n";
$result = $service->executeAction('get_analytics', ['period' => '30'], 'ceo', 1);
echo "get_analytics: ";
if ($result['success']) {
    echo "Success! Period: {$result['period_days']} days\n";
    echo "Analytics keys: " . implode(', ', array_keys($result['analytics'])) . "\n";
    foreach ($result['analytics'] as $key => $data) {
        echo "  - $key: " . count($data) . " records\n";
    }
} else {
    echo "Failed: {$result['error']}\n";
}

// Test bulk_update_leads
echo "\n5. Testing bulk_update_leads...\n";
$result = $service->executeAction('bulk_update_leads', [
    'lead_ids' => [49474, 49475],
    'status' => 'contacted',
    'priority' => 'high',
    'notes' => 'Bulk updated via Executive AI'
], 'ceo', 1);
echo "bulk_update_leads: ";
print_r($result);

echo "\n=== All New Executive AI Action Tests Completed ===\n";