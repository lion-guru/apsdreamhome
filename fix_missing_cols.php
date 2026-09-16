<?php
// Add missing columns that were added after Sep 5 backup
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=apsdreamhome", "root", "");

// site_visits columns (added in Session 99)
$siteVisitsCols = [
    "ALTER TABLE site_visits ADD COLUMN cab_details VARCHAR(500) NULL AFTER pickup_time",
    "ALTER TABLE site_visits ADD COLUMN outcome VARCHAR(50) NULL AFTER status",
    "ALTER TABLE site_visits ADD COLUMN outcome_notes TEXT NULL AFTER outcome",
];

// payout_entries columns (added in Session 99)
$payoutEntriesCols = [
    "ALTER TABLE payout_entries ADD COLUMN utr_number VARCHAR(50) NULL AFTER payment_reference",
    "ALTER TABLE payout_entries ADD COLUMN paid_at DATETIME NULL AFTER processed_at",
];

// agentic_task_logs table (from Session 55/56)
$createAgenticTaskLog = "CREATE TABLE IF NOT EXISTS agentic_task_logs (id INT AUTO_INCREMENT PRIMARY KEY, agent VARCHAR(100), action VARCHAR(200), details JSON, status VARCHAR(50), created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_agent (agent), INDEX idx_status (status), INDEX idx_created (created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$errors = [];
foreach ($siteVisitsCols as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $sql\n";
    } catch(Exception $e) {
        if (strpos($e->getMessage(), 'already exists') === false) {
            $errors[] = "site_visits: " . $e->getMessage();
            echo "FAIL: $sql -> " . $e->getMessage() . "\n";
        } else {
            echo "SKIP (exists): " . substr($sql, 0, 50) . "\n";
        }
    }
}

foreach ($payoutEntriesCols as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $sql\n";
    } catch(Exception $e) {
        if (strpos($e->getMessage(), 'already exists') === false) {
            $errors[] = "payout_entries: " . $e->getMessage();
            echo "FAIL: $sql -> " . $e->getMessage() . "\n";
        } else {
            echo "SKIP (exists): " . substr($sql, 0, 50) . "\n";
        }
    }
}

try {
    $pdo->exec($createAgenticTaskLog);
    echo "OK: agentic_task_logs created\n";
} catch(Exception $e) {
    if (strpos($e->getMessage(), 'already exists') === false) {
        $errors[] = "agentic_task_logs: " . $e->getMessage();
        echo "FAIL: $e->getMessage()\n";
    } else {
        echo "SKIP: agentic_task_logs already exists\n";
    }
}

echo "\nDone. Errors: " . count($errors) . "\n";
