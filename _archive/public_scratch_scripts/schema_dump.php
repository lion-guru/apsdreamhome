<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query("DESCRIBE sites");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));?>