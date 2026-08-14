<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$docTables = [
    'documents', 'business_documents', 'customer_documents', 'employee_documents',
    'farmer_documents', 'user_documents', 'property_documents',
    'document_types', 'document_categories', 'document_gallery',
    'document_classification', 'document_reviews', 'document_templates',
    'generated_documents', 'legal_documents', 'ocr_documents',
];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

foreach ($docTables as $t) {
    $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='$t'")->fetchColumn();
    if (!$exists) { echo "[MISSING] $t\n"; continue; }

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) { $codeRef += preg_match_all($pattern, $content); }
    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();

    echo sprintf("%-30s %3d rows  %2d refs  %d FKs\n", $t, $rows, $codeRef, $fkTo);
}?>