<?php
require __DIR__ . '/config/bootstrap.php';
$db = \App\Core\Database::getInstance()->getPdo();
$stmt = $db->prepare("SELECT content_value FROM site_content WHERE section = ? AND content_key = ?");
$stmt->execute(['settings', 'company_logo']);
$r = $stmt->fetch();
echo 'company_logo = ' . ($r ? $r['content_value'] : 'NOT SET') . PHP_EOL;
echo 'file exists: ' . (file_exists(__DIR__ . '/public/assets/images/logo/apslogonew.jpg') ? 'YES' : 'NO') . PHP_EOL;?>