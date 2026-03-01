<?php
$pdo = new PDO('mysql:host=localhost', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query('SHOW DATABASES');
$databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo 'Total Databases: ' . count($databases) . PHP_EOL;
$total = 0;
foreach ($databases as $db) {
    if (!in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) {
        try {
            $dbPdo = new PDO('mysql:host=localhost;dbname=' . $db, 'root', '');
            $stmt = $dbPdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $count = count($tables);
            $total += $count;
            echo $db . ': ' . $count . ' tables' . PHP_EOL;
        } catch (Exception $e) {
            echo $db . ': Access denied' . PHP_EOL;
        }
    }
}
echo 'TOTAL TABLES: ' . $total . PHP_EOL;
?>
