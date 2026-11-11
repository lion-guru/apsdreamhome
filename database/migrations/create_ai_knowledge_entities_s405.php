<?php
/**
 * Migration: Create AI knowledge entity tables for RAG system
 * 
 * Creates missing tables:
 * - ai_knowledge_entities: Entity storage for RAG knowledge graph
 * - ai_knowledge_relations: Relationships between knowledge entities
 * 
 * Idempotent: uses CREATE TABLE IF NOT EXISTS
 * 
 * Run via: php database/migrations/create_ai_knowledge_entities_s405.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';
use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$created = 0;
$skipped = 0;

function tableExists(PDO $pdo, string $table): bool {
    $row = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    return $row !== false;
}

function execMigration(PDO $pdo, string $name, string $sql): void {
    global $created, $skipped;
    if (tableExists($pdo, $name)) {
        echo "⏭️  SKIP: $name already exists\n";
        $skipped++;
        return;
    }
    try {
        $pdo->exec($sql);
        echo "✅ Created: $name\n";
        $created++;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $name: " . $e->getMessage() . "\n";
    }
}

// 1. ai_knowledge_entities - note: ai_knowledge_base.id is INT(11) signed
execMigration($pdo, 'ai_knowledge_entities', "
CREATE TABLE ai_knowledge_entities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    knowledge_base_id INT(11) NOT NULL,
    entity_name VARCHAR(255) NOT NULL,
    entity_type ENUM('person','organization','location','property','concept','event','other'),
    canonical_name VARCHAR(255),
    aliases JSON,
    properties JSON,
    embedding TEXT,
    confidence_score DECIMAL(5,4) DEFAULT 1.0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (knowledge_base_id) REFERENCES ai_knowledge_base(id) ON DELETE CASCADE,
    INDEX idx_kb_id (knowledge_base_id),
    INDEX idx_entity_name (entity_name),
    INDEX idx_entity_type (entity_type),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// 2. ai_knowledge_relations - entity types are INT UNSIGNED (matching ai_knowledge_entities.id)
execMigration($pdo, 'ai_knowledge_relations', "
CREATE TABLE ai_knowledge_relations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_entity_id INT UNSIGNED NOT NULL,
    target_entity_id INT UNSIGNED NOT NULL,
    relationship_type VARCHAR(100) NOT NULL,
    relationship_strength DECIMAL(5,4) DEFAULT 1.0,
    evidence TEXT,
    confidence_score DECIMAL(5,4) DEFAULT 1.0,
    tenant_id INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_entity_id) REFERENCES ai_knowledge_entities(id) ON DELETE CASCADE,
    FOREIGN KEY (target_entity_id) REFERENCES ai_knowledge_entities(id) ON DELETE CASCADE,
    INDEX idx_source (source_entity_id),
    INDEX idx_target (target_entity_id),
    INDEX idx_relation_type (relationship_type),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "\n=== Migration Summary ===\n";
echo "Created: $created tables\n";
echo "Skipped: $skipped tables (already exist)\n";
echo "\n✅ AI Knowledge Entity migration complete\n";