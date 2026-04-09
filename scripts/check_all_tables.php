<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "=== Checking tables ===\n\n";

$tables = ['testimonials', 'blog_posts', 'jobs', 'newsletter_subscribers', 'enquiries'];

foreach ($tables as $table) {
    try {
        $result = $db->fetch("SHOW TABLES LIKE '$table'");
        if ($result) {
            echo "✅ $table exists\n";
            $cols = $db->fetchAll("DESCRIBE $table");
            echo "   Columns: " . count($cols) . "\n";
        } else {
            echo "❌ $table does NOT exist\n";
        }
    } catch (Exception $e) {
        echo "❌ $table error: " . $e->getMessage() . "\n";
    }
}