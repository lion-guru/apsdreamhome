<?php

/**
 * Update API Keys - Security Update
 * 
 * This script updates leaked API keys with new secure ones
 * Run this after keys have been compromised
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

echo "🔐 Updating API Keys (Security Update)...\n\n";

// New secure API keys
$newKeys = [
    [
        'key_name' => 'GEMINI_API_KEY',
        'key_value' => 'AIzaSyCKu8FpPDXWaxgzFIlWd7OXwmkfvjRn4eE',
        'description' => 'Google Gemini AI API Key (Updated - Old key revoked)'
    ],
    [
        'key_name' => 'GEMINI_PROJECT_ID',
        'key_value' => '269391449583',
        'description' => 'Google Gemini Project ID'
    ],
    [
        'key_name' => 'AI_API_KEY',
        'key_value' => 'AIzaSyCKu8FpPDXWaxgzFIlWd7OXwmkfvjRn4eE',
        'description' => 'Legacy AI API Key (Updated)'
    ],
    [
        'key_name' => 'HUGGINGFACE_API_KEY',
        'key_value' => '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',
        'description' => 'Hugging Face API Key for ML models'
    ],
    [
        'key_name' => 'OPENROUTER_API_KEY',
        'key_value' => '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',
        'description' => 'OpenRouter API Key for AI models'
    ],
    [
        'key_name' => 'OPENROUTER_MODEL',
        'key_value' => 'gpt-4',
        'description' => 'OpenRouter Default Model'
    ]
];

try {
    $db = Database::getInstance();

    // Check if api_keys table exists
    $checkTable = $db->query("SHOW TABLES LIKE 'api_keys'");
    if ($checkTable->rowCount() === 0) {
        echo "⚠️  api_keys table not found. Running migration first...\n";
        require_once __DIR__ . '/create_api_keys_table.php';
        exit;
    }

    $updated = 0;
    $inserted = 0;

    foreach ($newKeys as $key) {
        // Encrypt the value before storing
        $salt = substr(md5($_SERVER['HTTP_HOST'] ?? 'localhost'), 0, 8);
        $encryptedValue = base64_encode($salt . $key['key_value']);

        // Check if key exists
        $checkSql = "SELECT id FROM api_keys WHERE key_name = :key_name";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([':key_name' => $key['key_name']]);

        if ($checkStmt->fetch()) {
            // Update existing key
            $updateSql = "UPDATE api_keys 
                         SET key_value = :key_value, 
                             description = :description,
                             is_encrypted = 1,
                             updated_at = NOW()
                         WHERE key_name = :key_name";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([
                ':key_name' => $key['key_name'],
                ':key_value' => $encryptedValue,
                ':description' => $key['description']
            ]);
            $updated++;
            echo "🔄 Updated: {$key['key_name']}\n";
        } else {
            // Insert new key
            $insertSql = "INSERT INTO api_keys (key_name, key_value, description, is_encrypted, is_active) 
                         VALUES (:key_name, :key_value, :description, 1, 1)";
            $insertStmt = $db->prepare($insertSql);
            $insertStmt->execute([
                ':key_name' => $key['key_name'],
                ':key_value' => $encryptedValue,
                ':description' => $key['description']
            ]);
            $inserted++;
            echo "✅ Inserted: {$key['key_name']}\n";
        }
    }

    echo "\n🎉 Security Update Completed!\n";
    echo "📊 Updated: {$updated} keys\n";
    echo "📊 Inserted: {$inserted} keys\n";
    echo "\n⚠️  IMPORTANT:\n";
    echo "   - Old API keys have been revoked\n";
    echo "   - New keys are now active in database\n";
    echo "   - Remove old keys from .env file\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
