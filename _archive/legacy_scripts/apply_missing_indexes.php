<?php
/**
 * Apply missing performance indexes identified by audit_indexes.php
 * Idempotent: skips indexes that already exist
 * Generated: 2026-06-03 from audit
 */

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$indexes = [
    'plots' => [
        'idx_plots_plot_number' => 'plot_number',
    ],
    'bookings' => [
        'idx_bookings_booking_date' => 'booking_date',
    ],
    'commissions' => [
        'idx_commissions_created_at' => 'created_at',
    ],
    'site_visits' => [
        'idx_site_visits_visit_date' => 'visit_date',
        'idx_site_visits_status' => 'status',
    ],
    'ai_call_sessions' => [
        'idx_ai_call_sessions_started_at' => 'started_at',
    ],
    'mlm_commission_ledger' => [
        'idx_mlm_ledger_created_at' => 'created_at',
    ],
    'projects' => [
        'idx_projects_launch_date' => 'launch_date',
    ],
    'sms_queue' => [
        'idx_sms_queue_status' => 'status',
        'idx_sms_queue_scheduled_at' => 'scheduled_at',
    ],
    'support_tickets' => [
        'idx_support_tickets_assigned_to' => 'assigned_to',
    ],
    'cities' => [
        'idx_cities_name' => 'name',
    ],
];

echo "=== APPLYING MISSING PERFORMANCE INDEXES ===\n\n";
$applied = 0;
$skipped = 0;
$failed = 0;

foreach ($indexes as $table => $tableIndexes) {
    // Get existing indexes
    $existing = [];
    foreach ($pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $existing[] = $row['Key_name'];
    }

    foreach ($tableIndexes as $idxName => $col) {
        if (in_array($idxName, $existing)) {
            echo "  âŠ˜ $idxName already exists\n";
            $skipped++;
            continue;
        }
        try {
            $pdo->exec("CREATE INDEX `$idxName` ON `$table`(`$col`)");
            echo "  âœ“ Created $idxName on $table($col)\n";
            $applied++;
        } catch (PDOException $e) {
            echo "  âœ— Failed $idxName: {$e->getMessage()}\n";
            $failed++;
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Applied: $applied | Skipped: $skipped | Failed: $failed\n";?>