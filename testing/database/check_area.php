<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'area_sqft'");
$result = $stmt->fetch();
echo $result ? 'area_sqft EXISTS' : 'area_sqft MISSING';
