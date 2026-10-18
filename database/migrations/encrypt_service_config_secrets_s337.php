<?php
/**
 * Migration: Encrypt existing plaintext secrets in service_configs (AES-256-CBC)
 * Session: 337
 * Idempotent: Only encrypts rows WITHOUT the 'enc:v1:' prefix (legacy plaintext).
 * Rows already encrypted are skipped (no double-encryption).
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Find plaintext secret rows (non-empty, no enc:v1: prefix)
$rows = $pdo->query("
    SELECT id, service_name, config_key, config_value
    FROM service_configs
    WHERE is_secret = 1
      AND config_value IS NOT NULL
      AND config_value != ''
      AND config_value NOT LIKE 'enc:v1:%'
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "SKIP — no plaintext secret rows to encrypt\n";
    echo "Done.\n";
    exit(0);
}

$encrypted = 0;
foreach ($rows as $row) {
    $enc = \App\Services\ServiceConfigService::encryptValue($row['config_value']);
    $stmt = $pdo->prepare("UPDATE service_configs SET config_value = ? WHERE id = ?");
    $stmt->execute([$enc, $row['id']]);
    $encrypted++;
    echo "ENCRYPTED service_configs#{$row['id']} ({$row['service_name']}.{$row['config_key']})\n";
}

echo "Encrypted {$encrypted} plaintext secret(s) to AES-256-CBC\n";
echo "Done.\n";