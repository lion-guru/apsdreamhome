<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();
$tables = ['legal_documents', 'legal_document_versions', 'legal_document_acceptances'];
foreach ($tables as $t) {
    $r = $db->query("SHOW TABLES LIKE '$t'")->fetch();
    echo $t . ': ' . ($r ? 'EXISTS' : 'MISSING') . "\n";
    if ($r) {
        $cols = $db->query("SHOW COLUMNS FROM $t")->fetchAll();
        echo "  Columns: " . count($cols) . "\n";
    }
}