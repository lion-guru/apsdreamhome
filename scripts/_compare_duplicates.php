<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== notifications ===\n";
foreach ($pdo->query('DESCRIBE notifications')->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  " . $c['Field'] . ' (' . $c['Type'] . ")\n";
}
echo "\n=== notifications_unified ===\n";
foreach ($pdo->query('DESCRIBE notifications_unified')->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  " . $c['Field'] . ' (' . $c['Type'] . ")\n";
}

echo "\n=== farmers ===\n";
foreach ($pdo->query('DESCRIBE farmers')->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  " . $c['Field'] . ' (' . $c['Type'] . ")\n";
}
echo "\n=== farmers_legacy ===\n";
foreach ($pdo->query('DESCRIBE farmers_legacy')->fetchAll(PDO::FETCH_ASSOC) as $c) {
    echo "  " . $c['Field'] . ' (' . $c['Type'] . ")\n";
}
