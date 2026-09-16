<?php
/**
 * Migration: Add legal_consent_accepted column to associates table
 * Enforces first-login Associate Code of Conduct clickwrap agreement.
 *
 * Usage: php scripts/migrate_associate_legal_consent.php
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3306;
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('DB_PASS') ?: '');
$db   = getenv('DB_DATABASE') ?: 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if table exists
    $tblCheck = $pdo->query("SHOW TABLES LIKE 'associates'");
    if ($tblCheck->rowCount() === 0) {
        echo "[SKIP] Table 'associates' does not exist.\n";
        exit(0);
    }

    // Check if column already exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM associates LIKE 'legal_consent_accepted'");
    if ($colCheck->rowCount() > 0) {
        echo "[OK] Column 'legal_consent_accepted' already exists. Skipping.\n";
        exit(0);
    }

    // Add the column — NULL means not yet accepted, timestamp means accepted
    $pdo->exec("
        ALTER TABLE associates
        ADD COLUMN legal_consent_accepted DATETIME NULL DEFAULT NULL
        COMMENT 'Timestamp when associate accepted the Code of Conduct. NULL = not yet accepted.'
        AFTER updated_at
    ");

    // Add an index for quick lookup of unaccepted associates
    $pdo->exec("CREATE INDEX idx_associates_legal_consent ON associates (legal_consent_accepted)");

    echo "[SUCCESS] Column 'legal_consent_accepted' added to associates table.\n";
    echo "[INFO] NULL = not yet accepted. Timestamp = accepted on that date/time.\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
