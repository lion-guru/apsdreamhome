<?php
/**
 * Migration: Seed Twilio voice provider config (Session 548)
 *
 * Seeds the `voice_provider_configs` row consumed by
 * App\Services\AI\VoiceService::loadProviders() / selectProvider() - without it the
 * AI voice-calling layer reports "No voice provider configured".
 *
 * Credentials are read at runtime from service_configs (encrypted at rest,
 * decrypted in-memory by ServiceConfigService) - nothing is hardcoded here.
 *
 * Idempotent: skips if the twilio row already exists.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// 1. Resolve Twilio credentials (encrypted in service_configs)
$svc = \App\Services\ServiceConfigService::getInstance();
$cfg = $svc->getApiConfig('twilio');
$sid = $cfg['account_sid'] ?? '';
$token = $cfg['auth_token'] ?? '';
$number = $cfg['from_number'] ?? '+18126684585';

if ($sid === '' || $token === '') {
    echo "SKIP voice_provider_configs - Twilio credentials not present in service_configs\n";
    return;
}

// 2. Skip if already seeded
$exists = $pdo->prepare("SELECT COUNT(*) FROM voice_provider_configs WHERE provider_name = 'twilio'");
$exists->execute();
if ((int)$exists->fetchColumn() > 0) {
    echo "SKIP voice_provider_configs#twilio - already exists\n";
    return;
}

// 3. Insert - API key/secret double as the SID/token for the AI voice layer
$stmt = $pdo->prepare("INSERT INTO voice_provider_configs
    (provider_name, display_name, api_key, api_secret, account_sid, auth_token, from_number, webhook_url, is_active, is_default, priority, tenant_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, 1, 1)");
$stmt->execute([
    'twilio',
    'Twilio (Global)',
    $sid,
    $token,
    $sid,
    $token,
    $number,
    (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/api/twilio/voice',
]);
echo "SEEDED voice_provider_configs#twilio (Twilio, number={$number})\n";
