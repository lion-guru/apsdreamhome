<?php
/**
 * S787: app/Services/Localization/LocalizationService queries config_key,
 * config_value and description on localization_config, but the live table was
 * a 4-column stub (id/tenant_id/created_at/updated_at). Every initializer
 * INSERT and supported-locales read 1054'd into a try/catch and silently
 * fell back to hardcoded defaults. The service defines the exact default
 * config itself — this migration only gives those writes a real, unique key.
 *
 * Idempotent: re-running skips each column/key that already exists.
 */
$root = dirname(__DIR__, 2);
require_once $root . '/config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$table = 'localization_config';

$columns = [
    'config_key'   => "ALTER TABLE `$table` ADD COLUMN `config_key` varchar(100) NULL AFTER `id`",
    'config_value' => "ALTER TABLE `$table` ADD COLUMN `config_value` text NULL AFTER `config_key`",
    'description'  => "ALTER TABLE `$table` ADD COLUMN `description` varchar(255) NULL AFTER `config_value`",
];
foreach ($columns as $name => $sql) {
    $exists = $db->fetch(
        "SELECT COUNT(*) c FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
        [$table, $name]
    );
    if ((int)($exists['c'] ?? 0) > 0) {
        echo "SKIP column $name\n";
        continue;
    }
    $db->execute($sql);
    echo "ADDED column $name\n";
}

$keyExists = $db->fetch(
    "SELECT COUNT(*) c FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = 'uniq_locale_tenant_key'",
    [$table]
);
if ((int)($keyExists['c'] ?? 0) > 0) {
    echo "SKIP unique key uniq_locale_tenant_key\n";
} else {
    $db->execute("ALTER TABLE `$table` ADD UNIQUE KEY `uniq_locale_tenant_key` (`tenant_id`, `config_key`)");
    echo "ADDED unique key uniq_locale_tenant_key\n";
}

// Verify the exact shape the service INSERTs/SELECTs/UPDATEs.
$have = $db->fetchAll(
    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION",
    [$table]
);
echo "COLUMNS: " . implode(', ', array_column($have, 'COLUMN_NAME')) . "\n";
