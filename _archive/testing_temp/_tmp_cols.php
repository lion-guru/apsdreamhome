<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$rows = $pdo->query('SHOW COLUMNS FROM mlm_profiles')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo $r['Field'] . ' ' . $r['Type'] . "\n";?>