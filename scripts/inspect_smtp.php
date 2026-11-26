<?php
$db = new PDO("mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4", "root", "");

echo "=== SMTP / MAIL SETTINGS IN DATABASE ===\n";
$stmt = $db->query("SELECT id, `key`, `value` FROM settings WHERE `key` LIKE '%smtp%' OR `key` LIKE '%mail%' OR `key` LIKE '%email%'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID {$r['id']} | {$r['key']} = {$r['value']}\n";
}

echo "\n=== EMAIL QUEUE RECENT ENTRIES ===\n";
$qStmt = $db->query("SELECT id, to_email, subject, status, attempts, error_message, created_at, sent_at FROM email_queue ORDER BY id DESC LIMIT 5");
while ($qr = $qStmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Queue ID {$qr['id']} | To: {$qr['to_email']} | Subj: {$qr['subject']} | Status: {$qr['status']} | Err: {$qr['error_message']} | Sent: {$qr['sent_at']}\n";
}
