<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4', $config['username'], $config['password']);

echo "=== gallery table ===" . PHP_EOL;
$r = $pdo->query('SHOW CREATE TABLE gallery')->fetch();
echo $r['Create Table'] . PHP_EOL;

echo PHP_EOL . "=== gallery rows ===" . PHP_EOL;
foreach ($pdo->query('SELECT * FROM gallery') as $row) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== careers table ===" . PHP_EOL;
$r = $pdo->query('SHOW CREATE TABLE careers')->fetch();
echo $r['Create Table'] . PHP_EOL;

echo PHP_EOL . "=== careers rows ===" . PHP_EOL;
foreach ($pdo->query('SELECT * FROM careers') as $row) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== site_content about section ===" . PHP_EOL;
$stmt = $pdo->prepare('SELECT content_key, content_value, content_group FROM site_content WHERE section = ? ORDER BY sort_order');
$stmt->execute(['about']);
foreach ($stmt->fetchAll() as $row) { echo $row['content_group'] . ' | ' . $row['content_key'] . ' = ' . substr($row['content_value'], 0, 80) . PHP_EOL; }

echo PHP_EOL . "=== existing gallery routes in web.php ===" . PHP_EOL;
$routes = file_get_contents($root . '/routes/web.php');
preg_match_all('/\/admin\/gallery[^\s\'"]*/', $routes, $m);
foreach ($m[0] as $r) { echo $r . PHP_EOL; }

echo PHP_EOL . "=== existing careers routes in web.php ===" . PHP_EOL;
preg_match_all('/\/admin\/career[^\s\'"]*/', $routes, $m);
foreach ($m[0] as $r) { echo $r . PHP_EOL; }

echo PHP_EOL . "=== existing about routes ===" . PHP_EOL;
preg_match_all('/\/admin\/about[^\s\'"]*/', $routes, $m);
foreach ($m[0] as $r) { echo $r . PHP_EOL; }

echo PHP_EOL . "=== site_content sections ===" . PHP_EOL;
foreach ($pdo->query('SELECT DISTINCT section, COUNT(*) as cnt FROM site_content GROUP BY section') as $row) {
    echo $row['section'] . ' (' . $row['cnt'] . ' keys)' . PHP_EOL;
}?>