<?php
// integration_usage_demo.php: Demonstrate usage of all integrations via helpers
require_once __DIR__ . '/includes/integration_helpers.php';

// WhatsApp
send_whatsapp('+911234567890', 'Test WhatsApp message!');

// Google Sheets
export_to_google_sheets([
    ['Name', 'Email'],
    ['John Doe', 'john@example.com']
]);

// Email
send_email('admin@example.com', 'Test Subject', 'This is a test email body.');

// SMS
send_sms('+911234567890', 'Test SMS message!');

// CRM
sync_with_crm(['name' => 'John Doe', 'email' => 'john@example.com']);

echo 'Integration demo executed. Check your external services.';
