<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$columns = [
    "ADD COLUMN lead_id INT NULL AFTER user_id",
    "ADD COLUMN colony_id INT NULL AFTER lead_id",
    "ADD COLUMN assigned_to INT NULL AFTER colony_id",
    "ADD COLUMN duration_minutes INT DEFAULT 60 AFTER assigned_to",
    "ADD COLUMN feedback TEXT NULL AFTER notes",
    "ADD COLUMN rating TINYINT NULL AFTER feedback",
    "ADD COLUMN completed_at DATETIME NULL AFTER rating",
];

echo "Altering site_visits table...\n";
foreach ($columns as $col) {
    try {
        $db->exec("ALTER TABLE site_visits $col");
        echo "  OK: $col\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "  SKIP (already exists): $col\n";
        } else {
            echo "  ERROR: $col â€” " . $e->getMessage() . "\n";
        }
    }
}

// Add indexes
try {
    $db->exec("ALTER TABLE site_visits ADD INDEX idx_sv_lead_id (lead_id)");
    echo "  OK: idx_sv_lead_id\n";
} catch (PDOException $e) { echo "  SKIP: idx_sv_lead_id\n"; }

try {
    $db->exec("ALTER TABLE site_visits ADD INDEX idx_sv_assigned_to (assigned_to)");
    echo "  OK: idx_sv_assigned_to\n";
} catch (PDOException $e) { echo "  SKIP: idx_sv_assigned_to\n"; }

try {
    $db->exec("ALTER TABLE site_visits ADD INDEX idx_sv_visit_date (visit_date)");
    echo "  OK: idx_sv_visit_date\n";
} catch (PDOException $e) { echo "  SKIP: idx_sv_visit_date\n"; }

echo "\nDone! Verifying schema:\n";
$rows = $db->query('DESCRIBE site_visits')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  {$r['Field']} | {$r['Type']}\n";
}?>