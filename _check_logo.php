<?php
require 'config/bootstrap.php';
try {
    $pdo = App\Core\Database\Database::getInstance()->getPdo();
    $stmt = $pdo->prepare("SELECT content_key, content_value FROM site_content WHERE content_key = 'company_logo' AND section = 'settings'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "company_logo: [" . ($row['content_value'] ?? 'NULL') . "]\n";
    echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "\n";
    echo "Concatenation: " . (defined('BASE_URL') ? BASE_URL : '') . ($row['content_value'] ?? '') . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
