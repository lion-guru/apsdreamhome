<?php
/**
 * Migration: Add marketplace notification templates
 */

require_once __DIR__ . '/../../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "Adding marketplace notification templates...\n";

$templates = [
    // Boost payment initiated
    [
        'template_code' => 'boost_payment_initiated',
        'template_name' => 'Boost Payment Initiated',
        'channel' => 'email',
        'language' => 'en',
        'subject' => 'Payment Initiated for Property Boost',
        'body' => 'Hi {name},\n\nYour payment for property boost ({boost_type} for {duration} days) has been initiated.\n\nOrder ID: {order_id}\nAmount: ₹{amount}\n\nPlease complete the payment using the Razorpay checkout.\n\nThank you,\nAPS Dream Home Team',
        'variables' => json_encode(['name', 'boost_type', 'duration', 'order_id', 'amount']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'boost_payment_initiated',
        'template_name' => 'Boost Payment Initiated SMS',
        'channel' => 'sms',
        'language' => 'en',
        'subject' => 'Boost Payment',
        'body' => 'APS Dream Home: Payment initiated for property boost ({boost_type}). Order: {order_id}. Amount: ₹{amount}. Complete payment to activate boost.',
        'variables' => json_encode(['boost_type', 'order_id', 'amount']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'boost_payment_initiated',
        'template_name' => 'Boost Payment Initiated Push',
        'channel' => 'push',
        'language' => 'en',
        'subject' => 'Boost Payment Initiated',
        'body' => 'Your payment for {boost_type} boost has been initiated. Complete payment to activate.',
        'variables' => json_encode(['boost_type']),
        'is_active' => 1,
    ],

    // Boost payment verified/successful
    [
        'template_code' => 'boost_payment_success',
        'template_name' => 'Boost Payment Successful',
        'channel' => 'email',
        'language' => 'en',
        'subject' => 'Property Boost Activated Successfully',
        'body' => 'Hi {name},\n\nYour property boost has been activated successfully!\n\nProperty: {property_name}\nBoost Type: {boost_type}\nDuration: {duration} days\nAmount Paid: ₹{amount}\nPayment ID: {payment_id}\n\nYour property is now boosted and will get more visibility.\n\nThank you,\nAPS Dream Home Team',
        'variables' => json_encode(['name', 'property_name', 'boost_type', 'duration', 'amount', 'payment_id']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'boost_payment_success',
        'template_name' => 'Boost Payment Success SMS',
        'channel' => 'sms',
        'language' => 'en',
        'subject' => 'Boost Activated',
        'body' => 'APS Dream Home: Your {boost_type} boost for "{property_name}" is now active! Paid ₹{amount}. Boost expires in {duration} days.',
        'variables' => json_encode(['boost_type', 'property_name', 'duration', 'amount']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'boost_payment_success',
        'template_name' => 'Boost Payment Success Push',
        'channel' => 'push',
        'language' => 'en',
        'subject' => 'Boost Activated',
        'body' => 'Your {boost_type} boost for {property_name} is now active!',
        'variables' => json_encode(['boost_type', 'property_name']),
        'is_active' => 1,
    ],

    // Property boost applied
    [
        'template_code' => 'boost_applied',
        'template_name' => 'Property Boost Applied',
        'channel' => 'email',
        'language' => 'en',
        'subject' => 'Your Property Boost is Now Live',
        'body' => 'Hi {name},\n\nYour property boost has been applied successfully.\n\nProperty: {property_name}\nBoost Type: {boost_type}\nDuration: {duration} days\nExpires: {expires_at}\n\nYour property will now appear in featured/urgent/premium sections based on the boost type.\n\nTrack your property views and inquiries from your dashboard.\n\nThank you,\nAPS Dream Home Team',
        'variables' => json_encode(['name', 'property_name', 'boost_type', 'duration', 'expires_at']),
        'is_active' => 1,
    ],

    // Property saved to shortlist
    [
        'template_code' => 'property_saved',
        'template_name' => 'Property Saved to Shortlist',
        'channel' => 'push',
        'language' => 'en',
        'subject' => 'Property Saved',
        'body' => 'Property "{property_name}" has been added to your saved list.',
        'variables' => json_encode(['property_name']),
        'is_active' => 1,
    ],

    // Followup scheduled
    [
        'template_code' => 'followup_scheduled',
        'template_name' => 'Followup Scheduled',
        'channel' => 'push',
        'language' => 'en',
        'subject' => 'Followup Scheduled',
        'body' => 'Followup scheduled for {followup_type} on {scheduled_for} for property {property_name}.',
        'variables' => json_encode(['followup_type', 'scheduled_for', 'property_name']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'followup_scheduled',
        'template_name' => 'Followup Scheduled SMS',
        'channel' => 'sms',
        'language' => 'en',
        'subject' => 'Followup Scheduled',
        'body' => 'APS Dream Home: Followup scheduled for {followup_type} on {scheduled_for}. Property: {property_name}.',
        'variables' => json_encode(['followup_type', 'scheduled_for', 'property_name']),
        'is_active' => 1,
    ],

    // Followup completed
    [
        'template_code' => 'followup_completed',
        'template_name' => 'Followup Completed',
        'channel' => 'push',
        'language' => 'en',
        'subject' => 'Followup Completed',
        'body' => 'Followup completed: {outcome}. {notes}',
        'variables' => json_encode(['outcome', 'notes']),
        'is_active' => 1,
    ],

    // Transaction completed
    [
        'template_code' => 'transaction_completed',
        'template_name' => 'Transaction Completed',
        'channel' => 'email',
        'language' => 'en',
        'subject' => 'Property Transaction Completed',
        'body' => 'Hi {name},\n\nYour property transaction has been completed successfully.\n\nTransaction ID: {transaction_id}\nProperty: {property_name}\nAmount: ₹{amount}\nStatus: {status}\n\nTransaction details are available in your dashboard.\n\nThank you,\nAPS Dream Home Team',
        'variables' => json_encode(['name', 'transaction_id', 'property_name', 'amount', 'status']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'transaction_completed',
        'template_name' => 'Transaction Completed SMS',
        'channel' => 'sms',
        'language' => 'en',
        'subject' => 'Transaction Complete',
        'body' => 'APS Dream Home: Transaction #{transaction_id} completed. Property: {property_name}. Amount: ₹{amount}. Check dashboard for details.',
        'variables' => json_encode(['transaction_id', 'property_name', 'amount']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'transaction_completed',
        'template_name' => 'Transaction Completed Push',
        'channel' => 'push',
        'language' => 'en',
        'subject' => 'Transaction Completed',
        'body' => 'Transaction #{transaction_id} completed. Amount: ₹{amount}.',
        'variables' => json_encode(['transaction_id', 'amount']),
        'is_active' => 1,
    ],

    // Builder subscription activated
    [
        'template_code' => 'builder_subscription_activated',
        'template_name' => 'Builder Subscription Activated',
        'channel' => 'email',
        'language' => 'en',
        'subject' => 'Builder Subscription Activated',
        'body' => 'Hi {name},\n\nYour builder subscription has been activated.\n\nPackage: {package_name}\nDuration: {duration}\nAmount: ₹{amount}\nExpires: {expires_at}\n\nYou can now list up to {max_listings} properties with premium features.\n\nThank you,\nAPS Dream Home Team',
        'variables' => json_encode(['name', 'package_name', 'duration', 'amount', 'expires_at', 'max_listings']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'builder_subscription_activated',
        'template_name' => 'Builder Subscription Activated SMS',
        'channel' => 'sms',
        'language' => 'en',
        'subject' => 'Subscription Activated',
        'body' => 'APS Dream Home: Your {package_name} subscription is active. List up to {max_listings} properties. Expires: {expires_at}.',
        'variables' => json_encode(['package_name', 'max_listings', 'expires_at']),
        'is_active' => 1,
    ],

    // Builder subscription expiring soon
    [
        'template_code' => 'builder_subscription_expiring',
        'template_name' => 'Builder Subscription Expiring Soon',
        'channel' => 'email',
        'language' => 'en',
        'subject' => 'Builder Subscription Expiring Soon',
        'body' => 'Hi {name},\n\nYour builder subscription ({package_name}) will expire on {expires_at}.\n\nRenew now to continue enjoying premium features and listing up to {max_listings} properties.\n\nVisit your dashboard to renew.\n\nThank you,\nAPS Dream Home Team',
        'variables' => json_encode(['name', 'package_name', 'expires_at', 'max_listings']),
        'is_active' => 1,
    ],
    [
        'template_code' => 'builder_subscription_expiring',
        'template_name' => 'Builder Subscription Expiring Soon SMS',
        'channel' => 'sms',
        'language' => 'en',
        'subject' => 'Subscription Expiring',
        'body' => 'APS Dream Home: Your {package_name} subscription expires on {expires_at}. Renew to continue listing properties.',
        'variables' => json_encode(['package_name', 'expires_at']),
        'is_active' => 1,
    ],
];

foreach ($templates as $t) {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO notification_templates (template_code, template_name, channel, language, subject, body, variables, is_active, created_at, updated_at) VALUES (:code, :name, :channel, :lang, :subject, :body, :vars, 1, NOW(), NOW())");
        $stmt->execute([
            ':code' => $t['template_code'],
            ':name' => $t['template_name'],
            ':channel' => $t['channel'],
            ':lang' => $t['language'],
            ':subject' => $t['subject'],
            ':body' => $t['body'],
            ':vars' => $t['variables'],
        ]);
        echo "✅ Added: {$t['template_code']} ({$t['channel']}) - {$t['template_name']}\n";
    } catch (\Exception $e) {
        echo "❌ Error adding {$t['template_code']}: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";