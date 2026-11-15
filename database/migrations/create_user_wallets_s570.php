<?php
/**
 * Restore user_wallets (legacy parallel wallet table) — idempotent.
 *
 * The table was dropped at some point while ~35 legacy code paths still
 * reference it (MLM commission credit, analytics joins, wallet activation,
 * withdrawal flow). Until the full wallet unification is executed, restore it
 * empty with exactly the columns the code expects so those paths stop 500ing.
 *
 * Safe: CREATE TABLE IF NOT EXISTS; zero rows; no data; additive only.
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "== S570: restore user_wallets ==\n";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$exists = $pdo->query("SHOW TABLES LIKE 'user_wallets'")->rowCount() > 0;
if ($exists) {
    echo "SKIP: user_wallets already exists\n";
    exit(0);
}

$sql = "CREATE TABLE `user_wallets` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(10) UNSIGNED NOT NULL DEFAULT 1,
  `user_id` BIGINT(20) UNSIGNED NOT NULL,
  `user_type` VARCHAR(20) NOT NULL DEFAULT 'associate',
  `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_credited` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_debited` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_earned` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_uw_user_type` (`tenant_id`, `user_id`, `user_type`),
  KEY `idx_uw_user` (`user_id`),
  KEY `idx_uw_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$pdo->exec($sql);
echo "CREATED user_wallets\n";

$check = $pdo->query("SHOW COLUMNS FROM user_wallets")->fetchAll(PDO::FETCH_COLUMN, 0);
echo "columns: " . implode(',', $check) . "\n";
$rows = $pdo->query("SELECT COUNT(*) AS c FROM user_wallets")->fetch();
echo "rows: " . $rows['c'] . "\n";
