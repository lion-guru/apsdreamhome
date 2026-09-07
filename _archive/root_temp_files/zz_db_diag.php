<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=UTF-8');
echo 'SAPI: ' . php_sapi_name() . PHP_EOL;
echo 'PPID env DB_PORT: ' . var_export(getenv('DB_PORT'), true) . PHP_EOL;
echo 'PPID env DB_HOST: ' . var_export(getenv('DB_HOST'), true) . PHP_EOL;
echo 'PPID env DB_PASSWORD: ' . var_export(getenv('DB_PASSWORD'), true) . PHP_EOL;
echo '$_ENV DB_PORT: ' . var_export($_ENV['DB_PORT'] ?? '(unset)', true) . PHP_EOL;
echo 'defined(DB_PORT): ' . var_export(defined('DB_PORT') ? constant('DB_PORT') : '(not defined)', true) . PHP_EOL;

require dirname(__DIR__) . '/config/bootstrap.php';

echo PHP_EOL . '--- after bootstrap ---' . PHP_EOL;
echo 'getenv DB_PORT: ' . var_export(getenv('DB_PORT'), true) . PHP_EOL;
echo 'getenv DB_HOST: ' . var_export(getenv('DB_HOST'), true) . PHP_EOL;
echo 'getenv DB_PASSWORD: ' . var_export(getenv('DB_PASSWORD'), true) . PHP_EOL;
echo '$_ENV DB_PORT: ' . var_export($_ENV['DB_PORT'] ?? '(unset)', true) . PHP_EOL;
echo 'defined(DB_PORT): ' . var_export(defined('DB_PORT') ? constant('DB_PORT') : '(not defined)', true) . PHP_EOL;
echo 'cfg db port: ' . var_export($config['port'] ?? '(unset)', true) . PHP_EOL;
echo 'cfg db host: ' . var_export($config['host'] ?? '(unset)', true) . PHP_EOL;

echo PHP_EOL . '--- ConfigService ---' . PHP_EOL;
try {
    $cs = \App\Core\ConfigService::getInstance();
    $db = $cs->getDatabaseConfig();
    echo 'host=' . var_export($db['host'] ?? null, true)
        . ' port=' . var_export($db['port'] ?? null, true)
        . ' db=' . var_export($db['database'] ?? null, true)
        . ' user=' . var_export($db['username'] ?? null, true)
        . ' pass=' . var_export($db['password'] ?? null, true) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'ConfigService FAILED: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . '--- Database::getInstance connection ---' . PHP_EOL;
try {
    $d = \App\Core\Database\Database::getInstance();
    $pdo = $d->getPdo();
    echo 'connected. server version: ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
    $r = $d->fetch('SELECT 1 AS ok');
    echo 'SELECT 1 => ' . json_encode($r) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'FAILED: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . '--- direct PDO (ConfigService keys) ---' . PHP_EOL;
try {
    $db = \App\Core\ConfigService::getInstance()->getDatabaseConfig();
    $pdo2 = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'], $db['port'], $db['database'], 'utf8mb4'),
        $db['username'], $db['password']
    );
    echo 'direct PDO connected. SELECT 1 => ' . json_encode($pdo2->query('SELECT 1')->fetch(PDO::FETCH_ASSOC)) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'direct PDO FAILED: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . '--- direct PDO port 3306 (control) ---' . PHP_EOL;
try {
    $pdo3 = new PDO('mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome;charset=utf8mb4', 'root', getenv('DB_PASSWORD') ?: '');
    echo '3306 connected?! => ' . json_encode($pdo3->query('SELECT 1')->fetch(PDO::FETCH_ASSOC)) . PHP_EOL;
} catch (\Throwable $e) {
    echo '3306 control FAILED (expected): ' . $e->getMessage() . PHP_EOL;
}