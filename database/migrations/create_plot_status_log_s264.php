<?php
/**
 * Create plot_status_log table (Session 264).
 * Idempotent: CREATE IF NOT EXISTS. Safe to re-run after any restore.
 *
 * Background: PlotManagementController writes status transitions in 7 places
 * and reads them on plots/show, but the table was never created — every write
 * died silently inside try/catch and the history tab always rendered empty.
 * Column set matches the live writers/readers exactly.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plot_status_log'")->fetchColumn();
if ($exists) {
    echo "SKIP plot_status_log (already exists)\nDone.\n";
    return;
}

$pdo->exec("CREATE TABLE IF NOT EXISTS plot_status_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plot_id INT NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NULL,
    changed_by BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plot_status_log_plot (plot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "FIXED plot_status_log created\nDone.\n";
