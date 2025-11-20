<?php
/**
 * Demo Property Data Generator
 * This script adds sample properties to your APS Dream Home database
 */

require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    // Sample properties data
    $properties = [
        [
            'title' => 'Luxury 3BHK Apartment in City Center',
            'description' => 'Beautiful 3BHK apartment with modern amenities, located in the heart of the city. Perfect for families looking for comfort and convenience.',
            'price' => 4500000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area_sqft' => 1500,
            'location' => 'City Center, Gorakhpur',
            'address' => '123 Main Street, City Center, Gorakhpur, UP 273001',
            'features' => 'Parking, Security, Gym, Swimming Pool, Garden',
            'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Spacious 2BHK Flat with Balcony',
            'description' => 'Well-maintained 2BHK flat with spacious balcony offering city views. Ideal for young professionals or small families.',
            'price' => 2800000,
            'type' => 'flat',
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area_sqft' => 1200,
            'location' => 'Rajendra Nagar, Gorakhpur',
            'address' => '456 Park Avenue, Rajendra Nagar, Gorakhpur, UP 273004',
            'features' => 'Balcony, Parking, 24/7 Water, Security',
            'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Modern Villa with Garden',
            'description' => 'Stunning modern villa with private garden and parking space. Perfect for those seeking luxury and privacy.',
            'price' => 8500000,
            'type' => 'villa',
            'status' => 'available',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'area_sqft' => 2500,
            'location' => 'Civil Lines, Gorakhpur',
            'address' => '789 Luxury Lane, Civil Lines, Gorakhpur, UP 273001',
            'features' => 'Garden, Parking, Security, Modern Kitchen, Large Windows',
            'image_url' => 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Affordable 1BHK Studio Apartment',
            'description' => 'Cozy studio apartment perfect for singles or students. Fully furnished with modern amenities.',
            'price' => 1200000,
            'type' => 'studio',
            'status' => 'available',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'area_sqft' => 600,
            'location' => 'University Area, Gorakhpur',
            'address' => '321 Student Colony, University Area, Gorakhpur, UP 273009',
            'features' => 'Furnished, WiFi Ready, Security, Near University',
            'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Commercial Shop in Prime Location',
            'description' => 'Prime commercial space ideal for retail business. High footfall area with excellent visibility.',
            'price' => 3500000,
            'type' => 'commercial',
            'status' => 'available',
            'bedrooms' => 0,
            'bathrooms' => 1,
            'area_sqft' => 800,
            'location' => 'Main Market, Gorakhpur',
            'address' => '555 Business District, Main Market, Gorakhpur, UP 273001',
            'features' => 'Prime Location, High Visibility, Parking Nearby, 24/7 Access',
            'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];

    echo "<h2>🏠 Adding Demo Properties to APS Dream Home</h2>\n";

    foreach ($properties as $index => $property) {
        // Check if property already exists
        $check_sql = "SELECT id FROM properties WHERE title = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$property['title']]);

        if ($check_stmt->rowCount() > 0) {
            echo "<p>⚠️ Property '{$property['title']}' already exists. Skipping...</p>\n";
            continue;
        }

        // Insert property
        $sql = "INSERT INTO properties (
            title, description, price, type, status, bedrooms, bathrooms,
            area_sqft, location, address, features, image_url, agent_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $property['title'],
            $property['description'],
            $property['price'],
            $property['type'],
            $property['status'],
            $property['bedrooms'],
            $property['bathrooms'],
            $property['area_sqft'],
            $property['location'],
            $property['address'],
            $property['features'],
            $property['image_url'],
            $property['agent_id'],
            $property['created_at']
        ]);

        if ($result) {
            echo "<p>✅ Property " . ($index + 1) . ": '{$property['title']}' added successfully!</p>\n";
        } else {
            echo "<p>❌ Failed to add property: '{$property['title']}'</p>\n";
        }
    }

    echo "<hr>\n";
    echo "<h3>📊 Summary:</h3>\n";

    // Count total properties
    $count_sql = "SELECT COUNT(*) as total FROM properties WHERE status = 'available'";
    $count_result = $conn->query($count_sql);
    $total = $count_result->fetch(PDO::FETCH_ASSOC)['total'];

    echo "<p><strong>Total Properties Available: {$total}</strong></p>\n";
    echo "<p><a href='properties.php' class='btn btn-primary'>View Properties Page</a></p>\n";
    echo "<p><a href='admin_panel.php' class='btn btn-success'>Go to Admin Panel</a></p>\n";

} catch (PDOException $e) {
    echo "<h3>❌ Database Error:</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Please make sure your database is set up correctly.</p>\n";
}
?>
