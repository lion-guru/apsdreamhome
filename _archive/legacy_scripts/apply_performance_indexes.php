<?php
/**
 * Apply Performance Indexes to Database
 * Safely applies recommended indexes from the optimization report
 */

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'apsdreamhome');
define('DB_USER', 'root');
define('DB_PASS', '');

// Recommended indexes from the optimization report
$recommendedIndexes = [
    'users' => [
        'idx_users_email' => 'email',
        'idx_users_phone' => 'phone',
        'idx_users_status' => 'status'
    ],
    'user_properties' => [
        'idx_user_properties_user_id' => 'user_id',
        'idx_user_properties_status' => 'status',
        'idx_user_properties_property_type' => 'property_type',
        'idx_user_properties_listing_type' => 'listing_type',
        'idx_user_properties_price' => 'price'
    ],
    'inquiries' => [
        'idx_inquiries_user_id' => 'user_id',
        'idx_inquiries_status' => 'status',
        'idx_inquiries_created_at' => 'created_at'
    ],
    'projects' => [
        'idx_projects_status' => 'status',
        'idx_projects_district_id' => 'district_id',
        'idx_projects_state_id' => 'state_id'
    ],
    'districts' => [
        'idx_districts_state_id' => 'state_id',
        'idx_districts_name' => 'name'
    ],
    'admin_menu_items' => [
        'idx_admin_menu_items_parent_id' => 'parent_id',
        'idx_admin_menu_items_sort_order' => 'sort_order'
    ],
    'leads' => [
        'idx_leads_status' => 'status',
        'idx_leads_assigned_to' => 'assigned_to',
        'idx_leads_created_at' => 'created_at'
    ],
    'bookings' => [
        'idx_bookings_user_id' => 'user_id',
        'idx_bookings_property_id' => 'property_id',
        'idx_bookings_status' => 'status',
        'idx_bookings_created_at' => 'created_at'
    ]
];

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== APPLYING PERFORMANCE INDEXES ===\n";
    echo "Database: " . DB_NAME . "\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n\n";
    
    $totalIndexes = 0;
    $appliedIndexes = 0;
    $skippedIndexes = 0;
    $failedIndexes = 0;
    
    foreach ($recommendedIndexes as $table => $indexes) {
        echo "Processing table: $table\n";
        
        // Check if table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        if (!$tableCheck) {
            echo "  ⚠ Table '$table' does not exist - skipping\n\n";
            continue;
        }
        
        // Get existing indexes
        $existingIndexes = [];
        $indexQuery = $pdo->query("SHOW INDEX FROM $table");
        while ($row = $indexQuery->fetch(PDO::FETCH_ASSOC)) {
            $existingIndexes[] = $row['Key_name'];
        }
        
        foreach ($indexes as $indexName => $column) {
            $totalIndexes++;
            
            // Check if index already exists
            if (in_array($indexName, $existingIndexes)) {
                echo "  ⊘ Index '$indexName' already exists - skipping\n";
                $skippedIndexes++;
                continue;
            }
            
            // Create index
            try {
                $sql = "CREATE INDEX $indexName ON $table($column)";
                $pdo->exec($sql);
                echo "  ✓ Created index: $indexName on $table($column)\n";
                $appliedIndexes++;
            } catch (PDOException $e) {
                echo "  ✗ Failed to create index $indexName: " . $e->getMessage() . "\n";
                $failedIndexes++;
            }
        }
        
        echo "\n";
    }
    
    echo "=== SUMMARY ===\n";
    echo "Total indexes processed: $totalIndexes\n";
    echo "Applied successfully: $appliedIndexes\n";
    echo "Skipped (already exist): $skippedIndexes\n";
    echo "Failed: $failedIndexes\n\n";
    
    if ($appliedIndexes > 0) {
        echo "✓ Performance indexes applied successfully!\n";
    } elseif ($skippedIndexes > 0) {
        echo "⊘ All recommended indexes already exist.\n";
    } else {
        echo "✗ No indexes were applied.\n";
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>