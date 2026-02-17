<?php
/**
 * APS Dream Homes - Colony Data Setup Script
 * Add APS Dream Homes actual colonies to database
 */

require_once 'includes/db_connection.php';

// Colony data based on user information
$colonies_data = [
    [
        'name' => 'Suryoday Colony',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'description' => 'Premium residential colony developed by APS Dream Homes in Gorakhpur',
        'total_area' => '25 Acres',
        'developed_area' => '25 Acres',
        'total_plots' => 200,
        'available_plots' => 0, // Sold out
        'completion_status' => 'Complete',
        'status' => 'sold_out',
        'starting_price' => 1200000,
        'current_price' => 1500000,
        'features' => ['24/7 Security', 'Wide Roads', 'Green Spaces', 'Community Hall', 'Children Play Area'],
        'amenities' => ['Power Backup', 'Water Supply', 'Sewage System', 'Street Lights', 'Landscaped Gardens'],
        'coordinates' => ['latitude' => 26.7606, 'longitude' => 83.3732], // Gorakhpur coordinates
        'developer' => 'APS Dream Homes Private Limited'
    ],
    [
        'name' => 'Raghunath Nagri',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'description' => 'Modern residential colony with excellent connectivity and amenities',
        'total_area' => '30 Acres',
        'developed_area' => '25 Acres',
        'total_plots' => 250,
        'available_plots' => 25,
        'completion_status' => 'Phase 2 Ongoing',
        'status' => 'active',
        'starting_price' => 1000000,
        'current_price' => 1300000,
        'features' => ['Prime Location', 'Modern Infrastructure', 'Investment Opportunity', 'Easy Financing'],
        'amenities' => ['Club House', 'Swimming Pool', 'Gym', 'Jogging Track', 'Security'],
        'coordinates' => ['latitude' => 26.7700, 'longitude' => 83.3800],
        'developer' => 'APS Dream Homes Private Limited'
    ],
    [
        'name' => 'Brajradha Nagri',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'description' => 'Luxury residential development with world-class facilities',
        'total_area' => '35 Acres',
        'developed_area' => '20 Acres',
        'total_plots' => 300,
        'available_plots' => 80,
        'completion_status' => 'Development Started',
        'status' => 'active',
        'starting_price' => 1500000,
        'current_price' => 1800000,
        'features' => ['Premium Location', 'High Appreciation', 'Modern Design', 'Luxury Living'],
        'amenities' => ['Community Hall', 'Landscaped Gardens', 'Security', 'Power Backup', 'Water Management'],
        'coordinates' => ['latitude' => 26.7500, 'longitude' => 83.3600],
        'developer' => 'APS Dream Homes Private Limited'
    ],
    [
        'name' => 'Stuti Bihar Sonbarsa',
        'location' => 'Sonbarsa, Gorakhpur, Uttar Pradesh',
        'description' => 'Peaceful residential colony in serene environment - COMPLETED & SOLD OUT',
        'total_area' => '20 Acres',
        'developed_area' => '20 Acres',
        'total_plots' => 150,
        'available_plots' => 0, // Sold out
        'completion_status' => 'Complete',
        'status' => 'sold_out',
        'starting_price' => 800000,
        'current_price' => 1100000,
        'features' => ['Peaceful Environment', 'Complete Infrastructure', 'High Appreciation', 'Sold Out'],
        'amenities' => ['Complete Infrastructure', 'Roads', 'Electricity', 'Water', 'Sewage'],
        'coordinates' => ['latitude' => 26.7800, 'longitude' => 83.3900],
        'developer' => 'APS Dream Homes Private Limited'
    ]
];

// Sample plots for active colonies
$sample_plots = [
    [
        'colony_name' => 'Raghunath Nagri',
        'plots' => [
            ['plot_number' => 'A-101', 'area' => 150, 'price' => 1300000, 'status' => 'available', 'facing' => 'North'],
            ['plot_number' => 'A-102', 'area' => 120, 'price' => 1040000, 'status' => 'available', 'facing' => 'East'],
            ['plot_number' => 'B-205', 'area' => 200, 'price' => 1740000, 'status' => 'available', 'facing' => 'South'],
            ['plot_number' => 'B-206', 'area' => 180, 'price' => 1566000, 'status' => 'booked', 'facing' => 'West'],
            ['plot_number' => 'C-301', 'area' => 160, 'price' => 1392000, 'status' => 'sold', 'facing' => 'North-East']
        ]
    ],
    [
        'colony_name' => 'Brajradha Nagri',
        'plots' => [
            ['plot_number' => 'P-001', 'area' => 200, 'price' => 1800000, 'status' => 'available', 'facing' => 'North'],
            ['plot_number' => 'P-002', 'area' => 180, 'price' => 1620000, 'status' => 'available', 'facing' => 'East'],
            ['plot_number' => 'P-003', 'area' => 250, 'price' => 2250000, 'status' => 'available', 'facing' => 'South'],
            ['plot_number' => 'Q-101', 'area' => 220, 'price' => 1980000, 'status' => 'booked', 'facing' => 'West'],
            ['plot_number' => 'Q-102', 'area' => 190, 'price' => 1710000, 'status' => 'sold', 'facing' => 'North-West']
        ]
    ]
];

