<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Check what happened
$stmt = $pdo->query("SELECT slug, title, description, file_path FROM legal_documents WHERE status='active' ORDER BY title");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$r['slug']} | {$r['title']} | desc=" . substr($r['description'] ?? '', 40) . " | file={$r['file_path']}\n";
}

// Try updating with direct query
$pdo->query("UPDATE legal_documents SET description='Our commitment to protecting your personal data and privacy rights.', file_path='assets/documents/privacy-policy.pdf' WHERE slug='privacy-policy' AND status='active'");
echo "\nPrivacy updated\n";

$pdo->query("UPDATE legal_documents SET description='Full terms and conditions governing your use of our platform.', file_path='assets/documents/terms-conditions.pdf' WHERE slug='terms-conditions' AND status='active'");
echo "Terms updated\n";

$pdo->query("UPDATE legal_documents SET description='Our refund policy as per Master Deed V8.', file_path='assets/documents/refund-policy.pdf' WHERE slug='refund-policy' AND status='active'");
echo "Refund updated\n";

$pdo->query("UPDATE legal_documents SET description='Cancellation rules and deduction structure per Master Deed V8.', file_path='assets/documents/cancellation-policy.pdf' WHERE slug='cancellation-policy' AND status='active'");
echo "Cancellation updated\n";

$pdo->query("UPDATE legal_documents SET description='Legal disclaimer and limitation of liability.', file_path='assets/documents/disclaimer.pdf' WHERE slug='disclaimer' AND status='active'");
echo "Disclaimer updated\n";

$pdo->query("UPDATE legal_documents SET description='Code of conduct for all associates and agents.', file_path='assets/documents/associate-rules.pdf' WHERE slug='associate-rules' AND status='active'");
echo "Associate rules updated\n";

// Final verify
$stmt2 = $pdo->query("SELECT slug, title, description, file_path FROM legal_documents WHERE status='active' AND slug IN ('privacy-policy','terms-conditions','refund-policy','cancellation-policy','disclaimer','associate-rules')");
echo "\n=== Final verify ===\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['slug']}: desc=" . substr($r['description'] ?? '', 30) . " file={$r['file_path']}\n";
}
