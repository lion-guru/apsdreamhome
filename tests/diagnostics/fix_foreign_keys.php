<?php
/**
 * Fix Foreign Keys
 * This script disables foreign key checks, drops and recreates the plots table
 */

// Include database configuration
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connection.php';

echo "🔧 Fixing foreign key constraints...\n\n";

try {
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✅ Disabled foreign key checks\n";
    
    // Drop existing plots table if it exists
    $pdo->exec("DROP TABLE IF EXISTS `plots`");
    echo "✅ Dropped existing plots table\n";
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Re-enabled foreign key checks\n";
    
    // Create new plots table
    $sql = "CREATE TABLE `plots` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colonies_id` int(11) NOT NULL,
        `plot_number` varchar(50) NOT NULL,
        `size` decimal(10,2) NOT NULL COMMENT 'in square feet',
        `price` decimal(15,2) NOT NULL,
        `status` enum('available','booked','sold','blocked') DEFAULT 'available',
        `facing` varchar(50) DEFAULT NULL,
        `corner_plot` tinyint(1) DEFAULT 0,
        `booking_amount` decimal(15,2) DEFAULT 0.00,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colonies_id` (`colonies_id`),
        KEY `idx_plot_status` (`status`),
        CONSTRAINT `plots_ibfk_1` FOREIGN KEY (`colonies_id`) REFERENCES `colonies` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "✅ Created new plots table with correct structure\n";
    
    // Insert sample plots data
    $plots = [
        // Suryoday Colony plots (ID: 1)
        ['colonies_id' => 1, 'plot_number' => 'SYD-101', 'size' => 2000.00, 'price' => 1500000.00, 'status' => 'sold', 'facing' => 'East', 'corner_plot' => 0, 'booking_amount' => 150000.00],
        ['colonies_id' => 1, 'plot_number' => 'SYD-102', 'size' => 2200.00, 'price' => 1650000.00, 'status' => 'sold', 'facing' => 'North', 'corner_plot' => 1, 'booking_amount' => 165000.00],
        
        // Raghunath Nagari plots (ID: 2)
        ['colonies_id' => 2, 'plot_number' => 'RN-201', 'size' => 2500.00, 'price' => 1800000.00, 'status' => 'sold', 'facing' => 'South', 'corner_plot' => 1, 'booking_amount' => 180000.00],
        ['colonies_id' => 2, 'plot_number' => 'RN-202', 'size' => 2300.00, 'price' => 1740000.00, 'status' => 'sold', 'facing' => 'East', 'corner_plot' => 0, 'booking_amount' => 174000.00],
        
        // Brajradha Nagri plots (ID: 3)
        ['colonies_id' => 3, 'plot_number' => 'BN-301', 'size' => 1500.00, 'price' => 1300000.00, 'status' => 'sold', 'facing' => 'North', 'corner_plot' => 0, 'booking_amount' => 130000.00],
        
        // Stuti Bihar plots (ID: 4)
        ['colonies_id' => 4, 'plot_number' => 'SB-401', 'size' => 1200.00, 'price' => 1100000.00, 'status' => 'sold', 'facing' => 'West', 'corner_plot' => 0, 'booking_amount' => 110000.00]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO plots (colonies_id, plot_number, size, price, status, facing, corner_plot, booking_amount) 
                           VALUES (:colonies_id, :plot_number, :size, :price, :status, :facing, :corner_plot, :booking_amount)");
    
    foreach ($plots as $plot) {
        $stmt->execute($plot);
        echo "✅ Added plot: " . $plot['plot_number'] . " (Colony ID: " . $plot['colonies_id'] . ")\n";
    }
    
    echo "\n✅ Successfully added " . count($plots) . " plots to the database!\n";
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

echo "\n🏁 Script execution completed!\n";
