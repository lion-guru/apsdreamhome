<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
foreach ($pdo->query('SHOW TABLES LIKE "%kyc%"')->fetchAll(PDO::FETCH_COLUMN) as $t) {
    echo "$t\n";
}
