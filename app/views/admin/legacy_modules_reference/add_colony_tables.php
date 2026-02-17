<?php
/**
 * Add Colonies and Plots Tables
 * This script adds the colonies and plots tables if they don't exist
 */

// Include database configuration
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

echo "🔍 Adding Colonies and Plots Tables...\n\n";

$sql = [];

// Create colonies table if not exists
$sql[] = "CREATE TABLE IF NOT EXISTS `colonies` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `location` varchar(255) NOT NULL,
    `description` text,
    `total_area` varchar(50) DEFAULT NULL,
    `developed_area` varchar(50) DEFAULT NULL,
    `total_plots` int(11) DEFAULT 0,
    `available_plots` int(11) DEFAULT 0,
    `completion_status` enum('Planning','Under Development','Completed') DEFAULT 'Planning',
    `status` enum('available','sold_out','coming_soon') DEFAULT 'available',
    `starting_price` decimal(15,2) DEFAULT 0.00,
    `current_price` decimal(15,2) DEFAULT 0.00,
    `features` text,
    `amenities` text,
    `coordinates` text,
    `developer` varchar(255) DEFAULT 'APS Dream Homes Private Limited',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`),
    FULLTEXT KEY `ft_name_location` (`name`,`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Create plots table if not exists
$sql[] = "CREATE TABLE IF NOT EXISTS `plots` (
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

// Execute all SQL queries
$success = true;
foreach ($sql as $query) {
    try {
        $db->execute($query);
        echo "✅ Executed successfully: " . substr($query, 0, 100) . "...\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        $success = false;
    }
}

