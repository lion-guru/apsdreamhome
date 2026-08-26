<?php
/**
 * Session 78+ — Missing analytics/tracking tables.
 *
 * Creates tables that live code referenced but never existed:
 * - visitor_sessions        VisitorTrackingService::trackSession()
 * - visitor_page_views      VisitorTrackingService::trackPageView()
 * - whatsapp_click_log      routes/api.php WA click tracking endpoint
 *
 * Idempotent: safe to re-run (CREATE TABLE IF NOT EXISTS).
 * Run: php scripts/migrate_tracking_tables.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

$pdo = App\Core\Database\Database::getInstance()->getConnection();

$migrations = [
    'visitor_sessions' => "
        CREATE TABLE IF NOT EXISTS visitor_sessions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            session_id VARCHAR(128) NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent VARCHAR(500) NULL,
            referrer VARCHAR(500) NULL,
            landing_page VARCHAR(500) NULL,
            first_visit DATETIME NULL,
            last_visit DATETIME NULL,
            page_views INT UNSIGNED DEFAULT 1,
            time_on_site INT UNSIGNED DEFAULT 0,
            is_converted TINYINT(1) DEFAULT 0,
            converted_user_id BIGINT UNSIGNED NULL,
            converted_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_vs_session (session_id),
            INDEX idx_vs_tenant (tenant_id),
            INDEX idx_vs_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'visitor_page_views' => "
        CREATE TABLE IF NOT EXISTS visitor_page_views (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            session_id VARCHAR(128) NOT NULL,
            page_url VARCHAR(500) NULL,
            page_title VARCHAR(255) NULL,
            visited_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_vpv_session (session_id),
            INDEX idx_vpv_visited (visited_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'whatsapp_click_log' => "
        CREATE TABLE IF NOT EXISTS whatsapp_click_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            user_id BIGINT UNSIGNED NULL,
            source_page VARCHAR(255) NULL,
            referral_page VARCHAR(500) NULL,
            clicked_at DATETIME NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_wcl_user (user_id),
            INDEX idx_wcl_clicked (clicked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($migrations as $table => $sql) {
    try {
        $pdo->exec($sql);
        echo "OK   {$table}\n";
    } catch (Throwable $e) {
        echo "FAIL {$table}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Done — tracking tables ready.\n";
