<?php

/**
 * Migration: widen_stamp_duty_calculations_s645.php
 * ============================================================
 * LegalAdvisorController's stamp-paper verification query reads
 * stamp_duty_calculations.status ('pending_verification') and
 * registration_fee_amount, but the table shipped as a bare stub with
 * neither column -> the whole legal stamp-paper review path was a
 * swallowed 1054 ("getStampPaperVerification Error" in php_error.log).
 *
 * Verified live: stamp_duty_calculations carries exactly 2 rows.
 * This widens ONLY `status` (designed-review column; defaults every
 * existing row to 'pending_verification' = needs review).
 * registration_fee_amount already exists (registered as
 * registration_fee_amount). Idempotent: re-run is a safe SKIP.
 */

return [
    'up' => function (PDO $pdo) {
        $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = 'stamp_duty_calculations' AND column_name = 'status'");
        $stmt->execute([$db]);
        if ((int)$stmt->fetchColumn() > 0) {
            echo "  SKIP: stamp_duty_calculations.status already exists\n";
            return;
        }
        $pdo->exec("ALTER TABLE `stamp_duty_calculations` ADD COLUMN `status` enum('pending_verification','verified','rejected') NOT NULL DEFAULT 'pending_verification' AFTER `calculation_data`");
        echo "  ADD: stamp_duty_calculations.status\n";
    },
    'down' => function (PDO $pdo) {
        // Additive-only migration; rollback is a no-op.
    },
];
