<?php
require_once __DIR__ . '/app/Core/autoload.php';
use App\Core\Database;

echo "Setting up 'downloads' table...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS downloads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100) DEFAULT 'General',
        file_path VARCHAR(255) NOT NULL,
        file_size VARCHAR(50),
        priority INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'downloads' created or already exists.\n";

    // Check if data exists
    $stmt = $db->query("SELECT COUNT(*) FROM downloads");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        echo "Seeding downloads data...\n";
        
        $downloads = [
            [
                'title' => 'APS Dream Homes Brochure',
                'description' => 'Comprehensive guide to our projects and services.',
                'category' => 'Brochures',
                'file_path' => 'assets/downloads/aps-brochure-2025.pdf',
                'file_size' => '5.2 MB',
                'priority' => 10,
                'status' => 'active'
            ],
            [
                'title' => 'Project Price List - Gorakhpur',
                'description' => 'Latest pricing for Raghunath Nagri and Suryoday Colony.',
                'category' => 'Price Lists',
                'file_path' => 'assets/downloads/price-list-gorakhpur.pdf',
                'file_size' => '1.5 MB',
                'priority' => 8,
                'status' => 'active'
            ],
            [
                'title' => 'Booking Application Form',
                'description' => 'Application form for booking plots and flats.',
                'category' => 'Forms',
                'file_path' => 'assets/downloads/booking-form.pdf',
                'file_size' => '0.8 MB',
                'priority' => 9,
                'status' => 'active'
            ],
            [
                'title' => 'Site Map - Raghunath Nagri',
                'description' => 'Detailed layout plan of Raghunath Nagri township.',
                'category' => 'Site Maps',
                'file_path' => 'assets/downloads/sitemap-raghunath-nagri.pdf',
                'file_size' => '3.1 MB',
                'priority' => 7,
                'status' => 'active'
            ]
        ];

        $insertSql = "INSERT INTO downloads (title, description, category, file_path, file_size, priority, status) VALUES (:title, :description, :category, :file_path, :file_size, :priority, :status)";
        $stmt = $db->prepare($insertSql);

        foreach ($downloads as $download) {
            $stmt->execute($download);
            echo "Inserted: {$download['title']}\n";
        }
        
        echo "Seeding complete.\n";
    } else {
        echo "Table already has data. Skipping seed.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
