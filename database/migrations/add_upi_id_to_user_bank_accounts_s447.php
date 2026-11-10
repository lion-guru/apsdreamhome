<?php
/**
 * Migration: add `upi_id` to `user_bank_accounts`
 *
 * The mobile "Bank Details" page (lib/presentation/pages/customer/user_bank_details_page.dart)
 * reads and writes a UPI id alongside the bank account fields, but
 * `user_bank_accounts` has no column to store it — every save silently
 * dropped the value. The mobile API endpoint /api/v2/mobile/user/bank-details
 * is added alongside (see MobileUserApiController::getUserBankDetails /
 * saveUserBankDetails).
 *
 * Idempotent: checks the column before adding. Re-running is a no-op.
 *
 * Run via: php database/migrations/add_upi_id_to_user_bank_accounts_s447.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "OK  Database connected\n\n";
} catch (\Throwable $e) {
    echo "ERR Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$tableExists = $pdo->query("SHOW TABLES LIKE 'user_bank_accounts'")->fetch();
if (!$tableExists) {
    echo "SKIP user_bank_accounts table does not exist\n";
    exit(0);
}

$columns = [
    "upi_id" => "VARCHAR(100) NULL AFTER branch_name",
];

foreach ($columns as $col => $definition) {
    $exists = $pdo->query("SHOW COLUMNS FROM user_bank_accounts LIKE '$col'")->fetch();
    if ($exists) {
        echo "SKIP column $col already exists\n";
        continue;
    }
    try {
        $pdo->exec("ALTER TABLE user_bank_accounts ADD COLUMN $col $definition");
        echo "OK  added column $col to user_bank_accounts\n";
    } catch (\Throwable $e) {
        echo "ERR failed to add $col: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Verify
$final = $pdo->query("SHOW COLUMNS FROM user_bank_accounts LIKE 'upi_id'")->fetch();
echo $final ? "OK  upi_id present\n" : "ERR upi_id missing\n";