// Insert sample data if tables were created successfully
if ($success) {
    echo "\n📥 Inserting sample data...\n";
    
    // Sample colonies data
    $colonies = [
        [
            'name' => 'Suryoday Colony',
            'location' => 'Gorakhpur, Uttar Pradesh',
            'description' => 'Premium residential colony developed by APS Dream Homes in Gorakhpur',
            'total_area' => '25 Acres',
            'developed_area' => '25 Acres',
            'total_plots' => 200,
            'available_plots' => 0,
            'completion_status' => 'Completed',
            'status' => 'sold_out',
            'starting_price' => 1200000.00,
            'current_price' => 1500000.00,
            'features' => '24/7 Security,Wide Roads,Green Spaces,Community Hall,Children Play Area',
            'amenities' => 'Power Backup,Water Supply,Sewage System,Street Lights,Landscaped Gardens',
            'coordinates' => '{"latitude": 26.7606, "longitude": 83.3732}',
            'developer' => 'APS Dream Homes Private Limited'
        ],
        [
            'name' => 'Raghunath Nagari',
            'location' => 'Gorakhpur, Uttar Pradesh',
            'description' => 'Luxury residential project with modern amenities',
            'total_area' => '15 Acres',
            'developed_area' => '15 Acres',
            'total_plots' => 150,
            'available_plots' => 0,
            'completion_status' => 'Completed',
            'status' => 'sold_out',
            'starting_price' => 1500000.00,
            'current_price' => 1800000.00,
            'features' => 'Gated Community,24/7 Security,Club House,Swimming Pool,Jogging Track',
            'amenities' => 'Power Backup,Water Supply,Underground Electricity,Landscaped Gardens',
            'coordinates' => '{"latitude": 26.7445, "longitude": 83.4032}',
            'developer' => 'APS Dream Homes Private Limited'
        ],
        [
            'name' => 'Brajradha Nagri',
            'location' => 'Gorakhpur, Uttar Pradesh',
            'description' => 'Affordable housing with all basic amenities',
            'total_area' => '20 Acres',
            'developed_area' => '20 Acres',
            'total_plots' => 180,
            'available_plots' => 0,
            'completion_status' => 'Completed',
            'status' => 'sold_out',
            'starting_price' => 1000000.00,
            'current_price' => 1300000.00,
            'features' => '24/7 Security,Park,Community Center,Children Play Area',
            'amenities' => 'Water Supply,Electricity,Street Lights',
            'coordinates' => '{"latitude": 26.7523, "longitude": 83.3921}',
            'developer' => 'APS Dream Homes Private Limited'
        ],
        [
            'name' => 'Stuti Bihar',
            'location' => 'Sonbarsa, Gorakhpur',
            'description' => 'Peaceful living in the lap of nature',
            'total_area' => '30 Acres',
            'developed_area' => '30 Acres',
            'total_plots' => 250,
            'available_plots' => 0,
            'completion_status' => 'Completed',
            'status' => 'sold_out',
            'starting_price' => 800000.00,
            'current_price' => 1100000.00,
            'features' => 'Green Belt,24/7 Security,Temple,Community Hall',
            'amenities' => 'Water Supply,Electricity,Well-connected Roads',
            'coordinates' => '{"latitude": 26.7356, "longitude": 83.4154}',
            'developer' => 'APS Dream Homes Private Limited'
        ]
    ];
    
    // Insert colonies
    $colonyIds = [];
    foreach ($colonies as $colony) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO colonies (" . implode(',', array_keys($colony)) . ") VALUES (" . str_repeat('?,', count($colony) - 1) . "?)");
        $stmt->execute(array_values($colony));
        $colonyId = $pdo->lastInsertId();
        if ($colonyId) {
            $colonyIds[$colony['name']] = $colonyId;
            echo "✅ Added colony: " . $colony['name'] . " (ID: $colonyId)\n";
        } else {
            // If colony already exists, get its ID
            $stmt = $pdo->prepare("SELECT id FROM colonies WHERE name = ?");
            $stmt->execute([$colony['name']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $colonyIds[$colony['name']] = $result['id'];
                echo "ℹ️  Colony already exists: " . $colony['name'] . " (ID: " . $result['id'] . ")\n";
            }
        }
    }
    
    // Sample plots data
    $plots = [
        // Suryoday Colony plots
        ['colonies_id' => $colonyIds['Suryoday Colony'], 'plot_number' => 'SYD-101', 'size' => 2000.00, 'price' => 1500000.00, 'status' => 'sold', 'facing' => 'East', 'corner_plot' => 0, 'booking_amount' => 150000.00],
        ['colonies_id' => $colonyIds['Suryoday Colony'], 'plot_number' => 'SYD-102', 'size' => 2200.00, 'price' => 1650000.00, 'status' => 'sold', 'facing' => 'North', 'corner_plot' => 1, 'booking_amount' => 165000.00],
        
        // Raghunath Nagari plots
        ['colonies_id' => $colonyIds['Raghunath Nagari'], 'plot_number' => 'RN-201', 'size' => 2500.00, 'price' => 1800000.00, 'status' => 'sold', 'facing' => 'South', 'corner_plot' => 1, 'booking_amount' => 180000.00],
        ['colonies_id' => $colonyIds['Raghunath Nagari'], 'plot_number' => 'RN-202', 'size' => 2300.00, 'price' => 1740000.00, 'status' => 'sold', 'facing' => 'East', 'corner_plot' => 0, 'booking_amount' => 174000.00],
        
        // Brajradha Nagri plots
        ['colonies_id' => $colonyIds['Brajradha Nagri'], 'plot_number' => 'BN-301', 'size' => 1500.00, 'price' => 1300000.00, 'status' => 'sold', 'facing' => 'North', 'corner_plot' => 0, 'booking_amount' => 130000.00],
        
        // Stuti Bihar plots
        ['colonies_id' => $colonyIds['Stuti Bihar'], 'plot_number' => 'SB-401', 'size' => 1200.00, 'price' => 1100000.00, 'status' => 'sold', 'facing' => 'West', 'corner_plot' => 0, 'booking_amount' => 110000.00]
    ];
    
    // Insert plots
    foreach ($plots as $plot) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO plots (" . implode(',', array_keys($plot)) . ") VALUES (" . str_repeat('?,', count($plot) - 1) . "?)");
        $stmt->execute(array_values($plot));
        echo "✅ Added plot: " . $plot['plot_number'] . " (Colony ID: " . $plot['colony_id'] . ")\n";
    }
    
    echo "\n✅ Sample data inserted successfully!\n";
}

echo "\n🏁 Script execution completed!\n";
