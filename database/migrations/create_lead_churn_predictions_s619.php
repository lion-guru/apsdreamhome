<?php

/**
 * Migration: create_lead_churn_predictions_s619.php
 * ============================================================
 * Creates `lead_churn_predictions` table for the Lead Churn Prediction
 * service (self-hosted, no external API).
 *
 * Predicts which leads will go cold / never convert, using existing
 * lead fields: lead_score, status, created_at, last_contacted_at,
 * next_activity_date, last_activity_date, ai_score, priority,
 * lead_category, source, budget.
 *
 * Idempotent: re-run is a safe SKIP.
 */

use App\Core\Database\Database;

return [
    'up' => function (PDO $pdo) {
        // Check idempotency: skip if table already has risk_score column
        $existing = (int)$pdo->query("
            SELECT COUNT(*) FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = 'lead_churn_predictions'
              AND column_name = 'risk_score'
        ")->fetchColumn();

        if ($existing > 0) {
            echo "  lead_churn_predictions already exists — SKIP\n";
            return;
        }

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `lead_churn_predictions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `lead_id` int(11) NOT NULL,
                `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
                `risk_level` enum('low','medium','high','critical') NOT NULL DEFAULT 'low',
                `risk_score` decimal(5,2) NOT NULL DEFAULT 0.00,
                `churn_probability` decimal(5,2) NOT NULL DEFAULT 0.00,
                `next_action` varchar(100) DEFAULT NULL,
                `factors` text DEFAULT NULL,
                `predicted_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_churn_prediction_lead` (`lead_id`),
                KEY `idx_churn_risk_level` (`risk_level`),
                KEY `idx_churn_lead` (`lead_id`),
                KEY `idx_churn_tenant` (`tenant_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        echo "  lead_churn_predictions table created\n";
    },

    'down' => function (PDO $pdo) {
        $pdo->exec("DROP TABLE IF EXISTS `lead_churn_predictions`");
        echo "  lead_churn_predictions table dropped\n";
    },
];
