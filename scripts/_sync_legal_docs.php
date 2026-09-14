<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Get content from pages table for each legal slug
$slugs = ['privacy-policy', 'terms-conditions', 'refund-policy', 'cancellation-policy', 'disclaimer', 'associate-rules'];
$categories_map = [
    'privacy-policy' => ['category' => 'data_protection', 'document_type' => 'privacy_policy', 'is_mandatory' => 1, 'version' => 'V8', 'template_id' => 0],
    'terms-conditions' => ['category' => 'general', 'document_type' => 'terms', 'is_mandatory' => 1, 'version' => 'V8', 'template_id' => 0],
    'refund-policy' => ['category' => 'financial', 'document_type' => 'refund', 'is_mandatory' => 1, 'version' => 'V8', 'template_id' => 0],
    'cancellation-policy' => ['category' => 'financial', 'document_type' => 'cancellation', 'is_mandatory' => 1, 'version' => 'V8', 'template_id' => 0],
    'disclaimer' => ['category' => 'general', 'document_type' => 'disclaimer', 'is_mandatory' => 0, 'version' => 'V1', 'template_id' => 0],
    'associate-rules' => ['category' => 'associate', 'document_type' => 'code_of_conduct', 'is_mandatory' => 1, 'version' => 'V8', 'template_id' => 0],
];

foreach ($slugs as $slug) {
    // Get content from pages table
    $stmt = $pdo->prepare("SELECT title, content, meta_description FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$page) {
        echo "SKIP: $slug not found or not published in pages table\n";
        continue;
    }

    $info = $categories_map[$slug];
    $title = $page['title'];
    $content = $page['content'];
    $summary = substr(strip_tags($content), 0, 500);

    // Check if already exists in legal_documents
    $check = $pdo->prepare("SELECT id FROM legal_documents WHERE slug = ? LIMIT 1");
    $check->execute([$slug]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "UPDATE: $slug already exists in legal_documents (id={$existing['id']})\n";
        $update = $pdo->prepare("UPDATE legal_documents SET title=?, content=?, summary=?, document_type=?, category=?, is_mandatory=?, version=?, status='active', description=?, slug=?, updated_at=NOW() WHERE id=?");
        $update->execute([
            $title, $content, $summary, $info['document_type'], $info['category'],
            $info['is_mandatory'], $info['version'], $slug . ' - APS Dream Home', $slug, $existing['id']
        ]);
    } else {
        echo "INSERT: $slug into legal_documents\n";
        $insert = $pdo->prepare("INSERT INTO legal_documents (tenant_id, title, slug, content, summary, document_type, category, is_mandatory, version, description, status, published_at, published_date, file_path, created_at) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW(), '', NOW())");
        $insert->execute([
            $title, $slug, $content, $summary, $info['document_type'], $info['category'],
            $info['is_mandatory'], $info['version'], $slug . ' - APS Dream Home'
        ]);
    }
}

// Verify
$stmt2 = $pdo->query("SELECT id, title, slug, document_type, status FROM legal_documents WHERE slug IN ('privacy-policy', 'terms-conditions', 'refund-policy', 'cancellation-policy', 'disclaimer', 'associate-rules')");
echo "\n=== Verified legal_documents ===\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "  id={$r['id']} title={$r['title']} type={$r['document_type']} status={$r['status']}\n";
}

// Count total
$stmt3 = $pdo->query("SELECT COUNT(*) as cnt FROM legal_documents WHERE status = 'active'");
$r = $stmt3->fetch(PDO::FETCH_ASSOC);
echo "\nTotal active legal_documents: {$r['cnt']}\n";
