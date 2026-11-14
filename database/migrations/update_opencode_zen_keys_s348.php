<?php
/**
 * Migration: Update OpenCode Zen API Keys (techguruabhay@gmail.com / apsdreamhomes44@gmail.com)
 * Session: 348 / sanitized 2026-11-14
 * Idempotent: updates or inserts keys, safe to re-run.
 *
 * SECURITY (2026-11-14): this file previously carried the LIVE OpenCode Zen
 * keys as plaintext literals. They were exposed on disk and in assistant
 * session context and must be considered compromised. The owner must rotate
 * both keys in the OpenCode console before re-importing; the literals were
 * removed. Keys are read from the environment (gitignored .env).
 *
 * Required env vars:
 *   OPENCODE_ZEN_KEY_TECHGURU   OpenCode Zen key for techguruabhay@gmail.com
 *   OPENCODE_ZEN_KEY_APS        OpenCode Zen key for apsdreamhomes44@gmail.com
 */
require_once __DIR__ . '/../../config/bootstrap.php';
use App\Services\ServiceConfigService;

$keyTechguru = getenv('OPENCODE_ZEN_KEY_TECHGURU') ?: '';
$keyAps      = getenv('OPENCODE_ZEN_KEY_APS')      ?: '';

$missing = [];
foreach (['OPENCODE_ZEN_KEY_TECHGURU' => $keyTechguru, 'OPENCODE_ZEN_KEY_APS' => $keyAps] as $name => $val) {
    if ($val === '') { $missing[] = $name; }
}
if ($missing) {
    fwrite(STDERR, "Refusing to import OpenCode keys - missing env vars: " . implode(', ', $missing) . "\n");
    fwrite(STDERR, "Set them in .env (gitignored) after rotating the exposed keys.\n");
    exit(1);
}

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// 1. Update settings table (plaintext settings keys by design)
$stmtCheckSetting  = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
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

// 2. Update api_keys table (plaintext key store by design)
$stmtCheckApiKey  = $pdo->prepare("SELECT COUNT(*) FROM api_keys WHERE email = ? AND service_name = 'opencode'");
$stmtUpdateApiKey = $pdo->prepare("UPDATE api_keys SET key_value = ?, key_name = ?, is_active = 1, status = 'active', updated_at = NOW() WHERE email = ? AND service_name = 'opencode'");
$stmtInsertApiKey = $pdo->prepare("INSERT INTO api_keys (tenant_id, key_name, key_value, key_type, service_name, provider, email, is_active, status, created_at, updated_at) VALUES (1, ?, ?, 'api_key', 'opencode', 'opencode', ?, 1, 'active', NOW(), NOW())");

foreach (['techguruabhay@gmail.com' => $keyTechguru, 'apsdreamhomes44@gmail.com' => $keyAps] as $email => $key) {
    $stmtCheckApiKey->execute([$email]);
    $name = $email === 'techguruabhay@gmail.com' ? 'OPENCODE_ZEN_KEY_TECHGURU' : 'OPENCODE_ZEN_KEY_APS';
    if ($stmtCheckApiKey->fetchColumn() > 0) {
        $stmtUpdateApiKey->execute([$key, $name, $email]);
        echo "UPDATED api_keys: {$email}\n";
    } else {
        $stmtInsertApiKey->execute([$name, $key, $email]);
        echo "INSERTED api_keys: {$email}\n";
    }
}

// Legacy rows (ids 22/29) kept in sync with the same env-sourced value.
$stmt = $pdo->prepare("UPDATE api_keys SET key_value = ?, email = 'techguruabhay@gmail.com', updated_at = NOW() WHERE id IN (22, 29)");
$stmt->execute([$keyTechguru]);

// 3. Update service_configs (encrypted at rest via ServiceConfigService)
$svc = ServiceConfigService::getInstance();
$svc->set('opencode', 'techguru_key', $keyTechguru, ['is_secret' => true, 'config_type' => 'password', 'description' => 'OpenCode Zen API Key (techguruabhay@gmail.com)', 'group_name' => 'ai', 'sort_order' => 10]);
$svc->set('opencode', 'aps_key',      $keyAps,      ['is_secret' => true, 'config_type' => 'password', 'description' => 'OpenCode Zen API Key (apsdreamhomes44@gmail.com)', 'group_name' => 'ai', 'sort_order' => 20]);
$svc->set('opencode', 'active_account', 'techguruabhay@gmail.com', ['config_type' => 'text', 'description' => 'Currently Active OpenCode Account', 'group_name' => 'ai', 'sort_order' => 30]);
$svc->invalidateCache();
echo "UPDATED service_configs: opencode.{techguru_key, aps_key, active_account} (encrypted)\n";

echo "Done. OpenCode Zen keys updated successfully (no secrets printed).\n";
