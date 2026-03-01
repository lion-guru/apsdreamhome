<?php
$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhomes', 'root', '');
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo 'apsdreamhomes database: ' . count($tables) . ' tables' . PHP_EOL;
echo 'First 20 tables:' . PHP_EOL;
for ($i = 0; $i < min(20, count($tables)); $i++) {
    echo ($i + 1) . '. ' . $tables[$i] . PHP_EOL;
}
if (count($tables) > 20) {
    echo '... and ' . (count($tables) - 20) . ' more tables' . PHP_EOL;
}
?>
