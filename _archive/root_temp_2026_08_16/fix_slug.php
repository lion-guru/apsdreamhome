<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// First, update existing rows to have unique slugs
$rows = $db->query("SELECT id, title FROM legal_documents WHERE slug IS NULL OR slug = ''")->fetchAll();
foreach ($rows as $row) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $row['title']));
    $slug = trim($slug, '-');
    // Make unique
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

// Now add the unique constraint
try {
    $db->exec("ALTER TABLE legal_documents ADD UNIQUE INDEX idx_slug (slug)");
    echo "Unique index on slug added successfully!\n";
} catch (Exception $e) {
    echo "Index may already exist: " . $e->getMessage() . "\n";
}

// Verify
$cols = $db->query('SHOW COLUMNS FROM legal_documents')->fetchAll(\PDO::FETCH_COLUMN, 0);
print_r($cols);