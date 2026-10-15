<?php
/**
 * Migration: Create RAG Knowledge Base tables
 * Run once to create ai_knowledge_base and ai_knowledge_chunks tables
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

echo "Creating RAG Knowledge Base tables...\n";

try {
    // ai_knowledge_base table
    $db->execute("
        CREATE TABLE IF NOT EXISTS ai_knowledge_base (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            source VARCHAR(100) DEFAULT 'manual',
            tags JSON,
            metadata JSON,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tenant (tenant_id),
            INDEX idx_active (is_active),
            INDEX idx_source (source)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ ai_knowledge_base created\n";

    // ai_knowledge_chunks table
    $db->execute("
        CREATE TABLE IF NOT EXISTS ai_knowledge_chunks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            knowledge_base_id INT NOT NULL,
            chunk_text LONGTEXT NOT NULL,
            embedding JSON NOT NULL,
            chunk_index INT DEFAULT 0,
            metadata JSON,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_kb (knowledge_base_id),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ ai_knowledge_chunks created\n";

    // Add is_active column if not exists
    $cols = $db->fetchAll("SHOW COLUMNS FROM ai_knowledge_base LIKE 'is_active'");
    if (empty($cols)) {
        $db->execute("ALTER TABLE ai_knowledge_base ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER metadata");
        echo "✅ is_active column added to ai_knowledge_base\n";
    }

    echo "\n✅ RAG Knowledge Base tables created successfully!\n";
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}