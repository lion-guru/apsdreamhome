<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== ACTIVITIES TABLE STRUCTURE ===\n\n";
$cols = $pdo->query("SHOW COLUMNS FROM activities")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ') ' . ($c['Key'] ? "[KEY: {$c['Key']}]" : "") . ($c['Extra'] ? "[EXTRA: {$c['Extra']}]" : "") . "\n";
}

echo "\n=== CURRENT DATA ===\n";
$currentData = $pdo->query("SELECT * FROM activities LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
foreach ($currentData as $row) {
    echo json_encode($row, JSON_PRETTY_PRINT) . "\n\n";
}
