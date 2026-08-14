<?php
/**
 * Daily Rank Auto-Promotion
 * â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
 * Evaluates all active associates and promotes those who meet
 * rank thresholds (legs + GBV volume). Updates associates.level,
 * mlm_profiles.current_level, and inserts mlm_rank_history.
 *
 * Thresholds:
 *   Silver:  3 legs, â‚¹2,00,000 GBV
 *   Gold:    4 legs, â‚¹5,00,000 GBV
 *   Platinum: 5 legs, â‚¹10,00,000 GBV
 *   Diamond: 6 legs, â‚¹25,00,000 GBV
 *
 * Schedule: Daily at 01:10 AM IST
 *   0 1 * * * php C:\xampp\htdocs\apsdreamhome\scripts\run_rank_promotion.php
 *
 * Usage: php scripts/run_rank_promotion.php
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

    // Get all active associates
    $stmt = $pdo->query("SELECT a.id, a.user_id, a.level, u.name
        FROM associates a
        JOIN users u ON a.user_id = u.id
        WHERE u.status = 'active'
        ORDER BY a.id");
    $associates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Evaluating " . count($associates) . " active associates..." . PHP_EOL . PHP_EOL;

    $promoted = 0;
    $unchanged = 0;

    foreach ($associates as $assoc) {
        $oldRank = $assoc['level'] ?: 'bronze';
        $newRank = $engine->evaluateRankPromotion((int)$assoc['id']);

        if ($newRank !== null && $newRank !== $oldRank) {
            $engine->applyRankPromotion((int)$assoc['id']);
            echo "âœ… {$assoc['name']} (user_id={$assoc['user_id']}): {$oldRank} â†’ {$newRank}" . PHP_EOL;
            $promoted++;
        } else {
            echo "   {$assoc['name']} (user_id={$assoc['user_id']}): {$oldRank} (no change)" . PHP_EOL;
            $unchanged++;
        }
    }

    echo PHP_EOL . "â”€â”€ Summary â”€â”€" . PHP_EOL;
    echo "   Total associates: " . count($associates) . PHP_EOL;
    echo "   Promoted: {$promoted}" . PHP_EOL;
    echo "   Unchanged: {$unchanged}" . PHP_EOL;

    echo PHP_EOL . "[" . date('Y-m-d H:i:s') . "] Rank promotion run complete" . PHP_EOL;

} catch (\Throwable $e) {
    echo "â�Œ FATAL: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}?>