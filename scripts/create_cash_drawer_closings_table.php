<?php
// Idempotent: day-end cash drawer / safe closing ledger.
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance();
try {
    $db->execute("
        CREATE TABLE IF NOT EXISTS cash_drawer_closings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            closing_date DATE NOT NULL,
            cashier_id INT NULL,
            opening_cash DECIMAL(12,2) NOT NULL DEFAULT 0,
            cash_receipts DECIMAL(12,2) NOT NULL DEFAULT 0,
            cash_expenses DECIMAL(12,2) NOT NULL DEFAULT 0,
            bank_deposit DECIMAL(12,2) NOT NULL DEFAULT 0,
            system_closing DECIMAL(12,2) NOT NULL DEFAULT 0,
            physical_closing DECIMAL(12,2) NOT NULL DEFAULT 0,
            discrepancy DECIMAL(12,2) NOT NULL DEFAULT 0,
            denominations_json TEXT NULL,
            bank_slip_path VARCHAR(255) NULL,
            status ENUM('open','closed','audited') NOT NULL DEFAULT 'closed',
            notes VARCHAR(1000) NULL,
            closed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_day (tenant_id, closing_date),
            KEY idx_date (closing_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "cash_drawer_closings table created/verified\n";
} catch (\Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
