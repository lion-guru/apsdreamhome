<?php
// Check projects table structure and data
require_once 'includes/db_connection.php';

echo "<h2>Projects Table Structure Check</h2>";

try {
    // Check if projects table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'projects'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ Projects table does not exist!</p>";
        echo "<p>Creating projects table...</p>";
        
        // Create projects table
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
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($createTable);
        echo "<p style='color: green;'>✅ Projects table created successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ Projects table exists!</p>";
    }
    
    // Check table structure
    echo "<h3>Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE projects");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "<td>{$column['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check existing projects
    echo "<h3>Existing Projects:</h3>";
    $stmt = $pdo->query("SELECT * FROM projects");
    $projects = $stmt->fetchAll();
    
    if (count($projects) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        foreach ($projects[0] as $key => $value) {
            echo "<th>{$key}</th>";
        }
        echo "</tr>";
        
        foreach ($projects as $project) {
            echo "<tr>";
            foreach ($project as $value) {
                echo "<td>".htmlspecialchars($value ?? '')."</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No projects found in the database.</p>";
        echo "<p>Adding sample projects...</p>";
        
        // Add sample projects
        $sampleProjects = [
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
        
        foreach ($sampleProjects as $project) {
            $stmt = $pdo->prepare("INSERT INTO projects (name, location, type, status, description, tagline, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $project['name'],
                $project['location'],
                $project['type'],
                $project['status'],
                $project['description'],
                $project['tagline'],
                $project['meta_description']
            ]);
        }
        
        echo "<p style='color: green;'>✅ Sample projects added successfully!</p>";
        echo "<p><a href='project-details.php?id=1'>Test Project 1</a></p>";
        echo "<p><a href='project-details.php?id=2'>Test Project 2</a></p>";
        echo "<p><a href='project-details.php?id=3'>Test Project 3</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// Test header menu functionality
echo "<h3>Header Menu Test:</h3>";
echo "<p><a href='project-details.php?id=3'>Test project-details.php?id=3</a></p>";
echo "<p><a href='projects.php?location=Gorakhpur'>Test projects.php?location=Gorakhpur</a></p>";
?>