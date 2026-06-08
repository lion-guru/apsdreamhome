<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo 'kyc_requests: ' . $pdo->query('SELECT COUNT(*) FROM kyc_requests')->fetchColumn() . "\n";
echo 'employees: ' . $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn() . "\n";
echo 'associates: ' . $pdo->query('SELECT COUNT(*) FROM associates')->fetchColumn() . "\n";
