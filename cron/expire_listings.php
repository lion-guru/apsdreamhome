<?php
/**
 * Listing Expiration Cron
 * Marks listings with expired expires_at as 'expired' status
 * 
 * Run: php cron/expire_listings.php
 * Schedule: daily (e.g., 02:00 AM)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

set_time_limit(60);
date_default_timezone_set('Asia/Kolkata');

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$logFile = $logDir . '/expire_listings_' . date('Y-m-d') . '.log';

function expireLog(string $msg): void {
    global $logFile;
    $line = date('Y-m-d H:i:s') . " - $msg\n";
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

expireLog("=== Listing Expiration Cron Started ===");

try {
    $db = Database::getInstance();

    // Set tenant context
    $cronTenantId = 1;
    if (class_exists('\App\Core\Middleware\TenantContext')) {
        \App\Core\Middleware\TenantContext::setById($cronTenantId, $db->getConnection());
    }
    $cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";

    // 1. Find listings that have expired (expires_at <= NOW() and status not already expired/sold)
    $expiredListings = $db->fetchAll(
        "SELECT id, name, user_id, expires_at, status, listing_type, property_type
         FROM user_properties
         WHERE expires_at IS NOT NULL
           AND expires_at <= NOW()
           AND status NOT IN ('expired', 'sold')
           {$cronTenantSql}
         ORDER BY expires_at ASC
         LIMIT 500"
    );

    expireLog("Found " . count($expiredListings) . " expired listings to process");

    $updated = 0;
    $failed = 0;

    foreach ($expiredListings as $listing) {
        try {
            // Update status to 'expired'
            $rows = $db->execute(
                "UPDATE user_properties 
                 SET status = 'expired', 
                     updated_at = NOW() 
                 WHERE id = ? AND expires_at <= NOW() {$cronTenantSql}",
                [$listing['id']]
            );

            if ($rows > 0) {
                $updated++;
                expireLog("EXPIRED: ID={$listing['id']} | {$listing['name']} | user={$listing['user_id']} | type={$listing['listing_type']} | expired_at={$listing['expires_at']}");
            } else {
                $failed++;
                expireLog("SKIP (concurrent update?): ID={$listing['id']}");
            }
        } catch (\Throwable $e) {
            $failed++;
            expireLog("ERROR on ID={$listing['id']}: " . $e->getMessage());
        }
    }

    // 2. Also clean up old 'pending' listings that never had expires_at set (older than 90 days)
    // These are stale drafts that were never published
    $staleStmt = $db->execute(
        "UPDATE user_properties
         SET status = 'expired', updated_at = NOW()
         WHERE status = 'pending'
           AND expires_at IS NULL
           AND created_at <= DATE_SUB(NOW(), INTERVAL 90 DAY)
           {$cronTenantSql}"
    );
    $stalePending = $staleStmt->rowCount();

    if ($stalePending > 0) {
        expireLog("CLEANED: {$stalePending} stale pending listings (>90 days, no expires_at)");
    }

    // 3. Optionally: notify users whose listings expired (if notification service available)
    // This could be extended to send email/SMS

    expireLog("=== Summary: {$updated} expired, {$failed} failed, {$stalePending} stale cleaned ===");

} catch (\Throwable $e) {
    expireLog("FATAL ERROR: " . $e->getMessage());
    exit(1);
}

expireLog("=== Listing Expiration Cron Completed ===\n");
exit(0);