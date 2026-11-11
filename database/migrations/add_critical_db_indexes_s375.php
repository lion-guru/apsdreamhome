<?php
/**
 * Migration: Add Critical Database Indexes for Performance (Session 375)
 * 
 * Adds 8 critical indexes identified in the deep scan analysis:
 * 1. idx_leads_status_created ON leads(status, created_at)
 * 2. idx_leads_assigned_status ON leads(assigned_to, status)
 * 3. idx_leads_source_created ON leads(source, created_at)
 * 4. idx_bookings_customer_status ON plot_bookings(customer_id, status)
 * 5. idx_bookings_plot_status ON plot_bookings(plot_id, status)
 * 6. idx_notifications_user_read ON notifications(user_id, is_read, created_at)
 * 7. idx_lead_activities_lead_created ON lead_activities(lead_id, created_at)
 * 8. idx_payments_booking_created ON payments(booking_id, created_at)
 * 
 * These indexes target the most frequent query patterns:
 * - Lead filtering by status and date
 * - Lead assignment and status queries
 * - Booking payment and status lookups
 * - Notification retrieval
 * - Activity logging queries
 * 
 * Re-run safety: Uses IF NOT EXISTS equivalent via SHOW INDEX checks
 */

echo "=== Adding Critical Database Indexes ===\n\n";

// Direct PDO connection (avoiding app bootstrap)
$host = '127.0.0.1';
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Database connected\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$indexes = [
    [
        'name' => 'idx_leads_status_created',
        'table' => 'leads',
        'columns' => '(status, created_at)',
    ],
    [
        'name' => 'idx_leads_assigned_status',
        'table' => 'leads',
        'columns' => '(assigned_to, status)',
    ],
    [
        'name' => 'idx_leads_source_created',
        'table' => 'leads',
        'columns' => '(source, created_at)',
    ],
    [
        'name' => 'idx_bookings_customer_status',
        'table' => 'plot_bookings',
        'columns' => '(customer_id, status)',
    ],
    [
        'name' => 'idx_bookings_plot_status',
        'table' => 'plot_bookings',
        'columns' => '(plot_id, status)',
    ],
    [
        'name' => 'idx_notifications_user_read',
        'table' => 'notifications',
        'columns' => '(user_id, is_read, created_at)',
    ],
    [
        'name' => 'idx_lead_activities_lead_created',
        'table' => 'lead_activities',
        'columns' => '(lead_id, created_at)',
    ],
    [
        'name' => 'idx_payments_booking_created',
        'table' => 'payments',
        'columns' => '(booking_id, created_at)',
    ],
];

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($indexes as $idx) {
    $name = $idx['name'];
    $table = $idx['table'];
    $columns = $idx['columns'];
    
    // Check if index already exists
    $checkSql = "SHOW INDEX FROM `{$table}` WHERE Key_name = '{$name}'";
    $stmt = $pdo->query($checkSql);
    $exists = $stmt->fetch() !== false;
    
    if ($exists) {
        echo "⏭  SKIP: {$name} on {$table} already exists\n";
        $skipped++;
        continue;
    }
    
    // Create the index
    $createSql = "CREATE INDEX `{$name}` ON `{$table}` {$columns}";
    
    try {
        $pdo->exec($createSql);
        echo "✅ CREATED: {$name} ON {$table} {$columns}\n";
        $created++;
    } catch (Exception $e) {
        echo "❌ ERROR: Failed to create {$name} on {$table}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Created: {$created}\n";
echo "Skipped (already exist): {$skipped}\n";
echo "Errors: {$errors}\n";

if ($errors > 0) {
    exit(1);
}

echo "\n✅ All critical indexes processed successfully!\n";