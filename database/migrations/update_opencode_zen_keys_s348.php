<?php
/**
 * Migration: Update OpenCode Zen API Keys for techguruabhay@gmail.com and apsdreamhomes44@gmail.com
 * Session: 348
 * Idempotent: Updates or inserts keys, safe to re-run
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$keyTechguru = 'oc_sk_e45303e6e06c_dlgfYvLfSFtkaZa5YTcOiy0upxQGrpjt';
$keyAps      = 'oc_sk_922b42946876_zyXpUtTi8K4KWjFRE5XI_lDx34ZX2wRQ';

// 1. Update settings table
$stmtCheckSetting = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
$stmtUpdateSetting = $pdo->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?");
$stmtInsertSetting = $pdo->prepare("INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");

$settingsMap = [
    'api_opencode_key'          => $keyTechguru,
    'api_opencode_key_techguru' => $keyTechguru,
    'api_opencode_key_aps'      => $keyAps,
];

foreach ($settingsMap as $k => $v) {
    $stmtCheckSetting->execute([$k]);
    if ($stmtCheckSetting->fetchColumn() > 0) {
        $stmtUpdateSetting->execute([$v, $k]);
        echo "UPDATED settings: {$k}\n";
    } else {
        $stmtInsertSetting->execute([$k, $v]);
        echo "INSERTED settings: {$k}\n";
    }
}

// 2. Update api_keys table
$stmtCheckApiKey = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE email = ? AND service_name = 'opencode'");
$stmtUpdateApiKey = $pdo->prepare("UPDATE api_keys SET key_value = ?, key_name = ?, is_active = 1, status = 'active', updated_at = NOW() WHERE email = ? AND service_name = 'opencode'");
$stmtInsertApiKey = $pdo->prepare("INSERT INTO api_keys (tenant_id, key_name, key_value, key_type, service_name, provider, email, is_active, status, created_at, updated_at) VALUES (1, ?, ?, 'api_key', 'opencode', 'opencode', ?, 1, 'active', NOW(), NOW())");

// TechGuru Key
$stmtCheckApiKey->execute(['techguruabhay@gmail.com']);
if ($stmtCheckApiKey->fetchColumn() > 0) {
    $stmtUpdateApiKey->execute([$keyTechguru, 'OPENCODE_ZEN_KEY_TECHGURU', 'techguruabhay@gmail.com']);
    echo "UPDATED api_keys: techguruabhay@gmail.com\n";
} else {
    $stmtInsertApiKey->execute(['OPENCODE_ZEN_KEY_TECHGURU', $keyTechguru, 'techguruabhay@gmail.com']);
    echo "INSERTED api_keys: techguruabhay@gmail.com\n";
}

// APS Key
$stmtCheckApiKey->execute(['apsdreamhomes44@gmail.com']);
if ($stmtCheckApiKey->fetchColumn() > 0) {
    $stmtUpdateApiKey->execute([$keyAps, 'OPENCODE_ZEN_KEY_APS', 'apsdreamhomes44@gmail.com']);
    echo "UPDATED api_keys: apsdreamhomes44@gmail.com\n";
} else {
    $stmtInsertApiKey->execute(['OPENCODE_ZEN_KEY_APS', $keyAps, 'apsdreamhomes44@gmail.com']);
    echo "INSERTED api_keys: apsdreamhomes44@gmail.com\n";
}

// Also update legacy row 22 / 29 if present
$pdo->exec("UPDATE api_keys SET key_value = '{$keyTechguru}', email = 'techguruabhay@gmail.com', updated_at = NOW() WHERE id IN (22, 29)");

// 3. Update service_configs table
$stmtCheckConfig = $pdo->prepare("SELECT COUNT(*) FROM service_configs WHERE service_name = 'opencode' AND config_key = ?");
$stmtUpdateConfig = $pdo->prepare("UPDATE service_configs SET config_value = ?, updated_at = NOW() WHERE service_name = 'opencode' AND config_key = ?");
$stmtInsertConfig = $pdo->prepare("INSERT INTO service_configs (tenant_id, service_name, config_key, config_value, config_type, description, is_secret, group_name, sort_order) VALUES (1, 'opencode', ?, ?, ?, ?, ?, 'ai', ?)");

$configs = [
    ['techguru_key', $keyTechguru, 'password', 'OpenCode Zen API Key (techguruabhay@gmail.com)', 1, 10],
    ['aps_key', $keyAps, 'password', 'OpenCode Zen API Key (apsdreamhomes44@gmail.com)', 1, 20],
    ['active_account', 'techguruabhay@gmail.com', 'text', 'Currently Active OpenCode Account', 0, 30],
];

foreach ($configs as $cfg) {
    $stmtCheckConfig->execute([$cfg[0]]);
    if ($stmtCheckConfig->fetchColumn() > 0) {
        $stmtUpdateConfig->execute([$cfg[1], $cfg[0]]);
        echo "UPDATED service_configs: opencode.{$cfg[0]}\n";
    } else {
        $stmtInsertConfig->execute([$cfg[0], $cfg[1], $cfg[2], $cfg[3], $cfg[4], $cfg[5]]);
        echo "INSERTED service_configs: opencode.{$cfg[0]}\n";
    }
}

echo "Done. OpenCode Zen keys updated successfully.\n";
