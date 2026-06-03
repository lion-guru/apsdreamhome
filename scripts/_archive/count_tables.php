<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$count = $pdo->query("SHOW TABLES")->rowCount();
echo "Total tables after cleanup: $count\n";