try {
    // Insert colonies
    foreach ($colonies_data as $colony) {
        $sql = "INSERT INTO colonies (
            name, location, description, total_area, developed_area,
            total_plots, available_plots, completion_status, status,
            starting_price, current_price, features, amenities,
            latitude, longitude, developer, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        ) ON DUPLICATE KEY UPDATE
        name = VALUES(name), location = VALUES(location), updated_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $colony['name'],
            $colony['location'],
            $colony['description'],
            $colony['total_area'],
            $colony['developed_area'],
            $colony['total_plots'],
            $colony['available_plots'],
            $colony['completion_status'],
            $colony['status'],
            $colony['starting_price'],
            $colony['current_price'],
            json_encode($colony['features']),
            json_encode($colony['amenities']),
            $colony['coordinates']['latitude'],
            $colony['coordinates']['longitude'],
            $colony['developer']
        ]);

        echo "✅ Added/Updated Colony: " . $colony['name'] . "\n";
    }

    // Insert sample plots for active colonies
    foreach ($sample_plots as $colony_plots) {
        $colony_name = $colony_plots['colony_name'];

        // Get colony ID
        $stmt = $pdo->prepare("SELECT id FROM colonies WHERE name = ?");
        $stmt->execute([$colony_name]);
        $colony_id = $stmt->fetchColumn();

        if ($colony_id) {
            foreach ($colony_plots['plots'] as $plot) {
                $sql = "INSERT INTO plots (
                    colony_id, plot_number, plot_area, price_per_sqyard,
                    total_price, status, facing, features, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                plot_area = VALUES(plot_area), total_price = VALUES(total_price),
                status = VALUES(status), updated_at = NOW()";

                $price_per_sqyard = $plot['price'] / $plot['area'];

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $colony_id,
                    $plot['plot_number'],
                    $plot['area'],
                    round($price_per_sqyard, 2),
                    $plot['price'],
                    $plot['status'],
                    $plot['facing'],
                    json_encode(['Corner Plot', 'Main Road', $plot['facing'] . ' Facing'])
                ]);

                echo "✅ Added Plot: " . $plot['plot_number'] . " in " . $colony_name . "\n";
            }
        }
    }

    // Create colonies table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS `colonies` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `location` varchar(255) NOT NULL,
        `description` text,
        `total_area` varchar(50) DEFAULT NULL,
        `developed_area` varchar(50) DEFAULT NULL,
        `total_plots` int(11) DEFAULT 0,
        `available_plots` int(11) DEFAULT 0,
        `completion_status` varchar(100) DEFAULT NULL,
        `status` enum('active','sold_out','upcoming','on_hold') DEFAULT 'active',
        `starting_price` decimal(15,2) DEFAULT NULL,
        `current_price` decimal(15,2) DEFAULT NULL,
        `features` text,
        `amenities` text,
        `latitude` decimal(10,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `developer` varchar(255) DEFAULT NULL,
        `image` varchar(255) DEFAULT NULL,
        `brochure` varchar(255) DEFAULT NULL,
        `virtual_tour` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_name` (`name`),
        KEY `idx_location` (`location`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);

    // Create plots table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS `plots` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) NOT NULL,
        `plot_number` varchar(50) NOT NULL,
        `plot_area` decimal(10,2) NOT NULL,
        `price_per_sqyard` decimal(10,2) NOT NULL,
        `total_price` decimal(15,2) NOT NULL,
        `status` enum('available','booked','sold','hold') DEFAULT 'available',
        `facing` varchar(50) DEFAULT NULL,
        `features` text,
        `booking_date` date DEFAULT NULL,
        `customer_id` int(11) DEFAULT NULL,
        `associate_id` int(11) DEFAULT NULL,
        `payment_plan` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_plot` (`colony_id`, `plot_number`),
        KEY `idx_colony_id` (`colony_id`),
        KEY `idx_status` (`status`),
        KEY `idx_plot_number` (`plot_number`),
        FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);

    echo "\n🎉 Database setup completed successfully!\n";
    echo "📊 Summary:\n";
    echo "- Added " . count($colonies_data) . " colonies\n";
    echo "- Added sample plots for active colonies\n";
    echo "- Created necessary tables if not existed\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
