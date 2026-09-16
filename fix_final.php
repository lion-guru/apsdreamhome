<?php
// Fix site_visits columns - check existing columns first
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome", "root", "");
$cols = $pdo->query("SHOW COLUMNS FROM site_visits")->fetchAll(PDO::FETCH_COLUMN);
echo "site_visits columns: " . implode(", ", $cols) . "\n";

// Add cab_details at the end instead of after pickup_time
try {
    $pdo->exec("ALTER TABLE site_visits ADD COLUMN cab_details VARCHAR(500) NULL");
    echo "OK: cab_details added\n";
} catch(Exception $e) {
    echo "SKIP: " . $e->getMessage() . "\n";
}

// Verify all missing columns are now present
echo "\n=== FINAL VERIFICATION ===\n";
$checks = [
    ['site_visits', 'cab_details'], ['site_visits', 'outcome'], ['site_visits', 'outcome_notes'],
    ['payout_entries', 'utr_number'], ['payout_entries', 'paid_at'],
];
$allOk = true;
foreach ($checks as $c) {
    $cols = $pdo->query("SHOW COLUMNS FROM {$c[0]}")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array($c[1], $cols)) {
        echo "  [OK] {$c[0]}.{$c[1]}\n";
    } else {
        echo "  [MISSING] {$c[0]}.{$c[1]}\n";
        $allOk = false;
    }
}

if ($allOk) {
    echo "\nAll columns present!\n";
}

// Final count
$total = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='apsdreamhome'")->fetchColumn();
echo "\nTotal tables: $total\n";
echo "All missing schema fixes complete!\n";
