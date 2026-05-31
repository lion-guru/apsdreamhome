<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 Phase 5: Projects Table\n";
    
    $db->exec("DROP TABLE IF EXISTS projects");
    
    $createTable = "CREATE TABLE projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        project_type ENUM('residential','commercial','industrial','mixed') DEFAULT 'residential',
        developer_name VARCHAR(150),
        developer_contact VARCHAR(100),
        colony_id INT,
        district_id INT,
        state_id INT,
        total_plots INT DEFAULT 0,
        available_plots INT DEFAULT 0,
        sold_plots INT DEFAULT 0,
        price_range_min DECIMAL(12,2),
        price_range_max DECIMAL(12,2),
        status ENUM('planning','under_construction','completed','delayed','cancelled') DEFAULT 'planning',
        launch_date DATE,
        completion_date DATE,
        amenities JSON,
        brochure_path VARCHAR(500),
        images JSON,
        is_featured TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $db->exec($createTable);
    echo "✅ Projects table created\n";
    
    // Add foreign keys
    $db->exec("ALTER TABLE projects ADD INDEX idx_colony (colony_id)");
    $db->exec("ALTER TABLE projects ADD INDEX idx_district (district_id)");
    $db->exec("ALTER TABLE projects ADD INDEX idx_state (state_id)");
    
    echo "✅ Indexes added\n";
    
    // Sample data
    $projects = [
        ['Suryoday Heights Phase 1', 'Premium residential project', 'residential', 'APS Developers', 2, 1, 1, 50, 45, 5, 2000000, 8000000, 'under_construction', '2024-01-15', '2025-12-31', json_encode(['Security','Club','Pool']), '/uploads/brochure.pdf', json_encode(['image1.jpg','image2.jpg']), 1],
        ['Braj Radha Enclave', 'Spiritual residential project', 'residential', 'Braj Properties', 3, 2, 1, 40, 35, 5, 1200000, 4500000, 'under_construction', '2024-02-01', '2025-09-30', json_encode(['Security','Temple View']), '/uploads/brochure2.pdf', json_encode(['image3.jpg']), 0],
        ['Raghunath City Center', 'Mixed-use development', 'mixed', 'Raghunath Developers', 4, 1, 1, 60, 25, 20, 1800000, 6000000, 'planning', '2024-03-01', '2026-06-30', json_encode(['Security','Commercial']), '/uploads/brochure3.pdf', json_encode(['image4.jpg','image5.jpg']), 1]
    ];
    
    foreach ($projects as $project) {
        $stmt = $db->prepare("INSERT INTO projects (name, description, project_type, developer_name, colony_id, district_id, state_id, total_plots, available_plots, sold_plots, price_range_min, price_range_max, status, launch_date, completion_date, amenities, brochure_path, images, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($project);
    }
    
    echo "✅ " . count($projects) . " projects inserted\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
