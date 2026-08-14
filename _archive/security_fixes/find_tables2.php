<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$pdo = $app->make('db')->getConnection();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    if (stripos($t, 'state') !== false || stripos($t, 'district') !== false || stripos($t, 'city') !== false || stripos($t, 'pincode') !== false || stripos($t, 'pin_code') !== false || stripos($t, 'bank') !== false || stripos($t, 'ifsc') !== false) {
        echo "$t\n";
    }
}?>