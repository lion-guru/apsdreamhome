<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$sql = "SELECT DISTINCT status FROM properties";
$stmt = $pdo->query($sql);
$statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Available statuses: " . implode(", ", $statuses) . "\n";

$sql2 = "SELECT COUNT(*) as c FROM properties";
$stmt2 = $pdo->query($sql2);
echo "Total properties: " . $stmt2->fetch()['c'] . "\n";
