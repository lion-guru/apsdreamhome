<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Add missing slug column
try {
    $db->exec("ALTER TABLE legal_documents ADD COLUMN slug VARCHAR(255) NULL AFTER title");
    echo "Added slug column\n";
} catch (Exception $e) {
    echo "slug column: " . $e->getMessage() . "\n";
}

// Add status column if missing
try {
    $db->exec("ALTER TABLE legal_documents ADD COLUMN status ENUM('draft','published','archived') DEFAULT 'draft' AFTER is_mandatory");
    echo "Added status column\n";
} catch (Exception $e) {
    echo "status column: " . $e->getMessage() . "\n";
}

// Update existing rows with slugs
$rows = $db->query("SELECT id, title FROM legal_documents WHERE slug IS NULL OR slug = ''")->fetchAll();
foreach ($rows as $row) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $row['title']));
    $slug = trim($slug, '-');
    $originalSlug = $slug;
    $counter = 1;
    while (true) {
        $existing = $db->query("SELECT id FROM legal_documents WHERE slug = ?", [$slug])->fetch();
        if (!$existing) break;
        $slug = $originalSlug . '-' . $counter++;
    }
    $db->exec("UPDATE legal_documents SET slug = ? WHERE id = ?", [$slug, $row['id']]);
    echo "Updated row {$row['id']} with slug: $slug\n";
}

// Add unique index
try {
    $db->exec("ALTER TABLE legal_documents ADD UNIQUE INDEX idx_slug (slug)");
    echo "Unique index on slug added\n";
} catch (Exception $e) {
    echo "Index: " . $e->getMessage() . "\n";
}

// Verify
$cols = $db->query('SHOW COLUMNS FROM legal_documents')->fetchAll(\PDO::FETCH_COLUMN, 0);
print_r($cols);