<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root', getenv('DB_PASS') ?: '2jcePXuNaOfEyo6I5wJVkG');
$rows = $pdo->query("SELECT url FROM admin_menu_items WHERE is_active=1 AND url IS NOT NULL AND url != '' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
$rows = array_values(array_unique(array_filter($rows, fn($u)=>str_starts_with($u,'/'))));
file_put_contents(__DIR__.'/../testing/visual_tests/admin_menu_urls.json', json_encode($rows, JSON_PRETTY_PRINT));
echo count($rows) . " urls dumped\n";
foreach(array_slice($rows,0,5) as $u) echo $u . "\n";
