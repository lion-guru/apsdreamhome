<?php
/**
 * Migration: Create company_credentials table and seed APS Dream Home data
 * Run: C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\add_company_credentials.php
 */

$config = require dirname(__DIR__) . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database.\n";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 1. Check if table exists
$tableCheck = $pdo->query("SHOW TABLES LIKE 'company_credentials'")->fetch();
if ($tableCheck) {
    echo "Table 'company_credentials' already exists. Skipping creation.\n";
} else {
    // 2. Create table
    $sql = "CREATE TABLE company_credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        credential_type ENUM('gst','pan','tan','cin','msme','rera','bank_account','digital_signature') NOT NULL,
        credential_label VARCHAR(100) NOT NULL,
        credential_value VARCHAR(255) NOT NULL,
        issuer VARCHAR(100) DEFAULT NULL,
        issue_date DATE DEFAULT NULL,
        expiry_date DATE DEFAULT NULL,
        document_path VARCHAR(500) DEFAULT NULL,
        is_primary TINYINT(1) DEFAULT 1,
        status ENUM('active','expired','suspended','pending_renewal') DEFAULT 'active',
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (credential_type),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql);
    echo "Table 'company_credentials' created successfully.\n";
}

// 3. Seed data
$existing = $pdo->query("SELECT COUNT(*) FROM company_credentials")->fetchColumn();
if ($existing > 0) {
    echo "Table already has {$existing} rows. Skipping seed.\n";
    exit(0);
}

$seeds = [
    ['gst', 'Company GSTIN', '09AAACN1234F1Z5', 'GST Network', null, null, null, 1, 'active', 'UP state GST registration'],
    ['pan', 'Company PAN', 'AAACN1234F', 'NSDL', '2020-01-15', null, null, 1, 'active', 'Permanent Account Number'],
    ['tan', 'TAN Number', 'DELN00123A', 'NSDL', '2020-01-15', null, null, 1, 'active', 'Tax Deduction Account Number'],
    ['cin', 'CIN Number', 'U70100UP2020PTC123456', 'MCA', '2020-03-20', null, null, 1, 'active', 'Corporate Identity Number'],
    ['rera', 'RERA Registration', 'UPRERAAGT12345', 'UP RERA', '2023-04-01', '2026-03-31', null, 1, 'active', 'Real Estate Regulatory Authority registration for all projects'],
    ['bank_account', 'Primary Bank Account', 'SBI - 12345678901 - IFSC SBIN0001234', 'State Bank of India', null, null, null, 1, 'active', 'Main operating account for all collections'],
    ['digital_signature', 'Digital Signature Certificate', 'DSC-APS-2024-001', 'eMudhra', '2024-01-01', '2026-12-31', null, 1, 'active', 'Class 3 DSC for digital agreements'],
];

$stmt = $pdo->prepare("
    INSERT INTO company_credentials
    (credential_type, credential_label, credential_value, issuer, issue_date, expiry_date, document_path, is_primary, status, notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$count = 0;
foreach ($seeds as $row) {
    $stmt->execute($row);
    $count++;
}

echo "Seeded {$count} company credential records.\n";
echo "Migration complete.\n";
