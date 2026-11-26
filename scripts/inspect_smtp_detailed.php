<?php
$db = new PDO("mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4", "root", "");

echo "--- site_content matching smtp ---\n";
$stmt = $db->query("SELECT id, section, content_key, content_value FROM site_content WHERE content_key LIKE '%smtp%'");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Sec: {$r['section']} | Key: {$r['content_key']} | Val: {$r['content_value']}\n";
}

echo "\n--- app_settings matching smtp ---\n";
try {
    $stmt2 = $db->query("SELECT id, setting_key, setting_value FROM app_settings WHERE setting_key LIKE '%smtp%'");
    while($r2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$r2['id']} | Key: {$r2['setting_key']} | Val: {$r2['setting_value']}\n";
    }
} catch (\Throwable $e) {
    echo "app_settings error: " . $e->getMessage() . "\n";
}

echo "\n--- config/email.php contents if exists ---\n";
$emailConfig = __DIR__ . '/../config/email.php';
if (file_exists($emailConfig)) {
    echo file_get_contents($emailConfig);
} else {
    echo "config/email.php does NOT exist\n";
}

echo "\n--- .env MAIL_* lines ---\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile);
    foreach ($lines as $line) {
        if (stripos($line, 'mail') !== false || stripos($line, 'smtp') !== false) {
            echo trim($line) . "\n";
        }
    }
}
