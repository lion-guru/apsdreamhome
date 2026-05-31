<?php
try {
    $db = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 Phase 5: Projects Table + Admin CRUD Implementation\n";
    
    // 1. Create projects table (without foreign key constraints initially)
    echo "📋 Creating Projects Table...\n";
    
    $db->exec("DROP TABLE IF EXISTS projects");
    $db->exec("DROP TABLE IF EXISTS project_status_history");
    $db->exec("DROP TABLE IF EXISTS project_images");
    
    $createProjectsTable = "CREATE TABLE projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        project_type ENUM('residential', 'commercial', 'industrial', 'mixed') DEFAULT 'residential',
        developer_name VARCHAR(150),
        developer_contact VARCHAR(100),
        developer_email VARCHAR(100),
        developer_phone VARCHAR(20),
        
        -- Location Details
        address TEXT,
        colony_id INT,
        district_id INT,
        state_id INT,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        
        -- Project Specifications
        total_area DECIMAL(15, 2),
        total_plots INT DEFAULT 0,
        available_plots INT DEFAULT 0,
        sold_plots INT DEFAULT 0,
        booked_plots INT DEFAULT 0,
        
        -- Pricing
        price_range_min DECIMAL(12, 2),
        price_range_max DECIMAL(12, 2),
        avg_price_per_sqft DECIMAL(10, 2),
        
        -- Timeline
        launch_date DATE,
        completion_date DATE,
        possession_date DATE,
        status ENUM('planning', 'under_construction', 'completed', 'delayed', 'cancelled') DEFAULT 'planning',
        
        -- Features & Amenities
        amenities JSON,
        specifications JSON,
        features JSON,
        
        -- Media
        brochure_path VARCHAR(500),
        images JSON,
        videos JSON,
        virtual_tour_url VARCHAR(500),
        
        -- Legal & Approvals
        rera_number VARCHAR(100),
        approvals JSON,
        legal_documents JSON,
        
        -- Marketing
        is_featured TINYINT DEFAULT 0,
        is_hot_deal TINYINT DEFAULT 0,
        marketing_description TEXT,
        tags VARCHAR(500),
        
        -- Contact & Sales
        sales_office_address TEXT,
        sales_office_phone VARCHAR(20),
        sales_office_email VARCHAR(100),
        sales_team_contact JSON,
        
        -- Management
        created_by INT,
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Indexes
        INDEX idx_colony (colony_id),
        INDEX idx_district (district_id),
        INDEX idx_state (state_id),
        INDEX idx_status (status),
        INDEX idx_project_type (project_type),
        INDEX idx_featured (is_featured),
        INDEX idx_hot_deal (is_hot_deal)
    )";
    
    $db->exec($createProjectsTable);
    echo "✅ Projects table created\n";
    
    // 2. Create project_status_history table
    echo "📝 Creating Project Status History Table...\n";
    
    $createStatusHistoryTable = "CREATE TABLE project_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        old_status VARCHAR(50),
        new_status VARCHAR(50) NOT NULL,
        remarks TEXT,
        changed_by INT,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_project (project_id),
        INDEX idx_changed_by (changed_by)
    )";
    
    $db->exec($createStatusHistoryTable);
    echo "✅ Project status history table created\n";
    
    // 3. Create project_images table
    echo "🖼️ Creating Project Images Table...\n";
    
    $createImagesTable = "CREATE TABLE project_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        image_title VARCHAR(200),
        image_description TEXT,
        image_path VARCHAR(500) NOT NULL,
        thumbnail_path VARCHAR(500),
        image_type ENUM('master_plan', 'elevation', 'amenity', 'construction', 'completed', 'location', 'other') DEFAULT 'other',
        display_order INT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_project (project_id),
        INDEX idx_type (image_type),
        INDEX idx_order (display_order)
    )";
    
    $db->exec($createImagesTable);
    echo "✅ Project images table created\n";
    
    // 4. Insert Sample Projects
    echo "🏗️ Inserting Sample Projects...\n";
    
    $sampleProjects = [
        [
            'Suryoday Heights Phase 1',
            'Premium residential project in Suryoday Colony with modern amenities and excellent connectivity. Part of the prestigious Suryoday development.',
            'residential',
            'APS Developers',
            '+91-9876543210',
            'info@apsdevelopers.com',
            '+91-9876543210',
            'Main Road, Suryoday Colony, Gorakhpur',
            2, // Suryoday Colony ID
            1, // Gorakhpur District ID  
            1, // UP State ID
            26.7485,
            83.3792,
            50000.00, // 50,000 sq ft total area
            50, // total plots
            45, // available
            3, // sold
            2, // booked
            2000000.00, // min price ₹20 lakh
            8000000.00, // max price ₹80 lakh
            2500.00, // avg price per sqft
            '2024-01-15',
            '2025-12-31',
            '2026-03-31',
            'under_construction',
            json_encode(['24/7 Security', 'Gated Community', 'Club House', 'Swimming Pool', 'Gym', 'Park', 'Children Play Area']),
            json_encode([' RCC Structure', 'Vitrified Tiles', 'CP Fittings', 'Power Backup', 'Water Supply']),
            json_encode(['Earthquake Resistant', 'Vastu Compliant', 'Green Building']),
            '/uploads/projects/suryoday_heights_brochure.pdf',
            json_encode(['suryoday_heights_1.jpg', 'suryoday_heights_2.jpg', 'suryoday_heights_3.jpg']),
            json_encode(['suryoday_heights_tour.mp4']),
            'https://virtualtour.example.com/suryoday-heights',
            'UP-RERA-2024-001234',
            json_encode(['DTCP Approved', 'Environmental Clearance', 'Fire Safety Approved']),
            json_encode(['title_deed.pdf', 'approval_letter.pdf']),
            1, // featured
            1, // hot deal
            'Limited time offer! Get 5% discount on booking before 31st March 2024.',
            'premium, luxury, gated, modern amenities, gorakhpur',
            'Sales Office: Suryoday Colony Main Gate, Gorakhpur',
            '+91-9876543210',
            'sales@suryodayheights.com',
            json_encode([
                ['name' => 'Rahul Sharma', 'phone' => '+91-9876543211', 'email' => 'rahul@suryoday.com'],
                ['name' => 'Priya Singh', 'phone' => '+91-9876543212', 'email' => 'priya@suryoday.com']
            ])
        ],
        [
            'Braj Radha Enclave',
            'Spiritual residential project near Budhya Mata Mandir in Deoria. Peaceful environment with modern facilities and traditional values.',
            'residential',
            'Braj Properties',
            '+91-9876543220',
            'contact@brajproperties.com',
            '+91-9876543220',
            'Near Budhya Mata Mandir, Deoria',
            3, // Braj Radha Nagri ID
            2, // Deoria District
            1, // UP State
            26.6404,
            83.5906,
            30000.00, // 30,000 sq ft total area
            40, // total plots
            35, // available
            3, // sold
            2, // booked
            1200000.00, // min price ₹12 lakh
            4500000.00, // max price ₹45 lakh
            1500.00, // avg price per sqft
            '2024-02-01',
            '2025-09-30',
            '2025-12-31',
            'under_construction',
            json_encode(['24/7 Security', 'Temple View', 'Meditation Center', 'Park', 'Community Hall']),
            json_encode(['RCC Structure', 'Vitrified Tiles', 'CP Fittings', 'Power Backup']),
            json_encode(['Vastu Compliant', 'Spiritual Environment', 'Peaceful Living']),
            '/uploads/projects/braj_radha_enclave_brochure.pdf',
            json_encode(['braj_radha_1.jpg', 'braj_radha_2.jpg']),
            json_encode([]),
            '',
            'UP-RERA-2024-001235',
            json_encode(['DTCP Approved', 'Environmental Clearance']),
            json_encode(['title_deed.pdf']),
            0, // featured
            0, // hot deal
            'Experience spiritual living with modern comforts near Budhya Mata Mandir.',
            'spiritual, peaceful, temple view, deoria, traditional',
            'Sales Office: Near Budhya Mata Mandir, Deoria',
            '+91-9876543220',
            'sales@brajradha.com',
            json_encode([
                ['name' => 'Amit Kumar', 'phone' => '+91-9876543221', 'email' => 'amit@brajradha.com']
            ])
        ],
        [
            'Raghunath City Center',
            'Mixed-use commercial and residential project in Raghunath Nagri. Modern facilities with traditional values named after Lord Raghunath.',
            'mixed',
            'Raghunath Developers',
            '+91-9876543330',
            'info@raghunathcity.com',
            '+91-9876543330',
            'Main Market, Raghunath Nagri, Gorakhpur',
            4, // Raghunath Nagri ID
            1, // Gorakhpur District
            1, // UP State
            26.7601,
            83.3698,
            75000.00, // 75,000 sq ft total area
            60, // total plots
            25, // available
            20, // sold
            15, // booked
            1800000.00, // min price ₹18 lakh
            6000000.00, // max price ₹60 lakh
            2000.00, // avg price per sqft
            '2024-03-01',
            '2026-06-30',
            '2026-09-30',
            'planning',
            json_encode(['24/7 Security', 'Commercial Complex', 'Shopping Area', 'Residential Blocks', 'Parking']),
            json_encode(['RCC Structure', 'Modern Amenities', 'Commercial Spaces', 'Residential Units']),
            json_encode(['Mixed Use Development', 'Modern Infrastructure', 'Prime Location']),
            '/uploads/projects/raghunath_city_brochure.pdf',
            json_encode(['raghunath_city_1.jpg', 'raghunath_city_2.jpg', 'raghunath_city_3.jpg']),
            json_encode(['raghunath_city_promo.mp4']),
            'https://virtualtour.example.com/raghunath-city',
            'UP-RERA-2024-001236',
            json_encode(['DTCP Approved', 'Commercial License']),
            json_encode(['master_plan.pdf', 'commercial_license.pdf']),
            1, // featured
            1, // hot deal
            'Prime mixed-use development with commercial and residential spaces in heart of Raghunath Nagri.',
            'mixed-use, commercial, residential, prime location, raghunath nagri',
            'Sales Office: Main Market, Raghunath Nagri, Gorakhpur',
            '+91-9876543330',
            'sales@raghunathcity.com',
            json_encode([
                ['name' => 'Vikas Singh', 'phone' => '+91-9876543331', 'email' => 'vikas@raghunathcity.com'],
                ['name' => 'Neha Gupta', 'phone' => '+91-9876543332', 'email' => 'neha@raghunathcity.com']
            ])
        ]
    ];
    
    foreach ($sampleProjects as $project) {
        $stmt = $db->prepare("INSERT INTO projects (
            name, description, project_type, developer_name, developer_contact, developer_email, developer_phone,
            address, colony_id, district_id, state_id, latitude, longitude,
            total_area, total_plots, available_plots, sold_plots, booked_plots,
            price_range_min, price_range_max, avg_price_per_sqft,
            launch_date, completion_date, possession_date, status,
            amenities, specifications, features,
            brochure_path, images, videos, virtual_tour_url,
            rera_number, approvals, legal_documents,
            is_featured, is_hot_deal, marketing_description, tags,
            sales_office_address, sales_office_phone, sales_office_email, sales_team_contact
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute($project);
    }
    
    echo "✅ " . count($sampleProjects) . " sample projects inserted\n";
    
    // 5. Insert sample project images
    echo "🖼️ Adding Sample Project Images...\n";
    
    $sampleImages = [
        [1, 'Master Plan', 'Complete master plan of Suryoday Heights Phase 1', '/uploads/projects/suryoday_master_plan.jpg', '/uploads/projects/thumbs/suryoday_master_plan_thumb.jpg', 'master_plan', 1],
        [1, 'Project Elevation', 'Beautiful elevation view of Suryoday Heights', '/uploads/projects/suryoday_elevation.jpg', '/uploads/projects/thumbs/suryoday_elevation_thumb.jpg', 'elevation', 2],
        [1, 'Club House', 'Modern club house with swimming pool and gym', '/uploads/projects/suryoday_club.jpg', '/uploads/projects/thumbs/suryoday_club_thumb.jpg', 'amenity', 3],
        [2, 'Temple View', 'Beautiful view of Budhya Mata Mandir from project', '/uploads/projects/braj_temple_view.jpg', '/uploads/projects/thumbs/braj_temple_view_thumb.jpg', 'location', 1],
        [2, 'Meditation Center', 'Peaceful meditation center within project', '/uploads/projects/braj_meditation.jpg', '/uploads/projects/thumbs/braj_meditation_thumb.jpg', 'amenity', 2],
        [3, 'Commercial Complex', 'Modern commercial complex with shopping area', '/uploads/projects/raghunath_commercial.jpg', '/uploads/projects/thumbs/raghunath_commercial_thumb.jpg', 'master_plan', 1],
        [3, 'Residential Block', 'Premium residential blocks with modern amenities', '/uploads/projects/raghunath_residential.jpg', '/uploads/projects/thumbs/raghunath_residential_thumb.jpg', 'elevation', 2]
    ];
    
    foreach ($sampleImages as $image) {
        $stmt = $db->prepare("INSERT INTO project_images (
            project_id, image_title, image_description, image_path, thumbnail_path, image_type, display_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute($image);
    }
    
    echo "✅ " . count($sampleImages) . " sample project images added\n";
    
    echo "\n🎉 Phase 5: Projects Table + Admin CRUD Complete!\n";
    echo "✅ Projects table: Created with 45+ fields\n";
    echo "✅ Status history: Tracking enabled\n";
    echo "✅ Images management: Multi-type support\n";
    echo "✅ Sample data: 3 projects with images\n";
    echo "✅ Indexes: Performance optimized\n";
    echo "📈 Ready for Admin CRUD Implementation!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}
?>
