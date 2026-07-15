<?php
/**
 * Seed SMS + WhatsApp templates for login/registration notifications
 * Run: php scripts/seed_notification_templates.php
 */

$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Seeding Notification Templates ===\n\n";

// ── 1. Ensure sms_templates table exists ──
$db->exec("CREATE TABLE IF NOT EXISTS sms_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_code VARCHAR(100) NOT NULL UNIQUE,
    template_name VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    variables JSON NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "[OK] sms_templates table ready\n";

// ── 2. Seed SMS templates ──
$smsTemplates = [
    [
        'template_code' => 'welcome_customer',
        'template_name' => 'Welcome - Customer',
        'body' => 'Hi {name}! Welcome to APS Dream Home. Your dream property awaits. Browse: {properties_url}. Login: {login_url}',
        'variables' => json_encode(['name', 'properties_url', 'login_url']),
    ],
    [
        'template_code' => 'welcome_associate',
        'template_name' => 'Welcome - Associate',
        'body' => 'Hi {name}! Welcome to APS Dream Home Associate Program. Your referral code: {referral_code}. Start earning commissions! Login: {login_url}',
        'variables' => json_encode(['name', 'referral_code', 'login_url']),
    ],
    [
        'template_code' => 'welcome_agent',
        'template_name' => 'Welcome - Agent',
        'body' => 'Hi {name}! Welcome to APS Dream Home Agent Network. Your agent dashboard is ready. Login: {login_url}',
        'variables' => json_encode(['name', 'login_url']),
    ],
    [
        'template_code' => 'login_alert',
        'template_name' => 'Login Security Alert',
        'body' => 'APS Dream Home: New login to your account at {time} from {device}. If this was not you, change your password immediately. Support: {support_url}',
        'variables' => json_encode(['time', 'device', 'support_url']),
    ],
    [
        'template_code' => 'password_reset',
        'template_name' => 'Password Reset OTP',
        'body' => 'Your APS Dream Home password reset OTP is: {otp}. Valid for 10 minutes. Do not share this with anyone.',
        'variables' => json_encode(['otp']),
    ],
    [
        'template_code' => 'booking_confirmation',
        'template_name' => 'Booking Confirmation',
        'body' => 'Booking Confirmed! Property: {property_title}. Amount: Rs.{amount}. Booking ID: {booking_id}. Thank you for choosing APS Dream Home.',
        'variables' => json_encode(['property_title', 'amount', 'booking_id']),
    ],
    [
        'template_code' => 'payment_success',
        'template_name' => 'Payment Success',
        'body' => 'Payment Received! Rs.{amount} for Booking #{booking_id}. Transaction ID: {transaction_id}. Thank you!',
        'variables' => json_encode(['amount', 'booking_id', 'transaction_id']),
    ],
];

$stmt = $db->prepare("INSERT INTO sms_templates (template_code, template_name, body, variables, is_active, created_at) 
    VALUES (?, ?, ?, ?, 1, NOW()) 
    ON DUPLICATE KEY UPDATE template_name=VALUES(template_name), body=VALUES(body), variables=VALUES(variables)");

foreach ($smsTemplates as $t) {
    $stmt->execute([$t['template_code'], $t['template_name'], $t['body'], $t['variables']]);
    echo "[OK] SMS template: {$t['template_name']}\n";
}

// ── 3. Ensure whatsapp_templates table exists ──
$db->exec("CREATE TABLE IF NOT EXISTS whatsapp_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(200) NOT NULL UNIQUE,
    category ENUM('UTILITY','MARKETING','AUTHENTICATION') DEFAULT 'UTILITY',
    language VARCHAR(10) DEFAULT 'en',
    header_type ENUM('TEXT','IMAGE','VIDEO','DOCUMENT','NONE') DEFAULT 'TEXT',
    header_text VARCHAR(255) DEFAULT NULL,
    body_text TEXT NOT NULL,
    footer_text VARCHAR(255) DEFAULT NULL,
    buttons JSON DEFAULT NULL,
    variables JSON DEFAULT NULL,
    status ENUM('DRAFT','PENDING','APPROVED','REJECTED') DEFAULT 'DRAFT',
    meta_template_id VARCHAR(100) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "\n[OK] whatsapp_templates table ready\n";

// ── 4. Seed WhatsApp templates ──
$waTemplates = [
    [
        'template_name' => 'welcome_message',
        'category' => 'UTILITY',
        'header_text' => 'Welcome to APS Dream Home',
        'body_text' => "Hi {{1}}! 🏠\n\nWelcome to APS Dream Home. We're thrilled to have you on board.\n\nYour dream property is just a few taps away. Browse our curated collection of premium plots, apartments, and commercial spaces.\n\n📞 Need help? Contact us anytime.",
        'footer_text' => 'APS Dream Home — Your Dream, Our Mission',
        'buttons' => json_encode([['type' => 'QUICK_REPLY', 'text' => 'Browse Properties'], ['type' => 'URL', 'text' => 'Visit Website', 'url' => '{{2}}']]),
        'variables' => json_encode(['name', 'website_url']),
        'status' => 'DRAFT',
    ],
    [
        'template_name' => 'login_alert',
        'category' => 'AUTHENTICATION',
        'header_text' => '🔐 Security Alert',
        'body_text' => "Hi {{1}}!\n\nA new login was detected on your APS Dream Home account.\n\n⏰ Time: {{2}}\n📱 Device: {{3}}\n🌐 Location: {{4}}\n\nIf this wasn't you, please secure your account immediately.",
        'footer_text' => 'APS Dream Home Security',
        'buttons' => json_encode([['type' => 'QUICK_REPLY', 'text' => 'This was me'], ['type' => 'QUICK_REPLY', 'text' => 'Secure my account']]),
        'variables' => json_encode(['name', 'time', 'device', 'location']),
        'status' => 'DRAFT',
    ],
    [
        'template_name' => 'booking_confirmation',
        'category' => 'UTILITY',
        'header_text' => 'Booking Confirmed ✅',
        'body_text' => "Hi {{1}}!\n\nYour property booking has been confirmed.\n\n🏠 Property: {{2}}\n📍 Location: {{3}}\n💰 Amount: ₹{{4}}\n🆔 Booking ID: {{5}}\n\nOur team will contact you shortly with next steps.",
        'footer_text' => 'APS Dream Home',
        'buttons' => json_encode([['type' => 'QUICK_REPLY', 'text' => 'View Booking'], ['type' => 'URL', 'text' => 'Download Receipt', 'url' => '{{6}}']]),
        'variables' => json_encode(['name', 'property_title', 'location', 'amount', 'booking_id', 'receipt_url']),
        'status' => 'DRAFT',
    ],
    [
        'template_name' => 'payment_reminder',
        'category' => 'UTILITY',
        'header_text' => 'Payment Reminder',
        'body_text' => "Hi {{1}}!\n\nThis is a friendly reminder for your upcoming EMI payment.\n\n📋 Booking: {{2}}\n💰 Amount: ₹{{3}}\n📅 Due Date: {{4}}\n\nPlease ensure timely payment to avoid late fees.",
        'footer_text' => 'APS Dream Home',
        'buttons' => json_encode([['type' => 'QUICK_REPLY', 'text' => 'Pay Now']]),
        'variables' => json_encode(['name', 'booking_id', 'amount', 'due_date']),
        'status' => 'DRAFT',
    ],
];

$stmt = $db->prepare("INSERT INTO whatsapp_templates (template_name, category, language, header_type, header_text, body_text, footer_text, buttons, variables, status, is_active, created_at, updated_at)
    VALUES (?, ?, 'en', 'TEXT', ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
    ON DUPLICATE KEY UPDATE category=VALUES(category), header_text=VALUES(header_text), body_text=VALUES(body_text), footer_text=VALUES(footer_text), buttons=VALUES(buttons), variables=VALUES(variables), status=VALUES(status), updated_at=NOW()");

foreach ($waTemplates as $t) {
    $stmt->execute([$t['template_name'], $t['category'], $t['header_text'], $t['body_text'], $t['footer_text'], $t['buttons'], $t['variables'], $t['status']]);
    echo "[OK] WhatsApp template: {$t['template_name']}\n";
}

// ── 5. Add welcome + login_alert to notification preferences ──
echo "\n=== Adding notification preference types ===\n";

// Check if user_notification_preferences table exists
try {
    $db->exec("SELECT 1 FROM user_notification_preferences LIMIT 1");
    echo "[OK] user_notification_preferences table exists\n";
    
    // Add welcome + login_alert types with default preferences
    // These are critical types that bypass user preferences (always sent)
    // But we still want entries so users can see them in the preferences UI
    
    $newTypes = ['welcome', 'login_alert'];
    $stmt = $db->prepare("INSERT IGNORE INTO user_notification_preferences 
        (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency, updated_at)
        SELECT u.id, u.role, ?, 1, 0, 0, 1, 'immediate', NOW()
        FROM users u
        WHERE u.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM user_notification_preferences unp 
            WHERE unp.user_id = u.id AND unp.notification_type = ?
        )
        LIMIT 100");
    
    foreach ($newTypes as $type) {
        $stmt->execute([$type, $type]);
        $count = $stmt->rowCount();
        echo "[OK] Added '{$type}' preferences for {$count} users\n";
    }
} catch (\Throwable $e) {
    echo "[WARN] user_notification_preferences: {$e->getMessage()}\n";
}

// ── 6. Ensure notification_logs table has proper schema ──
$db->exec("CREATE TABLE IF NOT EXISTS notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL DEFAULT 'general',
    recipient_token VARCHAR(255) DEFAULT '',
    title VARCHAR(255) DEFAULT '',
    body TEXT,
    payload JSON,
    response JSON,
    status VARCHAR(20) DEFAULT 'pending',
    channel VARCHAR(20) DEFAULT 'push',
    user_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "\n[OK] notification_logs table ready\n";

echo "\n=== All notification templates seeded successfully! ===\n";
echo "SMS templates: " . $db->query("SELECT COUNT(*) FROM sms_templates")->fetchColumn() . " total\n";
echo "WhatsApp templates: " . $db->query("SELECT COUNT(*) FROM whatsapp_templates")->fetchColumn() . " total\n";
