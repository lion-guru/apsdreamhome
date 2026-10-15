<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\ExecutiveAIService;

$service = new ExecutiveAIService();

// Test new actions
echo "Testing new Executive AI actions...\n\n";

// Test get_lead_details (invalid lead)
$result = $service->executeAction('get_lead_details', ['lead_id' => 999], 'ceo', 1);
echo "get_lead_details (invalid): ";
print_r($result);

// Test create_lead
$result = $service->executeAction('create_lead', [
    'name' => 'Test Lead',
    'email' => 'test@example.com',
    'phone' => '9876543210',
    'budget' => 5000000,
    'property_type' => 'plot',
    'location' => 'Gorakhpur',
    'source' => 'executive_ai'
], 'ceo', 1);
echo "\ncreate_lead: ";
print_r($result);

// Test get_lead_details (valid lead)
if (isset($result['lead_id'])) {
    $result2 = $service->executeAction('get_lead_details', ['lead_id' => $result['lead_id']], 'ceo', 1);
    echo "\nget_lead_details (valid): ";
    print_r($result2);
}

// Test update_lead_status
if (isset($result['lead_id'])) {
    $result3 = $service->executeAction('update_lead_status', [
        'lead_id' => $result['lead_id'],
        'status' => 'contacted',
        'notes' => 'Called and discussed requirements'
    ], 'ceo', 1);
    echo "\nupdate_lead_status: ";
    print_r($result3);
}

// Test send_email
$result4 = $service->executeAction('send_email', [
    'to' => 'test@example.com',
    'subject' => 'Test Email',
    'body_html' => '<p>This is a test email from Executive AI</p>',
    'body_text' => 'This is a test email from Executive AI',
    'template' => 'test_template'
], 'ceo', 1);
echo "\nsend_email: ";
print_r($result4);

// Test get_dashboard_stats
$result5 = $service->executeAction('get_dashboard_stats', [], 'ceo', 1);
echo "\nget_dashboard_stats: ";
print_r($result5);

// Test create_payment
$result6 = $service->executeAction('create_payment', [
    'amount' => 50000,
    'gateway' => 'upi',
    'reference' => 'UPI123456789',
    'customer_id' => 1
], 'cfo', 1);
echo "\ncreate_payment: ";
print_r($result6);

// Test get_user_profile
$result7 = $service->executeAction('get_user_profile', ['user_id' => 1], 'ceo', 1);
echo "\nget_user_profile: ";
print_r($result7);

// Test get_lead_list
$result8 = $service->executeAction('get_lead_list', ['status' => 'new', 'limit' => 5], 'ceo', 1);
echo "\nget_lead_list: ";
print_r($result8);

// Test update_booking
$result9 = $service->executeAction('update_booking', [
    'booking_id' => 1,
    'status' => 'agreement_signed',
    'approval_notes' => 'Approved by Executive AI'
], 'ceo', 1);
echo "\nupdate_booking: ";
print_r($result9);

echo "\n=== All Executive AI Action Tests Completed ===\n";