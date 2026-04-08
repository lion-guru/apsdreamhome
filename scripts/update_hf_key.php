<?php
/**
 * Hugging Face API Key Update Script
 * 
 * USAGE:
 * php scripts/update_hf_key.php hf_YOUR_NEW_KEY_HERE
 * 
 * IMPORTANT:
 * - Never commit real API keys to Git!
 * - Use .env file for secrets
 */

// Get key from command line argument
$key = $argv[1] ?? '';

if (empty($key)) {
    echo "Usage: php scripts/update_hf_key.php hf_YOUR_NEW_KEY\n";
    echo "Example: php scripts/update_hf_key.php hf_abc123\n";
    exit(1);
}

if (!preg_match('/^hf_[a-zA-Z0-9]+$/', $key)) {
    echo "❌ Invalid Hugging Face key format. Should start with 'hf_'\n";
    exit(1);
}

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$stmt = $pdo->prepare("UPDATE api_keys SET key_value = ?, description = ?, is_active = 1 WHERE key_name = ?");
$stmt->execute([$key, "Hugging Face API key - UPDATED", "HUGGING_FACE_API_KEY"]);
echo "✅ Updated Hugging Face key in DB\n";

// Update .env
$env = file_get_contents('.env');
if (preg_match('/HUGGING_FACE_API_KEY=/', $env)) {
    $env = preg_replace('/HUGGING_FACE_API_KEY=.*/', 'HUGGING_FACE_API_KEY=' . $key, $env);
    file_put_contents('.env', $env);
    echo "✅ Updated Hugging Face key in .env\n";
}

// Verify
$row = $pdo->query("SELECT * FROM api_keys WHERE key_name = 'HUGGING_FACE_API_KEY'")->fetch();
echo "Status: " . ($row['is_active'] ? 'Active' : 'Inactive') . "\n";
echo "Value: " . substr($row['key_value'], 0, 20) . "...\n";
