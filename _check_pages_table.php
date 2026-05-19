<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

echo "=== pages table ===\n";
$cols = $db->query("SHOW COLUMNS FROM pages");
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']}) Null:{$c['Null']} Default:{$c['Default']}\n";

echo "\n=== pages data ===\n";
$data = $db->query("SELECT * FROM pages");
if ($data && $data->rowCount()) foreach ($data as $r) echo json_encode($r) . "\n";
else echo "(empty)\n";

echo "\n=== site_content table ===\n";
$cols2 = $db->query("SHOW COLUMNS FROM site_content");
foreach ($cols2 as $c) echo "  {$c['Field']} ({$c['Type']})\n";

echo "\n=== site_content data ===\n";
$data2 = $db->query("SELECT * FROM site_content LIMIT 5");
if ($data2 && $data2->rowCount()) foreach ($data2 as $r) echo json_encode($r) . "\n";
else echo "(empty)\n";

echo "\n=== page_templates table ===\n";
$cols3 = $db->query("SHOW COLUMNS FROM page_templates");
foreach ($cols3 as $c) echo "  {$c['Field']} ({$c['Type']})\n";
