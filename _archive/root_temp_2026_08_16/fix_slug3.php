<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Add slug column if missing
try {
    $db->exec("ALTER TABLE legal_documents ADD COLUMN slug VARCHAR(255) NULL AFTER title");
    echo "Added slug column\n";
} catch (Exception $e) {
    echo "slug column: " . $e->getMessage() . "\n";
}

// Update existing rows with slugs
$stmt = $db->prepare("SELECT id, title FROM legal_documents WHERE slug IS NULL OR slug = ''");
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $row['title']));
    $slug = trim($slug, '-');
    $originalSlug = $slug;
    $counter = 1;
    while (true) {
        $check = $db->prepare("SELECT id FROM legal_documents WHERE slug = ?");
        $check->execute([$slug]);
        if (!$check->fetch()) break;
        $slug = $originalSlug . '-' . $counter++;
    }
    $update = $db->prepare("UPDATE legal_documents SET slug = ? WHERE id = ?");
    $update->execute([$slug, $row['id']]);
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