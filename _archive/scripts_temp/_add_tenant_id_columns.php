<?php
/**
 * Migration: Add tenant_id columns to key tables for SaaS multi-tenancy.
 * 
 * Tables altered:
 *   - leads (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - crm_settings (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - plot_bookings (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - user_properties (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - testimonials (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - blog_posts (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - email_templates (tenant_id INT UNSIGNED, DEFAULT 1)
 *   - sms_templates (tenant_id INT UNSIGNED, DEFAULT 1)
 * 
 * Safe to run multiple times (checks before adding).
 * All existing rows default to tenant_id=1 (APS Dream Home).
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dbHost = '127.0.0.1';
$dbPort = '3307';
$dbUser = 'root';
$dbPass = '';
$dbName = 'apsdreamhome';

try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName}", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Connected to MySQL.\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$tables = [
    'leads',
    'crm_settings',
    'plot_bookings',
    'user_properties',
    'testimonials',
    'blog_posts',
    'email_templates',
    'sms_templates',
];

$added = 0;
$skipped = 0;

foreach ($tables as $table) {
    echo "Processing: {$table}\n";
    
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE 'tenant_id'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "  -> tenant_id already exists. Skipping.\n";
        $skipped++;
        continue;
    }
    
    // Add column
    try {
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id");
        echo "  -> Added tenant_id column.\n";
    } catch (PDOException $e) {
        echo "  -> ERROR adding column: " . $e->getMessage() . "\n";
        continue;
    }
    
    // Add index (non-unique for performance)
    try {
        $pdo->exec("CREATE INDEX idx_{$table}_tenant ON {$table}(tenant_id)");
        echo "  -> Added index idx_{$table}_tenant.\n";
    } catch (PDOException $e) {
        // Index might already exist or fail for other reasons — non-critical
        echo "  -> Index creation note: " . $e->getMessage() . "\n";
    }
    
    $added++;
}

echo "\n=== Migration Complete ===\n";
echo "Added: {$added} tables\n";
echo "Skipped: {$skipped} tables (already had tenant_id)\n";

// Verify: show tenant_id columns across all tables
echo "\n=== Verification ===\n";
$stmt = $pdo->query("
    SELECT TABLE_NAME, COLUMN_TYPE, COLUMN_DEFAULT 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = 'apsdreamhome' AND COLUMN_NAME = 'tenant_id'
    ORDER BY TABLE_NAME
");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " tables now have tenant_id:\n";
foreach ($rows as $row) {
    echo "  {$row['TABLE_NAME']} ({$row['COLUMN_TYPE']}, default={$row['COLUMN_DEFAULT']})\n";
}
