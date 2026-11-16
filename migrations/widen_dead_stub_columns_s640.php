<?php

/**
 * Migration: widen_dead_stub_columns_s640.php
 * ============================================================
 * Fixes live 1054/1364 silent-failure paths found by auditing the
 * logged SQL errors against the live schema (2026-11-16):
 *
 * 1. bank_reconciliations is a bare stub (id/tenant_id/timestamps) but
 *    BankReconciliationService + the finance reconciliation view reference
 *    bank_account_id, statement_date, statement_balance, book_balance,
 *    difference, status, reconciled_by, reconciled_at, notes -> every
 *    reconciliation page 1054'd. Table carries 0 rows; widening adds the
 *    designed columns.
 * 2. reconciliation_items (0 rows) lacks cash_book_id referenced by
 *    matchTransaction() UPDATE.
 * 3. tds_register lacks certificate_number / certificate_issued_at referenced
 *    by TdsService::generateTdsCertificate() and getTdsCertificatesIssued()
 *    and EFilingController -> every TDS certificate generation/download silently
 *    failed.
 * 4. mlm_associate_registrations is a bare stub but MlmInvestmentEngine reads
 *    payment_status / amount_paid -> MLM joining-package stats always 1054'd.
 * 5. webhook_deliveries lacks event_type referenced by WebhookService INSERT
 *    and its stats-by-event query -> ALL webhook delivery logging silently
 *    failed.
 *
 * All five tables carry 0 rows at migration time (verified), so every change
 * is additive-only (nullable columns or inert defaults). Idempotent: re-run
 * is a safe SKIP.
 */

return [
    'up' => function (PDO $pdo) {
        $dbStmt = $pdo->query("SELECT DATABASE()");
        $db = $dbStmt->fetchColumn();

        $colExists = function (string $table, string $col) use ($pdo, $db): bool {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
            $stmt->execute([$db, $table, $col]);
            return (int)$stmt->fetchColumn() > 0;
        };

        $addCol = function (string $table, string $col, string $ddl) use ($pdo, $colExists) {
            if ($colExists($table, $col)) {
                echo "  SKIP: {$table}.{$col} already exists\n";
                return;
            }
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$ddl}");
            echo "  ADD: {$table}.{$col}\n";
        };

        // 1. bank_reconciliations
        $addCol('bank_reconciliations', 'bank_account_id', 'bigint unsigned NULL DEFAULT NULL');
        $addCol('bank_reconciliations', 'statement_date', 'date NULL DEFAULT NULL');
        $addCol('bank_reconciliations', 'statement_balance', 'decimal(15,2) NOT NULL DEFAULT 0.00');
        $addCol('bank_reconciliations', 'book_balance', 'decimal(15,2) NOT NULL DEFAULT 0.00');
        $addCol('bank_reconciliations', 'difference', 'decimal(15,2) NOT NULL DEFAULT 0.00');
        $addCol('bank_reconciliations', 'status', "varchar(30) NOT NULL DEFAULT 'open'");
        $addCol('bank_reconciliations', 'reconciled_by', 'bigint unsigned NULL DEFAULT NULL');
        $addCol('bank_reconciliations', 'reconciled_at', 'datetime NULL DEFAULT NULL');
        $addCol('bank_reconciliations', 'notes', 'text NULL');

        // 2. reconciliation_items
        $addCol('reconciliation_items', 'cash_book_id', 'bigint unsigned NULL DEFAULT NULL');

        // 3. tds_register
        $addCol('tds_register', 'certificate_number', 'varchar(120) NULL DEFAULT NULL');
        $addCol('tds_register', 'certificate_issued_at', 'datetime NULL DEFAULT NULL');

        // 4. mlm_associate_registrations
        $addCol('mlm_associate_registrations', 'payment_status', "enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'");
        $addCol('mlm_associate_registrations', 'amount_paid', 'decimal(12,2) NOT NULL DEFAULT 0.00');

        // 5. webhook_deliveries
        $addCol('webhook_deliveries', 'event_type', 'varchar(100) NULL DEFAULT NULL');
    },
    'down' => function (PDO $pdo) {
        // Additive-only migration; rollback is a no-op (columns are harmless).
    },
];
