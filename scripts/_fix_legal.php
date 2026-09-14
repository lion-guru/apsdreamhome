<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Update legal documents with proper descriptions and file paths
$updates = [
    'privacy-policy' => ['Privacy Policy - APS Dream Home', 'assets/documents/privacy-policy.pdf', 'Our commitment to protecting your personal data and privacy rights.'],
    'terms-conditions' => ['Terms & Conditions - APS Dream Home', 'assets/documents/terms-conditions.pdf', 'Full terms and conditions governing your use of our platform.'],
    'refund-policy' => ['Refund Policy - APS Dream Home', 'assets/documents/refund-policy.pdf', 'Our refund policy as per Master Deed V8 — all bookings are 100% non-refundable.'],
    'cancellation-policy' => ['Cancellation Policy - APS Dream Home', 'assets/documents/cancellation-policy.pdf', 'Cancellation rules and deduction structure per Master Deed V8.'],
    'disclaimer' => ['Disclaimer - APS Dream Home', 'assets/documents/disclaimer.pdf', 'Legal disclaimer and limitation of liability.'],
    'associate-rules' => ['Associate Code of Conduct - APS Dream Home', 'assets/documents/associate-rules.pdf', 'Code of conduct for all associates and agents.'],
];

foreach ($updates as $slug => $data) {
    $stmt = $pdo->prepare("UPDATE legal_documents SET title=?, description=?, file_path=?, updated_at=NOW() WHERE slug=? AND status='active'");
    $stmt->execute([$data[0], $data[2], $data[1], $slug]);
    echo "Updated: $slug -> {$data[0]}\n";
}

// Verify
$stmt2 = $pdo->query("SELECT slug, title, description, file_path FROM legal_documents WHERE slug IN ('privacy-policy','terms-conditions','refund-policy','cancellation-policy','disclaimer','associate-rules')");
echo "\n=== Verified ===\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['slug']}: {$r['title']}\n";
    echo "    desc: {$r['description']}\n";
    echo "    file: {$r['file_path']}\n";
}
