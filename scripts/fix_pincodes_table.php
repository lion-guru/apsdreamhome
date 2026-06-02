<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

$db->execute("ALTER TABLE pincodes ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1");
echo "✅ Added is_active column to pincodes\n";