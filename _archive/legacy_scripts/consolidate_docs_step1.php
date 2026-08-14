<?php
/**
 * DOCUMENT CONSOLIDATION: 6 entity tables -> polymorphic documents
 * Step 1: Schema + data migration
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== DOCUMENT CONSOLIDATION ===\n\n";

// Add polymorphic columns
$existingCols = array_column($pdo->query("DESCRIBE documents")->fetchAll(PDO::FETCH_ASSOC), 'Field');
if (!in_array('entity_type', $existingCols)) {
    $pdo->exec("ALTER TABLE documents ADD COLUMN entity_type VARCHAR(50) NULL AFTER user_id");
    $pdo->exec("ALTER TABLE documents ADD COLUMN entity_id BIGINT UNSIGNED NULL AFTER entity_type");
    echo "Added entity_type, entity_id\n";
}

// Migrate each entity table
$migrations = [
    'business_documents' => ['entity_type' => 'business', 'id_col' => 'case_id'],
    'customer_documents' => ['entity_type' => 'customer', 'id_col' => 'customer_id'],
    'employee_documents' => ['entity_type' => 'employee', 'id_col' => 'employee_id'],
    'farmer_documents' => ['entity_type' => 'farmer', 'id_col' => 'farmer_id'],
    'user_documents' => ['entity_type' => 'user', 'id_col' => 'user_id'],
    'property_documents' => ['entity_type' => 'property', 'id_col' => 'property_id'],
];

$total = 0;
foreach ($migrations as $table => $cfg) {
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $entityId = $row[$cfg['id_col']] ?? null;
        $docType = $row['document_type'] ?? $row['doc_type'] ?? 'other';
        $url = $row['file_path'] ?? null;
        $name = $row['document_name'] ?? null;
        $num = $row['document_number'] ?? null;
        $verified = isset($row['is_verified']) ? ($row['is_verified'] ? 'verified' : 'pending') : 'pending';

        $pdo->prepare("INSERT INTO documents (entity_type, entity_id, document_type, url, document_number, verification_status, uploaded_on) VALUES (?, ?, ?, ?, ?, ?, NOW())")
            ->execute([$cfg['entity_type'], $entityId, $docType, $url, $num, $verified]);
        $total++;
    }
    $pdo->exec("DROP TABLE `$table`");
    echo "Dropped $table (" . count($rows) . " rows migrated)\n";
}

echo "Total migrated: $total\n";
echo "Tables: " . $pdo->query('SHOW TABLES')->rowCount() . "\n";?>