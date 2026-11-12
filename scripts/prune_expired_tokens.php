<?php
/**
 * Prune Expired API Tokens
 *
 * Deletes expired tokens from `api_tokens` table where `expires_at < NOW()`.
 * Supports --dry-run flag to preview without deleting.
 * Supports --days=N to prune tokens expired older than N days (default: 0 = all expired).
 *
 * Usage:
 *   php scripts/prune_expired_tokens.php [--dry-run] [--days=30]
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Core\Database\Database;

$isDryRun = in_array('--dry-run', $argv);
$days = 0;
foreach ($argv as $arg) {
    if (preg_match('/^--days=(\d+)$/', $arg, $m)) {
        $days = (int)$m[1];
    }
}

try {
    $pdo = Database::getInstance()->getConnection();
} catch (\Throwable $e) {
    echo "ERR Failed to connect to database: " . $e->getMessage() . "\n";
    exit(1);
}

$cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));

$totalCount = $pdo->query("SELECT COUNT(*) FROM api_tokens")->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM api_tokens WHERE expires_at IS NOT NULL AND expires_at < ?");
$stmt->execute([$cutoff]);
$expiredCount = (int)$stmt->fetchColumn();

$activeCount = $totalCount - $expiredCount;

echo "=== API TOKEN PRUNING ===\n";
echo "Total tokens in table:  $totalCount\n";
echo "Expired tokens (< $cutoff): $expiredCount\n";
echo "Active tokens remaining: $activeCount\n";

if ($expiredCount === 0) {
    echo "No expired tokens to prune.\n";
    exit(0);
}

if ($isDryRun) {
    echo "\n[DRY RUN] Would delete $expiredCount expired token(s). No changes made.\n";
    exit(0);
}

$delStmt = $pdo->prepare("DELETE FROM api_tokens WHERE expires_at IS NOT NULL AND expires_at < ?");
$delStmt->execute([$cutoff]);
$deleted = $delStmt->rowCount();

echo "\nSUCCESS: Deleted $deleted expired token(s).\n";
$remaining = $pdo->query("SELECT COUNT(*) FROM api_tokens")->fetchColumn();
echo "Remaining tokens in table: $remaining\n";

