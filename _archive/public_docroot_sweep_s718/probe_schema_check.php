<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4','root','');
$cols = $pdo->query('DESCRIBE admin_menu_items')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'].' ('.$c['Type'].')'.PHP_EOL;
