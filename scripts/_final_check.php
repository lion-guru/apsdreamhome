<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';

// Quick HTTP check for key pages using cURL
$pages = [
    '/', ' /properties', '/colonies', '/tools-hub', '/projects', '/team',
    '/legal', '/terms-conditions', '/privacy-policy', '/refund-policy',
    '/cancellation-policy', '/disclaimer', '/associate-rules',
    '/associate/book-plot', '/associate/login?test_login=1',
];

$results = [];
foreach ($pages as $page) {
    $url = 'http://localhost/apsdreamhome' . trim($page);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $results[] = "$url => $httpCode";
}

echo implode("\n", $results) . "\n";

// Verify associate controller bookPlot syntax
exec("php -l C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/AssociateController.php 2>&1", $out);
echo "\n" . implode(" ", $out) . "\n";

// Verify legal docs
$pdo = \App\Core\Database\Database::getInstance()->getConnection();
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM legal_documents WHERE status = 'active' AND slug IN ('privacy-policy', 'terms-conditions', 'refund-policy', 'cancellation-policy', 'disclaimer', 'associate-rules')");
echo "\nLegal pages in legal_documents: " . $stmt->fetchColumn() . "\n";

// Verify terms_consent checkbox in book_plot.php
$content = file_get_contents('C:/xampp/htdocs/apsdreamhome/app/views/associate/book_plot.php');
echo "terms_consent in book_plot.php: " . (strpos($content, 'terms_consent') !== false ? 'YES' : 'NO') . "\n";
echo "Legal links in book_plot.php: " . (strpos($content, '/terms-conditions') !== false ? 'YES' : 'NO') . "\n";
echo "terms_consent validation in AssociateController: " . (strpos(file_get_contents('C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/AssociateController.php'), 'terms_consent') !== false ? 'YES' : 'NO') . "\n";
