<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

// Check for existing CMS/pages tables
$tables = $db->query("SHOW TABLES LIKE '%page%'")->fetchAll();
echo "=== Tables matching '%page%' ===\n";
foreach ($tables as $t) echo "  " . current($t) . "\n";

$tables2 = $db->query("SHOW TABLES LIKE '%cms%'")->fetchAll();
echo "\n=== Tables matching '%cms%' ===\n";
foreach ($tables2 as $t) echo "  " . current($t) . "\n";

$tables3 = $db->query("SHOW TABLES LIKE '%content%'")->fetchAll();
echo "\n=== Tables matching '%content%' ===\n";
foreach ($tables3 as $t) echo "  " . current($t) . "\n";

// Check legal_pages structure
$check = $db->query("SHOW TABLES LIKE 'legal_pages'");
if ($check && $check->rowCount()) {
  echo "\n=== legal_pages structure ===\n";
  foreach ($db->query("SHOW COLUMNS FROM legal_pages") as $c) echo "  {$c['Field']} ({$c['Type']})\n";
  foreach ($db->query("SELECT * FROM legal_pages") as $r) echo "  > " . json_encode($r) . "\n";
}

echo "\n=== admin/pages route check ===\n";
$ch = curl_init('http://localhost/apsdreamhome/admin/login?test_login=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$resp = curl_exec($ch);
preg_match('/^Set-Cookie:\s*([^=]+=[^;]+)/mi', $resp, $m);
$cookie = $m[1] ?? '';
curl_close($ch);

$ch = curl_init('http://localhost/apsdreamhome/admin/pages');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_COOKIE, $cookie);
$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
preg_match('/Location: (.*)/i', $resp, $loc);
curl_close($ch);
echo "Admin Pages: HTTP $http Location: " . ($loc[1] ?? 'none') . "\n";
