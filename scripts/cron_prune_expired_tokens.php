<?php
/**
 * API Token Prune Cron
 * 
 * Removes expired api_tokens rows (30-day TTL) to keep the database lean.
 * Runs daily at 03:00 AM (or as configured in Task Scheduler).
 * 
 * Usage: php scripts/cron_prune_expired_tokens.php [--dry-run] [--days=N]
 *   --dry-run: Preview what would be deleted without actually deleting
 *   --days=N:  Override grace period (default: 30 days)
 * 
 * Idempotent: Safe to run multiple times.
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

echo "=== API Token Prune Cron Started ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

$dryRun = in_array('--dry-run', $argv, true);
$graceDays = 30;

foreach ($argv as $arg) {
    if (preg_match('/^--days=(\d+)$/', $arg, $m)) {
        $graceDays = (int)$m[1];
    }
}

echo "Grace period: {$graceDays} days\n";
echo "Mode: " . ($dryRun ? 'DRY RUN' : 'LIVE') . "\n";

try {
    $db = Database::getInstance()->getConnection();

    // Count expired tokens before deletion
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM api_tokens 
        WHERE expires_at IS NOT NULL 
          AND expires_at < DATE_SUB(NOW(), INTERVAL ? DAY)
    ");
    $stmt->execute([$graceDays]);
    $expiredCount = (int)$stmt->fetchColumn();

    echo "Expired tokens found: $expiredCount\n";

    if ($expiredCount === 0) {
        echo "No expired tokens to prune.\n";
        echo "=== API Token Prune Cron Completed ===\n";
        exit(0);
    }

    // Show sample of expired tokens (for audit)
    $stmt = $db->prepare("
        SELECT id, user_id, token, created_at, expires_at 
        FROM api_tokens 
        WHERE expires_at IS NOT NULL 
          AND expires_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ORDER BY expires_at ASC
        LIMIT 5
    ");
    $stmt->execute([$graceDays]);
    $samples = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo "Sample expired tokens:\n";
    foreach ($samples as $s) {
        echo "  ID: {$s['id']}, User: {$s['user_id']}, Expires: {$s['expires_at']}\n";
    }

    if (!$dryRun) {
        // Delete expired tokens
        $stmt = $db->prepare("
            DELETE FROM api_tokens 
            WHERE expires_at IS NOT NULL 
              AND expires_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$graceDays]);
        $deleted = $stmt->rowCount();
        echo "Deleted: $deleted expired tokens\n";
    } else {
        echo "DRY RUN: Would delete $expiredCount expired tokens\n";
    }

    // Count remaining tokens
    $stmt = $db->query("SELECT COUNT(*) FROM api_tokens");
    $remaining = (int)$stmt->fetchColumn();
    echo "Remaining active tokens: $remaining\n";

    echo "✅ API token prune completed successfully\n";

} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

echo "=== API Token Prune Cron Completed ===\n";
