<?php
/**
 * Chat Conversation Cleanup Cron
 * Run daily: deletes old completed/cancelled/expired conversations
 * Keeps active/confirm conversations for 24h, everything else for 7 days
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Core/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

// Set tenant context for TenantContext consumers
$cronTenantId = 1;
if (class_exists('\App\Core\Middleware\TenantContext')) {
    \App\Core\Middleware\TenantContext::setById($cronTenantId, $db->getConnection());
}
$cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
$cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
$cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

echo "=== Chat Conversation Cleanup ===\n";
echo "Time: " . date('Y-m-d d H:i:s') . "\n\n";

// 1. Delete expired/old conversations (7+ days for completed/cancelled/expired)
$stmt = $db->query(
    "DELETE FROM ai_chat_conversations 
     WHERE status IN ('completed', 'cancelled', 'expired') 
     AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY){$cronTenantSql}"
);
$deletedOld = $stmt->rowCount();
echo "âœ“ Deleted {$deletedOld} old conversations (>7 days, completed/cancelled/expired)\n";

// 2. Delete orphaned 'active' conversations older than 24h (stuck/abandoned)
$stmt = $db->query(
    "DELETE FROM ai_chat_conversations 
     WHERE status = 'active' 
     AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR){$cronTenantSql}"
);
$deletedStale = $stmt->rowCount();
echo "âœ“ Deleted {$deletedStale} stale active conversations (>24h)\n";

// 3. Delete orphaned 'confirm' conversations older than 1 hour
$stmt = $db->query(
    "DELETE FROM ai_chat_conversations 
     WHERE status = 'confirm' 
     AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR){$cronTenantSql}"
);
$deletedConfirm = $stmt->rowCount();
echo "âœ“ Deleted {$deletedConfirm} abandoned confirm conversations (>1h)\n";

// 4. Show remaining stats
$stats = $db->fetchAll("SELECT status, COUNT(*) as cnt FROM ai_chat_conversations GROUP BY status");
echo "\n--- Remaining conversations ---\n";
foreach ($stats as $row) {
    echo "  {$row['status']}: {$row['cnt']}\n";
}

$total = $db->fetch("SELECT COUNT(*) as cnt FROM ai_chat_conversations");
echo "  TOTAL: {$total['cnt']}\n";

echo "\n=== Cleanup Complete ===\n";?>