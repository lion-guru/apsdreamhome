<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query('DESCRIBE properties');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "All columns in properties table:\n";
foreach($cols as $col) {
    echo $col['Field'] . "\n";
}
