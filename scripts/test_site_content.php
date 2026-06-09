<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$rows = $pdo->query('SELECT * FROM site_content WHERE section = "about" ORDER BY sort_order')->fetchAll(PDO::FETCH_ASSOC);
echo 'Rows: ' . count($rows) . PHP_EOL;
foreach ($rows as $r) { echo $r['content_key'] . ' = ' . substr($r['content_value'], 0, 40) . PHP_EOL; }
