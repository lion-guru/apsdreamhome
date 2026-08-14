<?php
/**
 * Daily Commission Clawback Runner
 * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * Finds EMI defaulters (30+ days overdue), debits proportional commission
 * from the agent/MLM upline chain via writeLedger with type='clawback'.
 *
 * Schedule: Daily at 01:05 AM IST
 *   0 1 * * * php C:\xampp\htdocs\apsdreamhome\scripts\run_clawback.php
 *
 * Usage: php scripts/run_clawback.php
 */

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[" . date('Y-m-d H:i:s') . "] Connected to database" . PHP_EOL;

    define('APP_ROOT', $root);
    require_once $root . '/app/Core/Autoloader.php';
    $autoloader = \App\Core\Autoloader::getInstance();
    $autoloader->register();

    $engine = new \App\Services\MLM\MLMCommissionEngine($pdo);
    $result = $engine->processClawbacks();

    echo "âœ… Clawback run complete" . PHP_EOL;
    echo "   Entries debited: {$result['processed']}" . PHP_EOL;
    echo "   Total clawback amount: â‚¹" . number_format($result['amount'], 2) . PHP_EOL;

    if (!empty($result['errors'])) {
        echo PHP_EOL . "   âš ï¸�  Errors:" . PHP_EOL;
        foreach ($result['errors'] as $err) {
            echo "   - {$err}" . PHP_EOL;
        }
    }

    echo PHP_EOL . "[" . date('Y-m-d H:i:s') . "] Clawback run complete" . PHP_EOL;

} catch (\Throwable $e) {
    echo "â�Œ FATAL: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}?>