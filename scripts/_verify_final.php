<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';

// Syntax check all modified files
$files = [
    'app/Http/Controllers/AssociateController.php',
    'app/views/associate/book_plot.php',
    'app/views/pages/booking/form.php',
    'app/views/pages/inquiry.php',
    'app/views/admin/sales/cancel-form.php',
];
foreach ($files as $f) {
    exec("php -l " . escapeshellarg($f) . " 2>&1", $out);
    echo "$f: " . implode(" ", $out) . "\n";
}

// Verify plot_bookings insert works
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM plot_bookings");
echo "\nplot_bookings count: " . $stmt->fetchColumn() . "\n";

// Verify legal_documents are all present
$stmt2 = $pdo->query("SELECT COUNT(*) as cnt FROM legal_documents WHERE status = 'active'");
echo "Active legal_documents: " . $stmt2->fetchColumn() . "\n";

// Clean up temp scripts
exec("rm -f C:/xampp/htdocs/apsdreamhome/scripts/_check_tables.php C:/xampp/htdocs/apsdreamhome/scripts/_check_legal_docs.php C:/xampp/htdocs/apsdreamhome/scripts/_sync_legal_docs.php 2>&1");
echo "\nTemp scripts cleaned.\n";
