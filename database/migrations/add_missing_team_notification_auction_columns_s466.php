<?php
/**
 * Migration S466: add the columns the code writes but the schema never had.
 *
 * WHY (S466): the new schema-mismatch scanner found 9 phantom columns
 * across three tables.  Every one of them is a real DOMAIN field that the
 * application writes - not a typo - so the schema was missing them and the
 * corresponding INSERT died with 1054 inside a try/catch:
 *
 *   team_messages.type        VARCHAR(50)   'announcement' | 'direct' | ...
 *   team_messages.recipients   VARCHAR(255)  'all' | a recipient list
 *   notifications.sender_id    BIGINT        the admin who sent it
 *   notifications.recipient_type VARCHAR(50) 'all' | 'customer' | ...
 *   auction_bids.bidder_name   VARCHAR(150)
 *   auction_bids.max_auto_bid  DECIMAL(15,2)
 *   auction_bids.bid_type      VARCHAR(30)   'manual' | 'auto'
 *   auction_bids.ip_address    VARCHAR(45)
 *   auction_bids.status        VARCHAR(30)   'winning' | 'outbid' | ...
 *
 * 100% Idempotent.  Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S466: the columns the code writes ===\n";

if (!function_exists('s466_columnExists')) {
    function s466_columnExists(PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('s466_tableExists')) {
    function s466_tableExists(PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

$columns = [
    ['team_messages',    'type',          "VARCHAR(50) NULL AFTER `sender_id`"],
    ['team_messages',    'recipients',    "VARCHAR(255) NULL AFTER `type`"],
    ['notifications',    'sender_id',     "BIGINT NULL AFTER `message`"],
    ['notifications',    'recipient_type', "VARCHAR(50) NULL AFTER `sender_id`"],
    ['auction_bids',     'bidder_name',   "VARCHAR(150) NULL AFTER `bidder_id`"],
    ['auction_bids',     'max_auto_bid',  "DECIMAL(15,2) NULL DEFAULT NULL AFTER `bid_amount`"],
    ['auction_bids',     'bid_type',      "VARCHAR(30) NULL DEFAULT 'manual' AFTER `max_auto_bid`"],
    ['auction_bids',     'ip_address',    "VARCHAR(45) NULL AFTER `bid_type`"],
    ['auction_bids',     'status',        "VARCHAR(30) NULL DEFAULT 'winning' AFTER `ip_address`"],
];

foreach ($columns as [$table, $column, $definition]) {
    if (!s466_tableExists($pdo, $table)) {
        echo "  [-] {$table} does not exist - skipped\n";
        continue;
    }
    if (s466_columnExists($pdo, $table, $column)) {
        echo "  [=] {$table}.{$column} already exists - skipped\n";
        continue;
    }
    try {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        echo "  [+] {$table}.{$column} ADDED\n";
    } catch (\Throwable $e) {
        echo "  [!] {$table}.{$column} FAILED: " . $e->getMessage() . "\n";
    }
}

echo "=== MIGRATION S466 COMPLETE ===\n";
