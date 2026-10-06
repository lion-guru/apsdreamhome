<?php
/**
 * Allow purpose='claim' in otp_verifications (Claim-My-Booking flow).
 * Idempotent. Run: php scripts/ensure_otp_claim_purpose.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = App\Core\Database\Database::getInstance()->getConnection();
    $row = $db->query("SHOW COLUMNS FROM otp_verifications LIKE 'purpose'")->fetch(PDO::FETCH_ASSOC);
    $type = $row['Type'] ?? '';
    if (strpos($type, "'claim'") === false) {
        $db->exec("ALTER TABLE otp_verifications MODIFY purpose ENUM('login','registration','password_reset','account_verification','claim') NOT NULL");
        echo "added 'claim' to purpose enum\n";
    } else {
        echo "purpose enum already has 'claim'\n";
    }
    echo "OK\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
