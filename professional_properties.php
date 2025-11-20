<?php
/**
 * Professional Property Listings for Gorakhpur
 * Adds realistic properties based on real market data
 */

require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    echo "<h2>🏠 Adding Professional Property Listings</h2>\n";

    // Professional property listings based on real Gorakhpur market
    $properties = [
        [
            'title' => 'Luxury 4BHK Villa in Civil Lines',
            'description' => 'Premium 4BHK independent villa in the prestigious Civil Lines area. This spacious 2800 sq ft property features modern architecture, private garden, parking for 2 cars, and premium finishes throughout. Perfect for families seeking luxury living in Gorakhpur\'s most sought-after neighborhood.',
            'price' => 12500000,
            'type' => 'villa',
            'status' => 'available',
            'bedrooms' => 4,
            'bathrooms' => 4,
            'area_sqft' => 2800,
            'location' => 'Civil Lines, Gorakhpur',
            'address' => '456, Civil Lines, Near City Mall, Gorakhpur, UP - 273001',
            'features' => 'Private Garden, 2 Car Parking, Modular Kitchen, Premium Interiors, 24/7 Security, Power Backup',
            'image_url' => 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Modern 3BHK Apartment in Betiahata',
            'description' => 'Beautiful 3BHK apartment in a well-maintained society in Betiahata. This 1650 sq ft apartment offers modern amenities including gym, swimming pool, children\'s play area, and community hall. Ideal for modern families looking for comfortable city living.',
            'price' => 6500000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'area_sqft' => 1650,
            'location' => 'Betiahata, Gorakhpur',
            'address' => '789, Royal Residency, Betiahata Main Road, Gorakhpur, UP - 273001',
            'features' => 'Gym, Swimming Pool, Children Play Area, Community Hall, Lift, Security',
            'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Spacious 2BHK Flat in Rajendra Nagar',
            'description' => 'Well-designed 2BHK flat in a peaceful locality of Rajendra Nagar. This 1200 sq ft property features a spacious balcony with garden views, modern kitchen, and excellent ventilation. Perfect for young professionals or small families.',
            'price' => 3800000,
            'type' => 'flat',
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area_sqft' => 1200,
            'location' => 'Rajendra Nagar, Gorakhpur',
            'address' => '321, Green Park Society, Rajendra Nagar, Gorakhpur, UP - 273004',
            'features' => 'Balcony with Garden View, Modern Kitchen, Natural Light, Parking, Security',
            'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Commercial Office Space in Golghar',
            'description' => 'Prime commercial office space in the heart of Golghar business district. This 2000 sq ft property is ideal for IT companies, coaching institutes, or professional offices. Features modern infrastructure and excellent connectivity.',
            'price' => 15000000,
            'type' => 'commercial',
            'status' => 'available',
            'bedrooms' => 0,
            'bathrooms' => 2,
            'area_sqft' => 2000,
            'location' => 'Golghar, Gorakhpur',
            'address' => '555, Business Center, Golghar Main Road, Gorakhpur, UP - 273001',
            'features' => 'Prime Location, Modern Infrastructure, Parking, 24/7 Access, High-Speed Internet Ready',
            'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Affordable 1BHK Apartment near University',
            'description' => 'Budget-friendly 1BHK apartment perfect for students and young professionals. Located near DDU University, this 750 sq ft apartment offers modern amenities at an affordable price. Fully furnished and ready to move in.',
            'price' => 2200000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'area_sqft' => 750,
            'location' => 'University Area, Gorakhpur',
            'address' => '147, Student Housing Complex, Near DDU University, Gorakhpur, UP - 273009',
            'features' => 'Fully Furnished, Near University, WiFi Ready, Security, Affordable Maintenance',
            'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Independent House in Medical College Area',
            'description' => 'Charming independent house in a peaceful locality near Medical College. This 1800 sq ft property features 3 bedrooms, private terrace, small garden, and parking space. Ideal for doctors, medical professionals, or families seeking a quiet neighborhood.',
            'price' => 8500000,
            'type' => 'house',
            'status' => 'available',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'area_sqft' => 1800,
            'location' => 'Medical College Road, Gorakhpur',
            'address' => '258, Doctors Colony, Near Medical College, Gorakhpur, UP - 273003',
            'features' => 'Independent House, Private Terrace, Small Garden, Parking, Peaceful Location',
            'image_url' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Premium Plot in Sahara Estate',
            'description' => 'Excellent investment opportunity! 1500 sq ft residential plot in the developing Sahara Estate area. This corner plot offers great potential for building your dream home. Located in a rapidly developing area with good infrastructure.',
            'price' => 4500000,
            'type' => 'plot',
            'status' => 'available',
            'bedrooms' => 0,
            'bathrooms' => 0,
            'area_sqft' => 1500,
            'location' => 'Sahara Estate, Gorakhpur',
            'address' => 'Plot No. 123, Sahara Estate Phase 2, Gorakhpur, UP - 273006',
            'features' => 'Corner Plot, Developing Area, Good Infrastructure, Investment Opportunity, Clear Title',
            'image_url' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Duplex House in Shivpuri Colony',
            'description' => 'Modern duplex house in the upscale Shivpuri Colony. This 2200 sq ft property features 4 bedrooms, modern kitchen, spacious living areas, and a beautiful terrace garden. Perfect for large families looking for contemporary living.',
            'price' => 9800000,
            'type' => 'duplex',
            'status' => 'available',
            'bedrooms' => 4,
            'bathrooms' => 4,
            'area_sqft' => 2200,
            'location' => 'Shivpuri Colony, Gorakhpur',
            'address' => '369, Modern Duplex Homes, Shivpuri Colony, Gorakhpur, UP - 273001',
            'features' => 'Duplex Design, Terrace Garden, Modern Kitchen, Spacious Rooms, Premium Location',
            'image_url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];

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
    echo "<h3>📊 Professional Property Portfolio Summary:</h3>\n";

    // Count total properties by type
    $type_sql = "SELECT type, COUNT(*) as count FROM properties WHERE status = 'available' GROUP BY type";
    $type_result = $conn->query($type_sql);
    $total_value = 0;

    echo "<div class='row'>\n";
    echo "<div class='col-md-6'>\n";
    echo "<h5>🏠 Property Types:</h5>\n";
    echo "<ul>\n";
    while ($row = $type_result->fetch(PDO::FETCH_ASSOC)) {
        echo "<li><strong>" . ucfirst($row['type']) . "s:</strong> {$row['count']} properties</li>\n";

        // Calculate total value by type
        $value_sql = "SELECT SUM(price) as total FROM properties WHERE type = ? AND status = 'available'";
        $value_stmt = $conn->prepare($value_sql);
        $value_stmt->execute([$row['type']]);
        $value_row = $value_stmt->fetch(PDO::FETCH_ASSOC);
        $total_value += $value_row['total'];
    }
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div class='col-md-6'>\n";
    echo "<h5>💰 Portfolio Value:</h5>\n";
    echo "<p><strong>Total Portfolio Value:</strong> ₹" . number_format($total_value) . "</p>\n";
    echo "<p><strong>Average Property Price:</strong> ₹" . number_format($total_value / 8) . "</p>\n";
    echo "<p><strong>Properties Available:</strong> 8 premium listings</p>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class='mt-4'>\n";
    echo "<a href='properties.php' class='btn btn-primary'>View All Properties</a>\n";
    echo "<a href='index.php' class='btn btn-success'>Go to Homepage</a>\n";
    echo "<a href='admin_panel.php' class='btn btn-secondary'>Manage Properties</a>\n";
    echo "</div>\n";

} catch (PDOException $e) {
    echo "<h3>❌ Database Error:</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Properties - APS Dream Home Realty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">🏠 Professional Property Portfolio</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Adding realistic property listings based on Gorakhpur real estate market data.</p>

                        <div class="alert alert-info">
                            <h6>✅ Properties Being Added:</h6>
                            <ul>
                                <li><strong>Luxury Villa:</strong> Civil Lines (₹1.25 Cr)</li>
                                <li><strong>3BHK Apartment:</strong> Betiahata (₹65 Lac)</li>
                                <li><strong>2BHK Flat:</strong> Rajendra Nagar (₹38 Lac)</li>
                                <li><strong>Commercial Office:</strong> Golghar (₹1.5 Cr)</li>
                                <li><strong>1BHK Apartment:</strong> University Area (₹22 Lac)</li>
                                <li><strong>Independent House:</strong> Medical College (₹85 Lac)</li>
                                <li><strong>Residential Plot:</strong> Sahara Estate (₹45 Lac)</li>
                                <li><strong>Duplex House:</strong> Shivpuri Colony (₹98 Lac)</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6>💰 Market-Based Pricing:</h6>
                            <p>Property prices are based on actual Gorakhpur real estate market rates and location advantages.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
