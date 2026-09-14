<?php
require 'C:\\xampp\\htdocs\\apsdreamhome\\config\\bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$stmt = $pdo->query("SELECT url FROM admin_menu_items WHERE url IS NOT NULL AND url != '#' ORDER BY url");
$urls = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $u = trim($row['url']);
    if (!$u || $u === '/') continue;
    if (!str_starts_with($u, '/')) $u = '/' . $u;
    $urls[] = $u;
}

$urls = array_values(array_unique($urls));
sort($urls);

$out = implode("\n", $urls) . "\n";
file_put_contents('C:\\Users\\abhay\\AppData\\Local\\Temp\\admin_urls.txt', $out);
echo "Total unique URLs: " . count($urls) . "\n";?>