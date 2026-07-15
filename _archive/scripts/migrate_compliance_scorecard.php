<?php
/**
 * Migration: compliance_scorecard_trend table for Compliance Scorecard feature
 * Run: php scripts/migrate_compliance_scorecard.php
 */

$host = '127.0.0.1';
$port = 3307;
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("USE {$db}");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS compliance_scorecard_trend (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            overall_score INT UNSIGNED NOT NULL DEFAULT 0,
            area_scores JSON DEFAULT NULL,
            checked_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_compliance_trend_checked (checked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "OK: compliance_scorecard_trend table created/verified\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
