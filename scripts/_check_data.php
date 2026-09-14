<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== plot_bookings ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM plot_bookings");
echo "Count: " . $stmt->fetchColumn() . "\n";

echo "\n=== Active legal_documents ===\n";
$stmt2 = $pdo->query("SELECT id, title, document_type FROM legal_documents WHERE status = 'active' ORDER BY title");
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['id']} {$r['title']} [{$r['document_type']}]\n";
}

echo "\n=== Specific legal pages ===\n";
$stmt3 = $pdo->query("SELECT id, title, document_type FROM legal_documents WHERE slug IN ('privacy-policy','terms-conditions','refund-policy','cancellation-policy','disclaimer','associate-rules') ORDER BY title");
while ($r = $stmt3->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['id']} {$r['title']} [{$r['document_type']}]\n";
}
