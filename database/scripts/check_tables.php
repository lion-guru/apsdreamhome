<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables in database '$db_name':\n";
    $hasVisits = false;
    $hasAppointments = false;

    foreach ($tables as $table) {
        echo "- $table\n";
        if ($table == 'visits') $hasVisits = true;
        if (strpos($table, 'appointment') !== false) $hasAppointments = true;
    }

    echo "\nAnalysis:\n";
    if ($hasVisits) echo "- 'visits' table found.\n";
    if ($hasAppointments) echo "- '*appointment*' table found.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
