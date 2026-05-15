<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop existing tables
    $db->exec("DROP TABLE IF EXISTS plot_images");
    $db->exec("DROP TABLE IF EXISTS plot_status_history");
    $db->exec("DROP TABLE IF EXISTS plots");
    
    // Create plots table
    $db->exec("CREATE TABLE plots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        colony_id INT NOT NULL,
        plot_number VARCHAR(50) NOT NULL,
        block VARCHAR(20),
        sector VARCHAR(20),
        plot_type ENUM('residential', 'commercial', 'industrial', 'mixed') DEFAULT 'residential',
        area_sqft DECIMAL(10,2) DEFAULT 0,
        area_sqm DECIMAL(10,2) DEFAULT 0,
        frontage_ft DECIMAL(6,2) DEFAULT 0,
        depth_ft DECIMAL(6,2) DEFAULT 0,
        price_per_sqft DECIMAL(10,2) DEFAULT 0,
        total_price DECIMAL(12,2) DEFAULT 0,
        status ENUM('available', 'booked', 'sold', 'hold', 'reserved', 'under_construction') DEFAULT 'available',
        booking_amount DECIMAL(12,2) DEFAULT 0,
        total_paid DECIMAL(12,2) DEFAULT 0,
        payment_status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
        customer_id INT DEFAULT NULL,
        booking_date DATE,
        sale_date DATE,
        possession_date DATE,
        description TEXT,
        features TEXT,
        facing VARCHAR(20),
        corner_plot TINYINT DEFAULT 0,
        park_facing TINYINT DEFAULT 0,
        road_width_ft DECIMAL(6,2) DEFAULT 0,
        latitude DECIMAL(10,8),
        longitude DECIMAL(11,8),
        image_path VARCHAR(500),
        documents_path VARCHAR(500),
        is_featured TINYINT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (colony_id) REFERENCES colonies(id)
    )");
    
    // Create status history table
    $db->exec("CREATE TABLE plot_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plot_id INT NOT NULL,
        old_status VARCHAR(20),
        new_status VARCHAR(20) NOT NULL,
        changed_by INT,
        change_reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (plot_id) REFERENCES plots(id)
    )");
    
    // Create plot images table
    $db->exec("CREATE TABLE plot_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plot_id INT NOT NULL,
        image_path VARCHAR(500) NOT NULL,
        image_type ENUM('main', 'gallery', 'document', 'map') DEFAULT 'gallery',
        caption VARCHAR(200),
        sort_order INT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (plot_id) REFERENCES plots(id)
    )");
    
    // Get colonies and insert sample plots
    $colonies = $db->query("SELECT id, name FROM colonies WHERE is_active = 1 LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colonies as $colony) {
        for ($i = 1; $i <= 10; $i++) {
            $area = rand(1000, 4000);
            $price = rand(2000, 6000);
            $total = $area * $price;
            $status = ['available', 'booked', 'sold'][array_rand(['available', 'booked', 'sold'])];
            
            $db->exec("INSERT INTO plots (colony_id, plot_number, block, sector, plot_type, area_sqft, area_sqm, frontage_ft, depth_ft, price_per_sqft, total_price, status, description, features, facing, corner_plot, park_facing, road_width_ft, image_path, is_featured, is_active) VALUES (
                {$colony['id']}, 
                'P" . str_pad($i, 3, '0', STR_PAD_LEFT) . "', 
                '" . chr(65 + ($i % 4)) . "',
                'Sector " . chr(65 + ($i % 3)) . "',
                'residential',
                $area,
                " . round($area * 0.092903, 2) . ",
                " . rand(25, 50) . ",
                " . rand(35, 70) . ",
                $price,
                $total,
                '$status',
                'Premium plot in " . addslashes($colony['name']) . "',
                'Park Facing, Wide Road, Gated Community',
                'north',
                " . rand(0, 1) . ",
                " . rand(0, 1) . ",
                " . rand(30, 60) . ",
                'assets/images/plots/plot_" . $colony['id'] . "_" . $i . ".jpg',
                " . rand(0, 1) . ",
                1
            )");
        }
    }
    
    echo "✅ Plots management setup complete! " . (count($colonies) * 10) . " sample plots created.";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
