<?php
/**
 * Seed API Keys into Database (Legacy Script)
 * 
 * WARNING: This script contains placeholder keys only.
 * Real keys should be added via .env and the admin panel.
 * 
 * USAGE: php scripts/seed_api_keys.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$existing = $pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();

if ($existing == 0) {
    // Placeholder keys - Replace with real keys from .env
    $keys = [
        ["openai", "gpt-4o", "sk-proj-YOUR_OPENAI_KEY", "https://api.openai.com/v1", 0, 0, "100", 0, "2025-12-31"],
        ["google", "gemini-2.0-flash-exp", "AIzaSyYOUR_GEMINI_KEY", "https://generativelanguage.googleapis.com/v1", 0, 0, "60", 0, "2025-12-31"],
        ["openrouter", "qwen/qwen3-coder:free", "sk-or-v1-YOUR_OPENROUTER_KEY", "https://openrouter.ai/api/v1", 0, 1, "unlimited", 0, null],
        ["anthropic", "claude-3-5-sonnet-20241022", "sk-ant-api03-YOUR_ANTHROPIC_KEY", "https://api.anthropic.com/v1", 0, 0, "50", 0, null],
    ];
    $stmt = $pdo->prepare("INSERT INTO api_keys (provider, model_name, api_key, endpoint, is_active, is_free, monthly_limit, used_this_month, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($keys as $k) { $stmt->execute($k); echo "Added: {$k[0]} - {$k[1]}\n"; }
} else {
    echo "Keys already exist: $existing\n";
}
echo "Total: " . $pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn() . "\n";
