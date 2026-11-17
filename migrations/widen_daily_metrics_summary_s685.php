<?php

/**
 * Migration: widen_daily_metrics_summary_s685.php
 * ============================================================
 * daily_metrics_summary is a bare stub (id/tenant_id/created_at/updated_at)
 * but AnalyticsService::recordDailyMetric() inserts and
 * AnalyticsService::getDailyMetrics() reads a designed contract:
 * metric_name, category, value, dimensions, metric_date. The forecast path
 * (AnalyticsService::generateForecast via /api/v2/analytics/forecast) reads
 * getDailyMetrics(), so that whole endpoint 1054'd on every call.
 *
 * The table carries 0 rows (verified). Widening is additive-only.
 * Idempotent: re-run is a safe SKIP.
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
        $idxExists = function (string $table, string $idx) use ($pdo, $db): bool {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?");
            $stmt->execute([$db, $table, $idx]);
            return (int)$stmt->fetchColumn() > 0;
        };
        $add = function (string $sql) use ($pdo) { $pdo->exec($sql); };

        if (!$colExists('daily_metrics_summary', 'metric_name')) {
            $add("ALTER TABLE `daily_metrics_summary` ADD COLUMN `metric_name` VARCHAR(100) NOT NULL AFTER `tenant_id`");
        }
        if (!$colExists('daily_metrics_summary', 'category')) {
            $add("ALTER TABLE `daily_metrics_summary` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'general' AFTER `metric_name`");
        }
        if (!$colExists('daily_metrics_summary', 'value')) {
            $add("ALTER TABLE `daily_metrics_summary` ADD COLUMN `value` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `category`");
        }
        if (!$colExists('daily_metrics_summary', 'dimensions')) {
            $add("ALTER TABLE `daily_metrics_summary` ADD COLUMN `dimensions` LONGTEXT DEFAULT NULL AFTER `value`");
        }
        if (!$colExists('daily_metrics_summary', 'metric_date')) {
            $add("ALTER TABLE `daily_metrics_summary` ADD COLUMN `metric_date` DATE DEFAULT NULL AFTER `dimensions`");
        }
        if (!$idxExists('daily_metrics_summary', 'uniq_metric_date')) {
            // Duplicate key only if an identical UNIQUE already exists under another name; guard with try/catch.
            try {
                $add("ALTER TABLE `daily_metrics_summary` ADD UNIQUE KEY `uniq_metric_date` (`metric_name`, `metric_date`)");
            } catch (Throwable $e) {
                echo "SKIP uniq_metric_date (already covered): ", $e->getMessage(), "\n";
            }
        }
        echo "widen_daily_metrics_summary_s685 done\n";
        return true;
    },
];
