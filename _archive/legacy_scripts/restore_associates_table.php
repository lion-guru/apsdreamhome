<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tableExists = $pdo->query("SHOW TABLES LIKE 'associates'")->fetch();
if ($tableExists) {
    echo "associates table already exists, skipping create\n";
    exit;
}

$pdo->exec("
CREATE TABLE associates (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NULL,
    name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    level ENUM('bronze','silver','gold','platinum','diamond') DEFAULT 'bronze',
    referral_code VARCHAR(20) NULL,
    sponsor_id INT(11) NULL,
    status ENUM('active','inactive','suspended','pending') DEFAULT 'active',
    total_sales DECIMAL(12,2) NULL DEFAULT 0,
    commission_earned DECIMAL(12,2) NULL DEFAULT 0,
    team_size INT(11) NULL DEFAULT 0,
    joining_date DATE NULL,
    address TEXT NULL,
    pan_number VARCHAR(20) NULL,
    aadhaar_number VARCHAR(20) NULL,
    bank_account VARCHAR(50) NULL,
    bank_ifsc VARCHAR(20) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_assoc_email (email),
    UNIQUE KEY uniq_assoc_referral (referral_code),
    KEY idx_assoc_user_id (user_id),
    KEY idx_assoc_sponsor_id (sponsor_id),
    KEY idx_assoc_level (level),
    KEY idx_assoc_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "associates table created\n";

$stmt = $pdo->query("SELECT u.id AS user_id, u.email, u.name, u.phone FROM users u WHERE u.role = 'associate'");
$associates = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($associates) . " associate users\n";

$insert = $pdo->prepare("
    INSERT INTO associates (user_id, name, email, phone, level, referral_code, status, joining_date, total_sales, commission_earned, team_size)
    VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?)
");
$count = 0;
foreach ($associates as $a) {
    $existing = $pdo->prepare("SELECT id FROM associates WHERE email COLLATE utf8mb4_unicode_ci = ?");
    $existing->execute([$a['email']]);
    if ($existing->fetch()) {
        echo "  Skip (exists): {$a['email']}\n";
        continue;
    }
    $code = 'REF' . strtoupper(substr(md5($a['email']), 0, 8));
    $insert->execute([
        $a['user_id'],
        $a['name'],
        $a['email'],
        $a['phone'],
        'bronze',
        $code,
        date('Y-m-d', strtotime('-2 years -' . ($count * 30) . ' days')),
        0,
        0,
        0
    ]);
    echo "  Created: {$a['email']} -> $code\n";
    $count++;
}
echo "\n$count associates created\n";

// Relink just in case
$updated = $pdo->exec("
    UPDATE associates e
    JOIN users u ON u.email COLLATE utf8mb4_unicode_ci = e.email COLLATE utf8mb4_unicode_ci
    SET e.user_id = u.id
    WHERE e.user_id IS NULL OR e.user_id != u.id
");
echo "Re-linked $updated associates\n";

$rows = $pdo->query("SELECT id, user_id, name, email, referral_code, level FROM associates ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== Final state ===\n";
echo "Total: " . count($rows) . " associates\n";
foreach ($rows as $r) {
    echo "  ID={$r['id']} user_id={$r['user_id']} code={$r['referral_code']} level={$r['level']} email={$r['email']}\n";
}?>