<?php
// Repair Projects Table Structure
require_once 'config.php';

require_once dirname(__DIR__, 2) . '/app/helpers.php';

echo "<h2>Repairing Projects Table Structure</h2>";

try {
    // Check if projects table exists
    $checkTable = $con->query("SHOW TABLES LIKE 'projects'");

    if ($checkTable->num_rows > 0) {
        echo "<p style='color: green;'>✅ Projects table exists</p>";

        // Check table structure
        $columnsResult = $con->query("DESCRIBE projects");
        $columnNames = [];
        while ($row = $columnsResult->fetch_assoc()) {
            $columnNames[] = $row['Field'];
        }

        // Required columns for our system
        $requiredColumns = [
            'id', 'name', 'location', 'type', 'status', 'description',
            'tagline', 'meta_description', 'image_path', 'banner_image',
            'possession_date', 'created_at', 'updated_at'
        ];

        $missingColumns = array_diff($requiredColumns, $columnNames);

        if (empty($missingColumns)) {
            echo "<p style='color: green;'>✅ All required columns exist</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Missing columns: " . implode(', ', $missingColumns) . "</p>";

            // Add missing columns
            foreach ($missingColumns as $column) {
                $alterSql = "";
                switch ($column) {
                    case 'type':
                        $alterSql = "ALTER TABLE projects ADD type VARCHAR(50) DEFAULT 'residential' AFTER location";
                        break;
                    case 'tagline':
                        $alterSql = "ALTER TABLE projects ADD tagline VARCHAR(255) AFTER description";
                        break;
                    case 'meta_description':
                        $alterSql = "ALTER TABLE projects ADD meta_description TEXT AFTER tagline";
                        break;
                    case 'banner_image':
                        $alterSql = "ALTER TABLE projects ADD banner_image VARCHAR(255) AFTER image_path";
                        break;
                    case 'possession_date':
                        $alterSql = "ALTER TABLE projects ADD possession_date DATE AFTER banner_image";
                        break;
                    default:
                        continue 2; // Skip to next column
                }

                $con->query($alterSql);
                echo "<p style='color: green;'>✅ Added column: $column</p>";
            }
        }

        // Check if we have our company projects
        $projectsResult = $con->query("SELECT * FROM projects WHERE name IN ('Suryoday Colony', 'Braj Radha Nagri', 'Raghunath Nagri')");
        $projects = [];
        while ($row = $projectsResult->fetch_assoc()) {
            $projects[] = $row;
        }

        if (count($projects) >= 3) {
            echo "<p style='color: green;'>✅ All company projects exist in database</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Adding company projects to database...</p>";

            // Insert company projects
            $companyProjects = [
                [
                    'name' => 'Suryoday Colony',
                    'location' => 'Gorakhpur',
                    'type' => 'residential',
                    'status' => 'ongoing',
                    'description' => 'Premium residential colony in Gorakhpur with modern amenities',
                    'tagline' => 'Your Dream Home in Gorakhpur',
                    'meta_description' => 'Suryoday Colony offers premium residential plots with modern amenities in Gorakhpur',
                    'image_path' => 'assets/images/projects/suryoday-colony.jpg',
                    'banner_image' => 'assets/images/banners/suryoday-colony-banner.jpg',
                    'possession_date' => '2024-12-31'
                ],
                [
                    'name' => 'Braj Radha Nagri',
                    'location' => 'Gorakhpur',
                    'type' => 'residential',
                    'status' => 'completed',
                    'description' => 'Luxury residential project with spiritual theme',
                    'tagline' => 'Divine Living Experience',
                    'meta_description' => 'Braj Radha Nagri offers luxury residential plots with spiritual theme in Gorakhpur',
                    'image_path' => 'assets/images/projects/braj-radha-nagri.jpg',
                    'banner_image' => 'assets/images/banners/braj-radha-nagri-banner.jpg',
                    'possession_date' => '2023-06-30'
                ],
                [
                    'name' => 'Raghunath Nagri',
                    'location' => 'Gorakhpur',
                    'type' => 'residential',
                    'status' => 'upcoming',
                    'description' => 'New residential project with modern infrastructure',
                    'tagline' => 'Modern Living Spaces',
                    'meta_description' => 'Raghunath Nagri offers modern residential plots with excellent infrastructure in Gorakhpur',
                    'image_path' => 'assets/images/projects/raghunath-nagri.jpg',
                    'banner_image' => 'assets/images/banners/raghunath-nagri-banner.jpg',
                    'possession_date' => '2025-03-31'
                ]
            ];

            foreach ($companyProjects as $project) {
                $sql = "INSERT INTO projects (name, location, type, status, description, tagline, meta_description, image_path, banner_image, possession_date, created_at, updated_at)
                        VALUES ('" . $con->real_escape_string($project['name']) . "', '" . $con->real_escape_string($project['location']) . "', '" . $con->real_escape_string($project['type']) . "', '" . $con->real_escape_string($project['status']) . "', '" . $con->real_escape_string($project['description']) . "', '" . $con->real_escape_string($project['tagline']) . "', '" . $con->real_escape_string($project['meta_description']) . "', '" . $con->real_escape_string($project['image_path']) . "', '" . $con->real_escape_string($project['banner_image']) . "', '" . $con->real_escape_string($project['possession_date']) . "', NOW(), NOW())";
                $con->query($sql);
            }

            echo "<p style='color: green;'>✅ Added company projects to database</p>";
        }

    } else {
        echo "<p style='color: red;'>❌ Projects table does not exist. Please run fix_projects.php first.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Show current projects
$allProjectsResult = $con->query("SELECT id, name, location, type, status FROM projects ORDER BY created_at DESC");
$allProjects = [];
while ($row = $allProjectsResult->fetch_assoc()) {
    $allProjects[] = $row;
}

echo "<h3>Current Projects in Database:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Name</th><th>Location</th><th>Type</th><th>Status</th></tr>";
foreach ($allProjects as $project) {
    echo "<tr>";
    echo "<td>" . h($project['id']) . "</td>";
    echo "<td>" . h($project['name']) . "</td>";
    echo "<td>" . h($project['location']) . "</td>";
    echo "<td>" . h($project['type']) . "</td>";
    echo "<td>" . h($project['status']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Quick Links</h3>";
foreach ($allProjects as $project) {
    echo "<p><a href='project-details.php?id=" . h($project['id']) . "' target='_blank'>View " . h($project['name']) . "</a></p>";
}
?>
