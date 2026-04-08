<?php
/**
 * Migration: Create API Keys Table
 * 
 * This table stores API keys securely in database instead of .env file
 * to prevent API key leaks in version control
 * 
 * USAGE: php database/migrations/create_api_keys_table.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database;

echo "🔐 Creating API Keys Table...\n";

try {
    $db = Database::getInstance();

    $sql = "CREATE TABLE IF NOT EXISTS api_keys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        key_name VARCHAR(100) NOT NULL UNIQUE,
        key_value TEXT NOT NULL,
        description VARCHAR(255) DEFAULT NULL,
        is_encrypted TINYINT(1) DEFAULT 1,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_key_name (key_name),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);
    echo "✅ API Keys table created successfully\n";

    // Insert default placeholder API keys
    $apiKeys = [
        [
            'key_name' => 'GEMINI_API_KEY',
            'key_value' => 'AIzaSyYOUR_GEMINI_KEY',
            'description' => 'Google Gemini AI API Key for chatbot and AI features'
        ],
        [
            'key_name' => 'GEMINI_PROJECT_ID',
            'key_value' => 'YOUR_PROJECT_ID',
            'description' => 'Google Gemini Project ID'
        ],
        [
            'key_name' => 'WHATSAPP_COUNTRY_CODE',
            'key_value' => '919876543210',
            'description' => 'WhatsApp Business Country Code'
        ]
    ];

    $inserted = 0;
    foreach ($apiKeys as $key) {
        // Encrypt the value before storing
        $salt = substr(md5($_SERVER['HTTP_HOST'] ?? 'localhost'), 0, 8);
        $encryptedValue = base64_encode($salt . $key['key_value']);

        $checkSql = "SELECT id FROM api_keys WHERE key_name = :key_name";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([':key_name' => $key['key_name']]);

        if (!$checkStmt->fetch()) {
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
        } else {
            echo "⚠️  Skipped (exists): {$key['key_name']}\n";
        }
    }

    echo "\n🎉 Migration completed!\n";
    echo "📊 Total API keys inserted: {$inserted}\n";
    echo "\n💡 Next Steps:\n";
    echo "   1. Get API keys from providers\n";
    echo "   2. Add keys to .env file\n";
    echo "   3. Use admin panel to configure keys\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
