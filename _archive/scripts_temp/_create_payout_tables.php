<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Creating payout_batches table ===\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS payout_batches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_name VARCHAR(100) NOT NULL,
    batch_type ENUM('commission','salary','bonus','refund') NOT NULL DEFAULT 'commission',
    total_entries INT UNSIGNED DEFAULT 0,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('draft','pending_approval','approved','processing','completed','rejected') NOT NULL DEFAULT 'draft',
    period_from DATE NULL,
    period_to DATE NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NOT NULL,
    approved_by INT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    processed_by INT UNSIGNED NULL,
    processed_at TIMESTAMP NULL,
    bank_export_file VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (batch_type),
    INDEX idx_period (period_from, period_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "  OK\n";

echo "=== Creating payout_entries table ===\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS payout_entries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id INT UNSIGNED NOT NULL,
    ledger_id BIGINT UNSIGNED NULL,
    beneficiary_user_id INT UNSIGNED NOT NULL,
    beneficiary_name VARCHAR(100) NULL,
    beneficiary_account VARCHAR(50) NULL,
    beneficiary_ifsc VARCHAR(20) NULL,
    beneficiary_upi VARCHAR(50) NULL,
    commission_type VARCHAR(50) NULL,
    amount DECIMAL(15,2) NOT NULL,
    tds_amount DECIMAL(15,2) DEFAULT 0.00,
    net_amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('bank_transfer','upi','cheque','cash','wallet') DEFAULT 'bank_transfer',
    payment_reference VARCHAR(100) NULL,
    status ENUM('pending','processing','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    INDEX idx_batch (batch_id),
    INDEX idx_user (beneficiary_user_id),
    INDEX idx_ledger (ledger_id),
    INDEX idx_status (status),
    FOREIGN KEY (batch_id) REFERENCES payout_batches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "  OK\n";

echo "=== DONE ===\n";
