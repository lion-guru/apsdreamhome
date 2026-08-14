<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$templates = [
    ['welcome_message', 'UTILITY', 'Hi {1}! Welcome to APS Dream Home. Your dream property awaits. Browse: {2}', 'en', 'text', json_encode(['name','website_url']), json_encode([['type'=>'QUICK_REPLY','text'=>'Browse Properties'],['type'=>'URL','text'=>'Visit Website','url'=>'{2}']])],
    ['login_alert', 'AUTHENTICATION', 'Hi {1}! New login to your APS Dream Home account at {2} from {3}. If not you, secure your account.', 'en', 'text', json_encode(['name','time','device']), json_encode([['type'=>'QUICK_REPLY','text'=>'This was me'],['type'=>'QUICK_REPLY','text'=>'Secure account']])],
    ['booking_confirmation', 'UTILITY', 'Booking Confirmed! Property: {1}. Amount: Rs.{2}. Booking ID: {3}.', 'en', 'text', json_encode(['property_title','amount','booking_id']), json_encode([['type'=>'QUICK_REPLY','text'=>'View Booking']])],
    ['payment_reminder', 'UTILITY', 'EMI Reminder: Rs.{1} due for Booking #{2} on {3}. Please pay on time.', 'en', 'text', json_encode(['amount','booking_id','due_date']), json_encode([['type'=>'QUICK_REPLY','text'=>'Pay Now']])],
];

$stmt = $db->prepare('INSERT INTO whatsapp_templates (name, category, content, language, template_type, variables, buttons, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE content=VALUES(content), variables=VALUES(variables), buttons=VALUES(buttons), updated_at=NOW()');

foreach ($templates as $t) {
    $stmt->execute($t);
    echo "OK: {$t[0]}\n";
}

echo "Total WhatsApp templates: " . $db->query("SELECT COUNT(*) FROM whatsapp_templates")->fetchColumn() . "\n";?>