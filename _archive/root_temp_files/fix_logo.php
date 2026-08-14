<?php
require __DIR__ . '/config/bootstrap.php';
$db = \App\Core\Database::getInstance();
$pdo = $db->getPdo();

// Fix company_logo â€” point to the actual file that exists
$stmt = $pdo->prepare("UPDATE site_content SET content_value = ? WHERE section = 'settings' AND content_key = 'company_logo'");
$stmt->execute(['assets/images/logo/apslogonew.jpg']);

echo "Updated company_logo to: assets/images/logo/apslogonew.jpg" . PHP_EOL;

// Verify
$stmt = $pdo->prepare("SELECT content_value FROM site_content WHERE section = 'settings' AND content_key = 'company_logo'");
$stmt->execute();
$r = $stmt->fetch();
echo "Verification: company_logo = " . $r['content_value'] . PHP_EOL;
echo "File exists: " . (file_exists(__DIR__ . '/public/' . $r['content_value']) ? 'YES' : 'NO') . PHP_EOL;?>