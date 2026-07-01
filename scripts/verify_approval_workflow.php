<?php
require_once __DIR__ . '/../app/Core/autoload.php';
$db = \App\Core\Database\Database::getInstance();

echo "=== Registration Approval Workflow - Verification ===\n\n";

// Check users table columns
echo "1. Users table columns:\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM users");
$regCols = array_filter($cols, fn($c) => in_array($c['Field'], ['registration_status', 'kyc_status', 'rejection_reason', 'approved_by', 'approved_at']));
foreach ($regCols as $col) {
    echo "   ✅ {$col['Field']} ({$col['Type']})\n";
}

// Check KYC table
echo "\n2. user_kyc_documents table:\n";
$kycTable = $db->fetchOne("SHOW TABLES LIKE 'user_kyc_documents'");
echo $kycTable ? "   ✅ Table exists\n" : "   ❌ Table not found\n";

// Check pending users
echo "\n3. Pending users count:\n";
$pending = $db->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE registration_status = 'pending'");
echo "   Pending: " . ($pending['cnt'] ?? 0) . "\n";

// Check all registration statuses
echo "\n4. Registration status distribution:\n";
$statuses = $db->fetchAll("SELECT registration_status, COUNT(*) as cnt FROM users GROUP BY registration_status");
foreach ($statuses as $s) {
    echo "   {$s['registration_status']}: {$s['cnt']}\n";
}

echo "\n=== Verification Complete! ===\n";
