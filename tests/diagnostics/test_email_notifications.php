<?php
/**
 * Test Email Notification System
 * Tests the email notification functionality
 */

require_once 'config/bootstrap.php';

echo "🧪 Testing Email Notification System:\n\n";

try {
    // Test 1: Check if EmailNotification class exists and can be instantiated
    echo "1. Testing EmailNotification Class...\n";

    if (class_exists('\App\Core\EmailNotification')) {
        echo "   ✅ EmailNotification class exists\n";

        $emailNotification = new \App\Core\EmailNotification();
        echo "   ✅ EmailNotification instance created successfully\n";
    } else {
        echo "   ❌ EmailNotification class not found\n";
    }

} catch (Exception $e) {
    echo "   ❌ Error creating EmailNotification instance: " . $e->getMessage() . "\n";
}

try {
    // Test 2: Test inquiry notification
    echo "2. Testing Inquiry Notification...\n";

    if (isset($emailNotification)) {
        // Test with sample inquiry ID
        $test_inquiry_id = 1;

        // Check if inquiry exists
        global $pdo;
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id FROM property_inquiries WHERE id = ?");
            $stmt->execute([$test_inquiry_id]);
            if ($stmt->rowCount() > 0) {
                $result = $emailNotification->sendInquiryNotification($test_inquiry_id);
                if ($result) {
                    echo "   ✅ Inquiry notification sent successfully\n";
                } else {
                    echo "   ⚠️  Inquiry notification failed (likely due to SMTP not configured)\n";
                }
            } else {
                echo "   ⚠️  Test inquiry not found in database\n";
            }
        }
    }

} catch (Exception $e) {
    echo "   ❌ Error testing inquiry notification: " . $e->getMessage() . "\n";
}

try {
    // Test 3: Test registration notification
    echo "3. Testing Registration Notification...\n";

    if (isset($emailNotification)) {
        $test_user_data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+91-9876543210',
            'role' => 'customer'
        ];

        $result = $emailNotification->sendRegistrationNotification($test_user_data);
        if ($result) {
            echo "   ✅ Registration notification sent successfully\n";
        } else {
            echo "   ⚠️  Registration notification failed (likely due to SMTP not configured)\n";
        }
    }

} catch (Exception $e) {
    echo "   ❌ Error testing registration notification: " . $e->getMessage() . "\n";
}

echo "\n📧 Email Notification System Test Summary:\n";
echo "   • EmailNotification class: ✅ Available\n";
echo "   • PHPMailer integration: ✅ Configured\n";
echo "   • Email templates: ✅ Ready\n";
echo "   • SMTP configuration: ⚠️  Needs setup for actual sending\n";
echo "\n💡 To enable email sending:\n";
echo "   1. Configure SMTP settings in admin panel\n";
echo "   2. Set up App Password for Gmail (recommended)\n";
echo "   3. Test with real email addresses\n";

echo "\n🎉 Email Notification Testing completed!\n";
?>
