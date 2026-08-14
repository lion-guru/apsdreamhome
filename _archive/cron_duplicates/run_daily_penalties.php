<?php
/**
 * Daily EMI Penalty Accrual
 * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * Finds all overdue installments past the 5-day grace period,
 * calculates 18% p.a. penalty, updates accrued_penalty, logs to penalty_audit.
 *
 * Schedule: Daily at 01:00 AM IST
 *   via Windows Task Scheduler or cron:
 *   0 1 * * * php C:\xampp\htdocs\apsdreamhome\scripts\run_daily_penalties.php
 *
 * Usage: php scripts/run_daily_penalties.php
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

    $service = new \App\Services\Accounting\MoneyWorkflowService();
    $result  = $service->applyDailyPenalties();

    if ($result['success']) {
        echo "âœ… Penalties applied successfully" . PHP_EOL;
        echo "   Installments affected: {$result['penalties_applied']}" . PHP_EOL;
        echo "   Total penalty accrued: â‚¹" . number_format($result['total_penalty'], 2) . PHP_EOL;

        if (!empty($result['installments'])) {
            echo PHP_EOL . "   Per-installment breakdown:" . PHP_EOL;
            foreach ($result['installments'] as $inst) {
                echo "   - Installment #{$inst['id']} ({$inst['booking_number']}, #{$inst['installment_no']}): " .
                     "{$inst['days_overdue']}d overdue, " .
                     "â‚¹" . number_format($inst['new_penalty'], 2) . " penalty, " .
                     "total accrued: â‚¹" . number_format($inst['total_accrued'], 2) . PHP_EOL;
            }
        }
    } else {
        echo "âš ï¸�  Penalty engine returned: " . ($result['error'] ?? 'unknown error') . PHP_EOL;
    }

    echo PHP_EOL . "[" . date('Y-m-d H:i:s') . "] Daily penalty run complete" . PHP_EOL;

} catch (\Throwable $e) {
    echo "â�Œ FATAL: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}?>