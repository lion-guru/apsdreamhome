<?php
/**
 * Migration: Save active OpenRouter and OpenCode API keys in ai_settings
 * Session: 334
 * Idempotent: Safe to re-run, checks before updating
 */
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $row = $db->fetch("SELECT * FROM ai_settings WHERE id = 1");

    if (!$row) {
        echo "ai_settings row #1 not found. Skipping.\n";
        exit(0);
    }

    $newOpenrouterKey = getenv('OPENROUTER_API_KEY') ?: '';
    $openrouterMgmtKey = getenv('OPENROUTER_MANAGEMENT_KEY') ?: '';
    $opencodeZenKey = getenv('OPENCODE_ZEN_API_KEY') ?: '';

    $cfg = json_decode((string)($row['settings'] ?? '{}'), true) ?: [];

    if ($newOpenrouterKey === '' || $openrouterMgmtKey === '' || $opencodeZenKey === '') {
        echo "SKIP: required OpenRouter/OpenCode environment variables are not set.\n";
        exit(0);
    }

    $alreadyUpdated = (
        ($row['openrouter_api_key'] ?? '') === $newOpenrouterKey &&
        ($cfg['openrouter_management_key'] ?? '') === $openrouterMgmtKey &&
        ($cfg['opencode_zen_api_key'] ?? '') === $opencodeZenKey
    );

    if ($alreadyUpdated) {
        echo "SKIP: ai_settings row #1 already has active keys set.\n";
        exit(0);
    }

    $cfg['openrouter_management_key'] = $openrouterMgmtKey;
    $cfg['opencode_zen_api_key'] = $opencodeZenKey;

    $updatedJson = json_encode($cfg);

    $db->execute(
        "UPDATE ai_settings SET openrouter_api_key = ?, settings = ?, updated_at = NOW() WHERE id = 1",
        [$newOpenrouterKey, $updatedJson]
    );

    echo "UPDATED: ai_settings row #1 successfully updated with OpenRouter and OpenCode keys.\n";
} catch (\Throwable $e) {
    echo "ERROR in migration: " . $e->getMessage() . "\n";
    exit(1);
}
