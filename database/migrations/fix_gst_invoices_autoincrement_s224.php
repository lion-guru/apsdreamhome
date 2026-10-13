<?php
/**
 * GST invoices auto_increment guard (S223 found live gst_invoices.id WITHOUT
 * auto_increment after a parallel migration rewrote the table; invoice store
 * 500'd with "Field 'id' doesn't have a default value").
 * Idempotent: ALTERs ONLY when EXTRA != auto_increment. Safe to re-run
 * after any DB restore. Standing Rule 1: no one-off live SQL.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$extra = $pdo->query("SELECT EXTRA FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'gst_invoices' AND COLUMN_NAME = 'id'")->fetchColumn();

if (stripos((string)$extra, 'auto_increment') !== false) {
    echo "SKIP gst_invoices.id (already auto_increment)\n";
} else {
    $pdo->exec("ALTER TABLE gst_invoices MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
    echo "FIXED gst_invoices.id (auto_increment restored)\n";
}

echo "Done.\n";
