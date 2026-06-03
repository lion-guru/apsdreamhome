<?php
/**
 * Restore tables that were incorrectly dropped
 * mlm_points: used by user_network.php (UI)
 * mlm_earnings: used by user_network.php (UI)
 * mlm_notification_log: used by AlertService
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== RESTORING NEEDED MLM TABLES ===\n\n";

// Get original schema from previous backup or recreate minimal
// We need to look at the actual columns used in code

// 1. mlm_points - used in user_network.php
//    Query: SELECT SUM(points) as total FROM mlm_points WHERE user_id = ?
//    Also: was in mlm_points_transactions: INSERT INTO mlm_points
$pdo->exec("
    CREATE TABLE IF NOT EXISTS mlm_points (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points INT NOT NULL DEFAULT 0,
        source VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "✓ Created mlm_points\n";

// 2. mlm_earnings - used in user_network.php
//    Query: SELECT SUM(amount) as total FROM mlm_earnings WHERE user_id = ?
$pdo->exec("
    CREATE TABLE IF NOT EXISTS mlm_earnings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        source VARCHAR(100),
        earning_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "✓ Created mlm_earnings\n";

// 3. mlm_notification_log - used in AlertService
//    Query: SELECT ... FROM mlm_notification_log
$pdo->exec("
    CREATE TABLE IF NOT EXISTS mlm_notification_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        notification_type VARCHAR(50),
        message TEXT,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'sent',
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "✓ Created mlm_notification_log\n";

// 4. mlm_referrals - used by RegistrationController, ReferralService, MLMGrowthReportController
//    Inserts to: (referrer_id, referred_id, referral_code, status, created_at)
//             or (referrer_user_id, referred_user_id, referral_type, channel, created_at)
//    SELECTs: WHERE status='active', LEFT JOIN ON a.id = r.sponsor_id
$pdo->exec("
    CREATE TABLE IF NOT EXISTS mlm_referrals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        referrer_user_id INT,
        referrer_id INT,
        referred_user_id INT,
        referred_id INT,
        sponsor_id INT,
        referral_code VARCHAR(50),
        referral_type VARCHAR(50),
        channel VARCHAR(50),
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_referrer (referrer_id),
        INDEX idx_referrer_user (referrer_user_id),
        INDEX idx_referred (referred_id),
        INDEX idx_referred_user (referred_user_id),
        INDEX idx_sponsor (sponsor_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
echo "✓ Created mlm_referrals (with all possible column variants)\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\nFinal table count: $after\n";
