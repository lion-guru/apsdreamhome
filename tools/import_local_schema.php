<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
$config = require APP_ROOT . '/config/database.php';
$db = $config['database'] ?? [];

$host = $db['host'] ?? 'localhost';
$username = $db['username'] ?? 'root';
$password = $db['password'] ?? '';
$dbname = $db['database'] ?? 'apsdreamhome';
$charset = $db['charset'] ?? 'utf8mb4';
$collation = $db['collation'] ?? 'utf8mb4_unicode_ci';

$dsn = "mysql:host={$host};charset={$charset}";
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET {$charset} COLLATE {$collation}");
$pdo->exec("USE `{$dbname}`");

$schemaFile = APP_ROOT . '/database/archive/legacy_sql/complete_setup.sql';
if (!file_exists($schemaFile)) {
    fwrite(STDERR, "Schema file not found: {$schemaFile}\n");
    exit(1);
}

$sql = file_get_contents($schemaFile);
if ($sql === false) {
    fwrite(STDERR, "Failed to read schema file\n");
    exit(1);
}

$statements = array_filter(array_map('trim', preg_split('/;\\s*\\n/', $sql)));
$count = 0;
foreach ($statements as $statement) {
    if ($statement !== '') {
        $pdo->exec($statement);
        $count++;
    }
}

echo "Imported {$count} statements into database '{$dbname}'.\n";
