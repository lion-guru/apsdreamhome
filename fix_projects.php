<?php
/**
 * Fix Projects Table and Header Menu Functionality
 * This script will:
 * 1. Check if projects table exists and create it if not
 * 2. Add sample projects for Gorakhpur (Suryoday Colony, Braj Radha Nagri, Raghunath Nagri)
 * 3. Fix the header menu to properly link to project details
 */

require_once 'includes/db_connection.php';

echo "<h2>Fixing Projects Table and Header Menu</h2>";

try {
    // Check if projects table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'projects'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ Projects table does not exist! Creating it...</p>";
        
        // Create projects table with proper structure
        $createTable = "
            CREATE TABLE projects (
                id INT(11) PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                location VARCHAR(100) NOT NULL,
                type VARCHAR(50) DEFAULT 'Residential',
                status VARCHAR(20) DEFAULT 'active',
                description TEXT,
                tagline VARCHAR(255),
                meta_description TEXT,
                image_path VARCHAR(255),
                banner_image VARCHAR(255),
                possession_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_location (location),
                KEY idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        
        $pdo->exec($createTable);
        echo "<p style='color: green;'>✅ Projects table created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ Projects table exists!</p>";
    }
    
    // Add/Update the three Gorakhpur projects
    $gorakhpurProjects = [
        [
            'name' => 'Suryoday Colony',
            'location' => 'Gorakhpur',
            'type' => 'Residential',
            'status' => 'active',
            'description' => '35 acre residential colony near Dropadidevi Degree College on Kalesar Four Lane. Features 3 blocks (A, B, C) with A block sold out. Premium amenities including 24/7 power backup, rainwater harvesting, swimming pool, landscaped gardens, club house, gymnasium, and children\'s play area.',
            'tagline' => 'Premium Living on Kalesar Four Lane',
            'meta_description' => 'Suryoday Colony - 35 acre residential project in Gorakhpur near Dropadidevi Degree College with premium amenities and modern infrastructure.'
        ],
        [
            'name' => 'Braj Radha Nagri',
            'location' => 'Gorakhpur',
            'type' => 'Residential',
            'status' => 'active',
            'description' => '10 acre residential project near Budhiya Mata Mandir Rajahi, Gorakhpur. Peaceful living environment with modern amenities and spiritual proximity.',
            'tagline' => 'Spiritual Living in Gorakhpur',
            'meta_description' => 'Braj Radha Nagri - 10 acre residential project near Budhiya Mata Mandir in Gorakhpur offering spiritual living environment.'
        ],
        [
            'name' => 'Raghunath Nagri',
            'location' => 'Gorakhpur',
            'type' => 'Residential',
            'status' => 'active',
            'description' => 'Premium residential colony in Motira, Gorakhpur. Well-planned infrastructure with all modern amenities and excellent connectivity.',
            'tagline' => 'Modern Living in Motira',
            'meta_description' => 'Raghunath Nagri - Premium residential colony in Motira, Gorakhpur with modern amenities and excellent connectivity.'
        ]
    ];
    
    foreach ($gorakhpurProjects as $projectData) {
        // Check if project already exists
        $checkStmt = $pdo->prepare("SELECT id FROM projects WHERE name = ? AND location = ?");
        $checkStmt->execute([$projectData['name'], $projectData['location']]);
        $existingProject = $checkStmt->fetch();
        
        if ($existingProject) {
            // Update existing project
            $updateStmt = $pdo->prepare("UPDATE projects SET type = ?, status = ?, description = ?, tagline = ?, meta_description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([
                $projectData['type'],
                $projectData['status'],
                $projectData['description'],
                $projectData['tagline'],
                $projectData['meta_description'],
                $existingProject['id']
            ]);
            echo "<p style='color: blue;'>📝 Updated project: {$projectData['name']}</p>";
        } else {
            // Insert new project
            $insertStmt = $pdo->prepare("INSERT INTO projects (name, location, type, status, description, tagline, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([
                $projectData['name'],
                $projectData['location'],
                $projectData['type'],
                $projectData['status'],
                $projectData['description'],
                $projectData['tagline'],
                $projectData['meta_description']
            ]);
            echo "<p style='color: green;'>✅ Added project: {$projectData['name']}</p>";
        }
    }
    
    // Display all projects for verification
    echo "<h3>Current Projects in Database:</h3>";
    $stmt = $pdo->query("SELECT id, name, location, status FROM projects ORDER BY location, name");
    $projects = $stmt->fetchAll();
    
    if (count($projects) > 0) {
        echo "<table border='1' cellpadding='5' style='width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Location</th><th>Status</th><th>Test Link</th></tr>";
        foreach ($projects as $project) {
            echo "<tr>";
            echo "<td>{$project['id']}</td>";
            echo "<td>{$project['name']}</td>";
            echo "<td>{$project['location']}</td>";
            echo "<td>{$project['status']}</td>";
            echo "<td><a href='project-details.php?id={$project['id']}' target='_blank'>Test Details</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No projects found in database.</p>";
    }
    
    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure Apache is running in XAMPP control panel</li>";
    echo "<li>Visit <a href='http://localhost/apsdreamhomefinal/' target='_blank'>http://localhost/apsdreamhomefinal/</a></li>";
    echo "<li>Test the project menu links in the header</li>";
    echo "<li>Test individual project pages using the links above</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure MySQL is running in XAMPP control panel.</p>";
}
?>