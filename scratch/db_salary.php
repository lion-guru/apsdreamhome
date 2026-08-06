<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = $db->query("SHOW TABLES LIKE '%salary%'")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
$tables2 = $db->query("SHOW TABLES LIKE '%attendance%'")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables2);
$tables3 = $db->query("SHOW TABLES LIKE '%leave%'")->fetchAll(PDO::FETCH_COLUMN);
print_r($tables3);
