<?php
/**
 * Migration: Enhance gallery table + create career_benefits + career_applications
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'].';charset=utf8mb4', $config['username'], $config['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Add columns to gallery table
$cols = $pdo->query("SHOW COLUMNS FROM gallery")->fetchAll(PDO::FETCH_COLUMN);
$adds = [
    "ALTER TABLE gallery ADD COLUMN title VARCHAR(255) DEFAULT NULL AFTER image_path",
    "ALTER TABLE gallery ADD COLUMN category VARCHAR(50) DEFAULT 'general' AFTER title",
    "ALTER TABLE gallery ADD COLUMN description TEXT DEFAULT NULL AFTER caption",
    "ALTER TABLE gallery ADD COLUMN sort_order INT DEFAULT 0 AFTER status",
    "ALTER TABLE gallery ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL AFTER created_at",
];
foreach ($adds as $sql) {
    $col = preg_match('/ADD COLUMN (\w+)/', $sql, $m) ? $m[1] : '';
    if ($col && !in_array($col, $cols)) {
        $pdo->exec($sql);
        echo "OK: Added $col to gallery" . PHP_EOL;
    } else {
        echo "SKIP: $col already exists" . PHP_EOL;
    }
}

// 2. Create career_benefits table
$pdo->exec("CREATE TABLE IF NOT EXISTS career_benefits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'fa-star',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK: career_benefits table" . PHP_EOL;

// 3. Create career_applications table
$pdo->exec("CREATE TABLE IF NOT EXISTS career_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    career_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    resume_path VARCHAR(500) DEFAULT NULL,
    cover_letter TEXT DEFAULT NULL,
    experience_years INT DEFAULT NULL,
    current_company VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','reviewed','shortlisted','rejected','hired') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_applications_career (career_id),
    KEY idx_applications_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK: career_applications table" . PHP_EOL;

// 4. Seed career_benefits (6 default benefits)
$benefits = [
    ['Career Growth', 'Accelerate your career with clear growth paths and mentorship programs', 'fa-chart-line', 1],
    ['Collaborative Culture', 'Work with passionate people who believe in teamwork and innovation', 'fa-people-group', 2],
    ['Work-Life Balance', 'Flexible working hours and policies that respect your personal time', 'fa-scale-balanced', 3],
    ['Competitive Pay', 'Industry-leading salaries with performance-based bonuses', 'fa-indian-rupee-sign', 4],
    ['Health & Wellness', 'Comprehensive health insurance and wellness programs for you and your family', 'fa-heart-pulse', 5],
    ['Meaningful Impact', 'Be part of creating dream homes for thousands of families', 'fa-bullseye', 6],
];
$stmt = $pdo->prepare("INSERT IGNORE INTO career_benefits (title, description, icon, sort_order) VALUES (?, ?, ?, ?)");
foreach ($benefits as $b) {
    $stmt->execute($b);
}
echo "OK: Seeded " . count($benefits) . " career benefits" . PHP_EOL;

// 5. Delete empty gallery seed rows (all have empty image_path)
$pdo->exec("DELETE FROM gallery WHERE image_path = '' OR image_path IS NULL");
$deleted = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();
echo "OK: Cleaned " . $deleted . " empty gallery rows" . PHP_EOL;

echo PHP_EOL . "DONE" . PHP_EOL;
