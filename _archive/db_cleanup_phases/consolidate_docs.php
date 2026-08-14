<?php
/**
 * DOCUMENT CONSOLIDATION: 6 entity-specific tables -> 1 polymorphic documents
 *
 * Current state:
 *   documents (6 rows, 14 refs, 2 FKs) - main table
 *   business_documents (3 rows, 2 refs)
 *   customer_documents (3 rows, 4 refs)
 *   employee_documents (3 rows, 9 refs)
 *   farmer_documents (3 rows, 5 refs)
 *   user_documents (3 rows, 3 refs)
 *   property_documents (3 rows, 7 refs)
 *
 * Plan:
 *   1. Add entity_type + entity_id to documents
 *   2. Migrate data from 6 entity tables (18 rows)
 *   3. Drop 6 entity tables
 *   4. Update code references
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== DOCUMENT CONSOLIDATION ===\n\n";

// Step 1: Backup all 6 entity tables
echo "1. Backing up entity tables...\n";
$pdo->exec("DROP TABLE IF EXISTS entity_docs_backup_20260603");
$pdo->exec("CREATE TABLE entity_docs_backup_20260603 AS SELECT * FROM business_documents");
$pdo->exec("INSERT INTO entity_docs_backup_20260603 SELECT * FROM customer_documents");
$pdo->exec("INSERT INTO entity_docs_backup_20260603 SELECT * FROM employee_documents");
$pdo->exec("INSERT INTO entity_docs_backup_20260603 SELECT * FROM farmer_documents");
$pdo->exec("INSERT INTO entity_docs_backup_20260603 SELECT * FROM user_documents");
$pdo->exec("INSERT INTO entity_docs_backup_20260603 SELECT * FROM property_documents");
$backupRows = $pdo->query("SELECT COUNT(*) FROM entity_docs_backup_20260603")->fetchColumn();
echo "   Backed up $backupRows rows\n\n";

// Step 2: Add entity_type + entity_id to documents
echo "2. Adding polymorphic columns to documents...\n";
$existingCols = array_column($pdo->query("DESCRIBE documents")->fetchAll(PDO::FETCH_ASSOC), 'Field');
if (!in_array('entity_type', $existingCols)) {
    $pdo->exec("ALTER TABLE documents ADD COLUMN entity_type VARCHAR(50) NULL AFTER user_id");
    $pdo->exec("ALTER TABLE documents ADD COLUMN entity_id BIGINT UNSIGNED NULL AFTER entity_type");
    $pdo->exec("ALTER TABLE documents ADD COLUMN original_table VARCHAR(50) NULL");
    echo "   Added entity_type, entity_id, original_table\n";
} else {
    echo "   Columns already exist\n";
}
echo "\n";

// Step 3: Migrate data from each entity table
echo "3. Migrating entity data...\n";

$migrations = [
    'business_documents' => [
        'entity_type' => 'business',
        'id_col' => 'case_id',
        'map' => ['doc_type' => 'document_type', 'file_path' => 'url'],
    ],
    'customer_documents' => [
        'entity_type' => 'customer',
        'id_col' => 'customer_id',
        'map' => ['document_type' => 'document_type', 'file_path' => 'url', 'document_name' => 'type'],
    ],
    'employee_documents' => [
        'entity_type' => 'employee',
        'id_col' => 'employee_id',
        'map' => ['document_type' => 'document_type', 'file_path' => 'url'],
    ],
    'farmer_documents' => [
        'entity_type' => 'farmer',
        'id_col' => 'farmer_id',
        'map' => ['document_type' => 'document_type', 'file_path' => 'url'],
    ],
    'user_documents' => [
        'entity_type' => 'user',
        'id_col' => 'user_id',
        'map' => ['document_type' => 'document_type', 'file_path' => 'url'],
    ],
    'property_documents' => [
        'entity_type' => 'property',
        'id_col' => 'property_id',
        'map' => ['document_type' => 'document_type', 'file_path' => 'url'],
    ],
];

$migrated = 0;
foreach ($migrations as $table => $cfg) {
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $entityId = $row[$cfg['id_col']] ?? null;
        $docType = $row[$cfg['map']['document_type']] ?? 'other';
        $url = $row[$cfg['map']['file_path']] ?? null;
        $name = $row[$cfg['map']['document_name'] ?? ''] ?? null;

        $pdo->prepare("INSERT INTO documents (entity_type, entity_id, document_type, url, original_table, uploaded_on) VALUES (?, ?, ?, ?, ?, NOW())")
            ->execute([$cfg['entity_type'], $entityId, $docType, $url, $table]);
        $migrated++;
    }
    echo "   Migrated {$table}: " . count($rows) . " rows\n";
}
echo "   Total migrated: $migrated rows\n\n";

// Step 4: Drop entity tables
echo "4. Dropping entity tables...\n";
foreach (array_keys($migrations) as $table) {
    $pdo->exec("DROP TABLE `$table`");
    echo "   Dropped $table\n";
}
echo "\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables after: $after\n";
echo "Migration complete. Update code references next.\n";?>