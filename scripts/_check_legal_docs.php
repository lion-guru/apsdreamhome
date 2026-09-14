<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Check legal_documents table structure
$stmt = $pdo->query("DESCRIBE legal_documents");
echo "=== legal_documents columns ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['Field']} ({$r['Type']})\n";
}

// Check what's in legal_documents
$stmt2 = $pdo->query("SELECT id, title, slug, document_type, status FROM legal_documents ORDER BY document_type, title LIMIT 20");
echo "\n=== Existing legal_documents ===\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  id={$r['id']} title={$r['title']} slug={$r['slug']} type={$r['document_type']} status={$r['status']}\n";
}

// Check pages table for legal slugs
$stmt3 = $pdo->query("SELECT slug, title FROM pages WHERE slug LIKE '%legal%' OR slug LIKE '%terms%' OR slug LIKE '%privacy%' OR slug LIKE '%refund%' OR slug LIKE '%cancellation%' OR slug LIKE '%disclaimer%' OR slug LIKE '%associate-rules%'");
echo "\n=== CMS Pages for legal ===\n";
while ($r = $stmt3->fetch(PDO::FETCH_ASSOC)) {
    echo "  slug={$r['slug']} title={$r['title']}\n";
}
