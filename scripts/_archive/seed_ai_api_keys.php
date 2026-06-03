<?php
/**
 * Seed AI API Keys into Database
 * 
 * This script seeds placeholder/dummy values for API keys.
 * Replace with real keys in .env file.
 * 
 * USAGE: php scripts/seed_ai_api_keys.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== Seed AI API Keys ===\n\n";

// AI Keys - These are PLACEHOLDERS. Replace with real keys in .env
$keys = [
    // AI Keys - Get real keys from respective providers
    ['OPENAI_API_KEY', 'sk-proj-YOUR_OPENAI_KEY_HERE', 'api_key', 'OpenAI', 'GPT-4o API key - Add your key to .env', 0],
    ['GEMINI_API_KEY', 'AIzaSyYOUR_GEMINI_KEY_HERE', 'api_key', 'Google Gemini', 'Gemini 2.0 Flash API key - Add your key to .env', 0],
    ['OPENROUTER_API_KEY', 'sk-or-v1-YOUR_OPENROUTER_KEY_HERE', 'api_key', 'OpenRouter', 'OpenRouter API key - Add your key to .env', 0],
    ['ANTHROPIC_API_KEY', 'sk-ant-api03-YOUR_ANTHROPIC_KEY_HERE', 'api_key', 'Anthropic Claude', 'Claude 3.5 Sonnet API key - Add your key to .env', 0],
    ['HUGGING_FACE_API_KEY', 'hf_YOUR_HUGGING_FACE_KEY_HERE', 'api_key', 'Hugging Face', 'Hugging Face API key - Add your key to .env', 0],
    
    // Placeholder keys
    ['GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_KEY_HERE', 'api_key', 'Google Maps', 'Google Maps JavaScript API key - PLACEHOLDER', 0],
    ['RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY_HERE', 'api_key', 'Google reCAPTCHA', 'reCAPTCHA site key - PLACEHOLDER', 0],
    ['RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY_HERE', 'api_key', 'Google reCAPTCHA', 'reCAPTCHA secret key - PLACEHOLDER', 0],
    ['WHATSAPP_ACCESS_TOKEN', 'YOUR_WHATSAPP_ACCESS_TOKEN', 'token', 'WhatsApp Business', 'WhatsApp Business API access token - PLACEHOLDER', 0],
    ['WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'YOUR_WEBHOOK_TOKEN', 'token', 'WhatsApp Business', 'WhatsApp webhook verification token - PLACEHOLDER', 0],
];

$stmt = $pdo->prepare("INSERT INTO api_keys (key_name, key_value, key_type, service_name, description, is_active) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE key_value=VALUES(key_value), service_name=VALUES(service_name), description=VALUES(description), is_active=VALUES(is_active)");

foreach ($keys as $k) {
    $stmt->execute($k);
    echo "Upserted: {$k[0]} ({$k[3]})\n";
}

echo "\n=== All API Keys ===\n";
$data = $pdo->query("SELECT id, key_name, service_name, key_type, is_active, usage_count, last_used_at FROM api_keys")->fetchAll(PDO::FETCH_ASSOC);
foreach ($data as $row) {
    $status = $row['is_active'] ? '✅' : '❌';
    echo "$status {$row['key_name']} | {$row['service_name']} | Used: {$row['usage_count']}\n";
}
echo "\nTotal: " . count($data) . " keys\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. Get API keys from providers:\n";
echo "   - OpenAI: https://platform.openai.com/api-keys\n";
echo "   - Gemini: https://aistudio.google.com/apikey\n";
echo "   - OpenRouter: https://openrouter.ai/keys\n";
echo "   - Anthropic: https://console.anthropic.com/settings/keys\n";
echo "   - HuggingFace: https://huggingface.co/settings/tokens\n";
echo "2. Add keys to .env file\n";
echo "3. Set is_active=1 for keys you're using\n";
