<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$r = $db->query("SHOW TABLES LIKE '%attend%'");
foreach ($r as $row) echo reset($row) . PHP_EOL;
$r = $db->query("SHOW TABLES LIKE '%check%'");
foreach ($r as $row) echo reset($row) . PHP_EOL;
