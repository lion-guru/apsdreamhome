<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo "=== addresses ===\n";
$cols = $pdo->query('DESCRIBE addresses')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
echo PHP_EOL . 'Sample data:' . PHP_EOL;
foreach ($pdo->query('SELECT * FROM addresses LIMIT 2')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    print_r($r);
}
echo PHP_EOL . "=== colonies ===\n";
$cols = $pdo->query('DESCRIBE colonies')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo $c['Field'] . ' (' . $c['Type'] . ')' . PHP_EOL;
echo PHP_EOL . 'Sample data:' . PHP_EOL;
foreach ($pdo->query('SELECT * FROM colonies LIMIT 1')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    print_r($r);
}
