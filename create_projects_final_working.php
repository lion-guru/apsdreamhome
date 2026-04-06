<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 Phase 5: Projects Table\n";
    
    // Disable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop any existing projects table
    $db->exec("DROP TABLE IF EXISTS projects");
    
    // Simple projects table
    $createTable = "CREATE TABLE projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        project_type VARCHAR(50) DEFAULT 'residential',
        developer_name VARCHAR(150),
        developer_contact VARCHAR(100),
        colony_name VARCHAR(200),
        district_name VARCHAR(100),
        state_name VARCHAR(100),
        total_plots INT DEFAULT 0,
        available_plots INT DEFAULT 0,
        sold_plots INT DEFAULT 0,
        price_range_min DECIMAL(12,2),
        price_range_max DECIMAL(12,2),
        status VARCHAR(50) DEFAULT 'planning',
        launch_date DATE,
        completion_date DATE,
        amenities TEXT,
        brochure_path VARCHAR(500),
        images TEXT,
        is_featured TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->exec($createTable);
    echo "✅ Projects table created\n";
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Sample data
    $projects = [
        ['Suryoday Heights Phase 1', 'Premium residential project in Suryoday Colony with modern amenities', 'residential', 'APS Developers', '+91-9876543210', 'Suryoday Colony', 'Gorakhpur', 'Uttar Pradesh', 50, 45, 5, 2000000, 8000000, 'under_construction', '2024-01-15', '2025-12-31', '24/7 Security, Club House, Swimming Pool, Gym, Park', '/uploads/brochure.pdf', 'suryoday_1.jpg,suryoday_2.jpg', 1],
        ['Braj Radha Enclave', 'Spiritual residential project near Budhya Mata Mandir in Deoria', 'residential', 'Braj Properties', '+91-9876543220', 'Braj Radha Nagri', 'Deoria', 'Uttar Pradesh', 40, 35, 5, 1200000, 4500000, 'under_construction', '2024-02-01', '2025-09-30', '24/7 Security, Temple View, Meditation Center, Community Hall', '/uploads/brochure2.pdf', 'braj_1.jpg,braj_2.jpg', 0],
        ['Raghunath City Center', 'Mixed-use commercial and residential project in Raghunath Nagri', 'mixed', 'Raghunath Developers', '+91-9876543330', 'Raghunath Nagri', 'Gorakhpur', 'Uttar Pradesh', 60, 25, 20, 1800000, 6000000, 'planning', '2024-03-01', '2026-06-30', '24/7 Security, Commercial Complex, Shopping Area, Residential Blocks', '/uploads/brochure3.pdf', 'raghunath_1.jpg,raghunath_2.jpg,raghunath_3.jpg', 1]
    ];
    
    foreach ($projects as $project) {
        $stmt = $db->prepare("INSERT INTO projects (name, description, project_type, developer_name, developer_contact, colony_name, district_name, state_name, total_plots, available_plots, sold_plots, price_range_min, price_range_max, status, launch_date, completion_date, amenities, brochure_path, images, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($project);
    }
    
    echo "✅ " . count($projects) . " projects inserted\n";
    
    echo "\n🎉 Phase 5: Projects Table Complete!\n";
    echo "✅ Projects table: Created with 20+ fields\n";
    echo "✅ Sample data: 3 projects\n";
    echo "✅ Text support: Amenities & images\n";
    echo "✅ Status tracking: Planning to completed\n";
    echo "✅ Featured projects: Marketing ready\n";
    echo "📈 Ready for Admin CRUD!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
