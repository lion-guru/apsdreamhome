<?php
/**
 * Script to verify properties table structure and geolocation columns
 */

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "✅ Database connection successful!\n\n";

    // Get table structure
    $stmt = $pdo->prepare("DESCRIBE properties");
    $stmt->execute();
    $columns = $stmt->fetchAll();

    echo "Properties table structure:\n";
    echo str_repeat("-", 60) . "\n";
    printf("%-20s %-15s %-8s %-10s %s\n", "Field", "Type", "Null", "Key", "Default");
    echo str_repeat("-", 60) . "\n";

    $hasLatitude = false;
    $hasLongitude = false;

    foreach ($columns as $column) {
        printf("%-20s %-15s %-8s %-10s %s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key'] ?? '',
            $column['Default'] ?? ''
        );

        if ($column['Field'] === 'latitude') $hasLatitude = true;
        if ($column['Field'] === 'longitude') $hasLongitude = true;
    }

    echo "\n" . str_repeat("-", 60) . "\n";
    echo "Geolocation columns verification:\n";
    echo "Latitude column: " . ($hasLatitude ? "✅ EXISTS" : "❌ MISSING") . "\n";
    echo "Longitude column: " . ($hasLongitude ? "✅ EXISTS" : "❌ MISSING") . "\n";

    if ($hasLatitude && $hasLongitude) {
        echo "\n🎉 Properties table successfully updated with geolocation support!\n";

        // Test inserting sample data
        echo "\nTesting geolocation data insertion...\n";

        // Insert sample property with coordinates (Delhi coordinates as example)
        $sampleData = [
            'title' => 'Sample Property with Location',
            'description' => 'Test property for geolocation',
            'price' => 5000000,
            'location' => 'Connaught Place, New Delhi',
            'latitude' => 28.613939,
            'longitude' => 77.209021,
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'status' => 'available',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if sample property already exists
        $checkStmt = $pdo->prepare("SELECT id FROM properties WHERE title = ?");
        $checkStmt->execute([$sampleData['title']]);
        $existing = $checkStmt->fetch();

        if (!$existing) {
            // Insert sample property
            $columns = implode(', ', array_keys($sampleData));
            $placeholders = implode(', ', array_fill(0, count($sampleData), '?'));
            $insertSql = "INSERT INTO properties ($columns) VALUES ($placeholders)";

            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute(array_values($sampleData));

            echo "✅ Sample property with geolocation data inserted successfully!\n";
        } else {
            echo "ℹ️ Sample property already exists\n";
        }

        // Test retrieving geolocation data
        $selectStmt = $pdo->prepare("SELECT id, title, latitude, longitude, location FROM properties WHERE latitude IS NOT NULL AND longitude IS NOT NULL LIMIT 5");
        $selectStmt->execute();
        $properties = $selectStmt->fetchAll();

        echo "\nProperties with geolocation data:\n";
        foreach ($properties as $property) {
            echo "• {$property['title']} - Lat: {$property['latitude']}, Lng: {$property['longitude']} ({$property['location']})\n";
        }

    } else {
        echo "\n❌ Geolocation columns are missing. Please run the update script again.\n";
    }

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
