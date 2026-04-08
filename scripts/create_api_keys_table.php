<?php
/**
 * Create api_keys table for AI API key management
 * 
 * USAGE: php scripts/create_api_keys_table.php
 * 
 * NOTE: This script creates the table with placeholder keys.
 * Real keys should be configured via admin panel or .env
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");

$sql = "CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(50) NOT NULL COMMENT 'openai, anthropic, google, openrouter, huggingface, groq, cohere',
    model_name VARCHAR(100) NOT NULL COMMENT 'gpt-4, claude-3-opus, gemini-pro, etc.',
    api_key VARCHAR(500) NOT NULL,
    endpoint VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
    is_free TINYINT(1) DEFAULT 0 COMMENT '1=free tier, 0=paid',
    monthly_limit VARCHAR(50) DEFAULT 'unlimited' COMMENT 'e.g. 100, 500, unlimited',
    used_this_month INT DEFAULT 0,
    cost_this_month DECIMAL(10,2) DEFAULT 0,
    last_used_at DATETIME DEFAULT NULL,
    expires_at DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_provider (provider),
    INDEX idx_is_active (is_active),
    INDEX idx_is_free (is_free)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql);
echo "✅ api_keys table created\n";

// Seed with placeholder keys
$existing = $pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn();
if ($existing == 0) {
    $keys = [
        ['openai', 'gpt-4o', 'sk-proj-YOUR_OPENAI_KEY', 'https://api.openai.com/v1', 0, 0, '100', 0, '2025-12-31'],
        ['google', 'gemini-2.0-flash-exp', 'AIzaSyYOUR_GEMINI_KEY', 'https://generativelanguage.googleapis.com/v1', 0, 0, '60', 0, '2025-12-31'],
        ['openrouter', 'qwen/qwen3-coder:free', 'sk-or-v1-YOUR_OPENROUTER_KEY', 'https://openrouter.ai/api/v1', 0, 1, 'unlimited', 0, NULL],
        ['anthropic', 'claude-3-5-sonnet-20241022', 'sk-ant-api03-YOUR_ANTHROPIC_KEY', 'https://api.anthropic.com/v1', 0, 0, '50', 0, NULL],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO api_keys (provider, model_name, api_key, endpoint, is_active, is_free, monthly_limit, used_this_month, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($keys as $k) {
        $stmt->execute($k);
        echo "✅ Added placeholder: {$k[0]} - {$k[1]}\n";
    }
} else {
    echo "ℹ️  Keys already exist, skipping seed\n";
}

echo "\n📊 Total keys: " . $pdo->query("SELECT COUNT(*) FROM api_keys")->fetchColumn() . "\n";
echo "\n⚠️  IMPORTANT: Replace placeholder keys with real API keys:\n";
echo "   1. OpenAI: https://platform.openai.com/api-keys\n";
echo "   2. Gemini: https://aistudio.google.com/apikey\n";
echo "   3. OpenRouter: https://openrouter.ai/keys\n";
echo "   4. Anthropic: https://console.anthropic.com/settings/keys\n";
echo "   Use admin panel to set real keys\n";
