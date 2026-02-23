<?php
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/app/Core/autoload.php';

use App\Models\Project;
use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Checking 'projects' table schema...\n";
    $stmt = $db->query("DESCRIBE projects");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Check for 'slug' column
    if (!in_array('slug', $columns)) {
        echo "Adding 'slug' column to 'projects' table...\n";
        $db->exec("ALTER TABLE projects ADD COLUMN slug VARCHAR(255) UNIQUE AFTER name");
        echo "Added 'slug' column.\n";
    }

    $projectsToSeed = [
        [
            'name' => 'Raghunath Nagri',
            'slug' => 'gorakhpur-raghunath-nagri',
            'project_code' => 'RN001',
            'location' => 'Gorakhpur',
            'description' => 'Premium Township at Motiram to Jhangha Road, Gorakhpur. Spread across more than 15 acres.',
            'image' => 'assets/images/projects/raghunath-nagri.jpg',
            'status' => 'active'
        ],
        [
            'name' => 'Suryoday Colony',
            'slug' => 'gorakhpur-suryoday-colony',
            'project_code' => 'SC001',
            'location' => 'Gorakhpur',
            'description' => 'Gorakhpur\'s Finest Residential Community. Located in the heart of Gorakhpur.',
            'image' => 'assets/images/site_photo/gorakhpur/suryoday/suryoday.png',
            'status' => 'active'
        ],
        [
            'name' => 'Ganga Nagri',
            'slug' => 'varanasi-ganga-nagri',
            'project_code' => 'GN001',
            'location' => 'Varanasi',
            'description' => 'Divine Living Near the Holy Ganges. Located in a serene environment.',
            'image' => 'assets/images/projects/ganga-nagri.jpg',
            'status' => 'active'
        ]
    ];

    foreach ($projectsToSeed as $data) {
        // Check if project exists by slug
        $stmt = $db->prepare("SELECT count(*) FROM projects WHERE slug = ?");
        $stmt->execute([$data['slug']]);
        if ($stmt->fetchColumn() == 0) {
            // Check if it exists by name (to update slug)
            $stmt = $db->prepare("SELECT id FROM projects WHERE name = ?");
            $stmt->execute([$data['name']]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                echo "Updating slug for existing project: {$data['name']}\n";
                $update = $db->prepare("UPDATE projects SET slug = ? WHERE id = ?");
                $update->execute([$data['slug'], $existingId]);
            } else {
                echo "Seeding project: {$data['name']}\n";
                // Insert
                // We must include project_code to satisfy unique constraint if it exists
                $sql = "INSERT INTO projects (name, slug, project_code, location, description, image, status) VALUES (:name, :slug, :project_code, :location, :description, :image, :status)";
                
                $insert = $db->prepare($sql);
                $insert->execute([
                    ':name' => $data['name'],
                    ':slug' => $data['slug'],
                    ':project_code' => $data['project_code'],
                    ':location' => $data['location'],
                    ':description' => $data['description'],
                    ':image' => $data['image'],
                    ':status' => $data['status']
                ]);
            }
        } else {
            echo "Project already exists: {$data['name']}\n";
        }
    }

    echo "Database check complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
