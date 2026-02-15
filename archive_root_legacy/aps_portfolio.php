<?php
/**
 * APS Dream Homes Pvt Ltd - Professional Property Portfolio
 * Adds realistic properties for a registered real estate development company
 */

require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    echo "<h2>🏠 APS Dream Homes Pvt Ltd - Property Portfolio</h2>\n";

    // Professional property portfolio for a registered developer
    $properties = [
        [
            'title' => 'APS Green Valley - 2BHK Premium Apartments',
            'description' => 'APS Dream Homes presents Green Valley, a premium residential project featuring modern 2BHK apartments with world-class amenities. Located in the heart of Gorakhpur, this project offers spacious living spaces, contemporary design, and eco-friendly features. Each apartment is designed to provide maximum comfort and luxury to residents.',
            'price' => 4500000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area_sqft' => 1200,
            'location' => 'Kunraghat, Gorakhpur',
            'address' => 'Green Valley Project, Kunraghat Main Road, Gorakhpur, UP - 273008',
            'features' => 'Clubhouse, Swimming Pool, Gymnasium, Children Play Area, 24/7 Security, Power Backup, Landscaped Gardens, Jogging Track',
            'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Royal Residency - 3BHK Luxury Flats',
            'description' => 'Experience luxury living at APS Royal Residency. Our flagship project features spacious 3BHK apartments with premium specifications including Italian marble flooring, modular kitchens, and modern bathrooms. The project includes exclusive amenities like rooftop infinity pool, banquet hall, and concierge services.',
            'price' => 7500000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'area_sqft' => 1650,
            'location' => 'Civil Lines, Gorakhpur',
            'address' => 'Royal Residency, Civil Lines Extension, Gorakhpur, UP - 273001',
            'features' => 'Rooftop Infinity Pool, Banquet Hall, Concierge Services, Italian Marble, Modular Kitchen, Smart Home Features, Multi-tier Security',
            'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Business Park - Commercial Office Spaces',
            'description' => 'APS Business Park offers premium commercial office spaces designed for modern businesses. With state-of-the-art infrastructure, high-speed connectivity, and professional management, this is the ideal location for IT companies, startups, and established businesses looking for a prestigious address.',
            'price' => 8500000,
            'type' => 'commercial',
            'status' => 'available',
            'bedrooms' => 0,
            'bathrooms' => 2,
            'area_sqft' => 1500,
            'location' => 'Business District, Gorakhpur',
            'address' => 'APS Business Park, IT Park Area, Gorakhpur, UP - 273001',
            'features' => 'High-Speed Internet, Conference Rooms, Cafeteria, Parking Facility, 24/7 Power Backup, Professional Management, Modern Infrastructure',
            'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Smart Villas - 4BHK Independent Houses',
            'description' => 'APS Smart Villas represent the pinnacle of luxury living. These independent 4BHK villas feature smart home technology, private gardens, and premium construction quality. Each villa is designed with modern architecture and includes exclusive amenities for a truly luxurious lifestyle.',
            'price' => 15000000,
            'type' => 'villa',
            'status' => 'available',
            'bedrooms' => 4,
            'bathrooms' => 4,
            'area_sqft' => 2500,
            'location' => 'Smart City Area, Gorakhpur',
            'address' => 'APS Smart Villas, Smart City Extension, Gorakhpur, UP - 273001',
            'features' => 'Smart Home Technology, Private Garden, Swimming Pool, Home Theater, Premium Construction, Energy Efficient, 24/7 Security',
            'image_url' => 'https://images.unsplash.com/photo-1613977257363-707ba9348227?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Affordable Housing - 1BHK Starter Homes',
            'description' => 'APS Affordable Housing project is designed for first-time home buyers and young families. These well-planned 1BHK apartments offer modern amenities at budget-friendly prices. The project focuses on quality construction and community living with essential facilities.',
            'price' => 2800000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'area_sqft' => 650,
            'location' => 'Developing Area, Gorakhpur',
            'address' => 'APS Affordable Housing, Developing Sector, Gorakhpur, UP - 273008',
            'features' => 'Community Center, Children Play Area, Security, Parking, Quality Construction, Affordable Maintenance, Good Connectivity',
            'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Lakeview Plots - Residential Land',
            'description' => 'APS Lakeview Plots offer excellent investment opportunities in a rapidly developing area. These residential plots are perfect for building your dream home with modern infrastructure and scenic surroundings. The project includes planned roads, drainage, and basic amenities.',
            'price' => 3500000,
            'type' => 'plot',
            'status' => 'available',
            'bedrooms' => 0,
            'bathrooms' => 0,
            'area_sqft' => 1200,
            'location' => 'Lakeview Area, Gorakhpur',
            'address' => 'APS Lakeview Plots, Near City Lake, Gorakhpur, UP - 273001',
            'features' => 'Scenic Location, Planned Infrastructure, Investment Opportunity, Clear Title, Good Connectivity, Development Potential',
            'image_url' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Corporate Suites - Service Apartments',
            'description' => 'APS Corporate Suites provide premium service apartments for business travelers and corporate clients. These fully furnished apartments offer hotel-like amenities with the comfort of home. Perfect for short-term and long-term stays with professional management.',
            'price' => 5500000,
            'type' => 'apartment',
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area_sqft' => 1000,
            'location' => 'Business Hub, Gorakhpur',
            'address' => 'APS Corporate Suites, Business District, Gorakhpur, UP - 273001',
            'features' => 'Fully Furnished, Hotel Amenities, Housekeeping, Business Center, Conference Facilities, 24/7 Support, Prime Location',
            'image_url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800',
            'agent_id' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'APS Heritage Row Houses',
            'description' => 'APS Heritage Row Houses combine traditional architecture with modern amenities. These 3BHK row houses feature contemporary design, private parking, and community facilities. The project emphasizes sustainable living with green spaces and energy-efficient features.',
            'price' => 9200000,
            'type' => 'house',
            'status' => 'available',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'area_sqft' => 1800,
            'location' => 'Heritage District, Gorakhpur',
            'address' => 'APS Heritage Row Houses, Heritage Area, Gorakhpur, UP - 273001',
            'features' => 'Traditional Architecture, Modern Amenities, Private Parking, Community Facilities, Green Spaces, Energy Efficient, Sustainable Design',
            'image_url' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
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
    echo "<h3>📊 APS Dream Homes Project Portfolio:</h3>\n";

    // Count total properties by type
    $type_sql = "SELECT type, COUNT(*) as count FROM properties WHERE status = 'available' GROUP BY type";
    $type_result = $conn->query($type_sql);
    $total_value = 0;

    echo "<div class='row'>\n";
    echo "<div class='col-md-6'>\n";
    echo "<h5>🏗️ Project Types:</h5>\n";
    echo "<ul>\n";
    while ($row = $type_result->fetch(PDO::FETCH_ASSOC)) {
        echo "<li><strong>" . ucfirst($row['type']) . "s:</strong> {$row['count']} projects</li>\n";

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
    echo "<p><strong>Average Project Price:</strong> ₹" . number_format($total_value / 8) . "</p>\n";
    echo "<p><strong>Active Projects:</strong> 8 premium developments</p>\n";
    echo "</div>\n";
    echo "</div>\n";

    echo "<div class='alert alert-info mt-4'>\n";
    echo "<h6>🏆 Project Highlights:</h6>\n";
    echo "<ul>\n";
    echo "<li><strong>Green Valley:</strong> Eco-friendly apartments with modern amenities</li>\n";
    echo "<li><strong>Royal Residency:</strong> Luxury living with premium specifications</li>\n";
    echo "<li><strong>Business Park:</strong> Commercial spaces for modern businesses</li>\n";
    echo "<li><strong>Smart Villas:</strong> Technology-integrated luxury homes</li>\n";
    echo "<li><strong>Affordable Housing:</strong> Quality homes for first-time buyers</li>\n";
    echo "<li><strong>Lakeview Plots:</strong> Investment opportunities in scenic locations</li>\n";
    echo "<li><strong>Corporate Suites:</strong> Premium service apartments</li>\n";
    echo "<li><strong>Heritage Row Houses:</strong> Traditional architecture meets modern living</li>\n";
    echo "</ul>\n";
    echo "</div>\n";

    echo "<div class='mt-4'>\n";
    echo "<a href='properties.php' class='btn btn-primary'>View All Projects</a>\n";
    echo "<a href='index.php' class='btn btn-success'>Go to Homepage</a>\n";
    echo "<a href='admin_panel.php' class='btn btn-secondary'>Manage Portfolio</a>\n";
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
    <title>APS Dream Homes Portfolio - Professional Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">🏠 APS Dream Homes Pvt Ltd - Project Portfolio</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Adding professional property portfolio for a registered real estate development company.</p>

                        <div class="alert alert-info">
                            <h6>✅ Projects Being Added:</h6>
                            <ul>
                                <li><strong>Green Valley:</strong> 2BHK Premium Apartments (₹45 Lac)</li>
                                <li><strong>Royal Residency:</strong> 3BHK Luxury Flats (₹75 Lac)</li>
                                <li><strong>Business Park:</strong> Commercial Office Spaces (₹85 Lac)</li>
                                <li><strong>Smart Villas:</strong> 4BHK Independent Houses (₹1.5 Cr)</li>
                                <li><strong>Affordable Housing:</strong> 1BHK Starter Homes (₹28 Lac)</li>
                                <li><strong>Lakeview Plots:</strong> Residential Land (₹35 Lac)</li>
                                <li><strong>Corporate Suites:</strong> Service Apartments (₹55 Lac)</li>
                                <li><strong>Heritage Row Houses:</strong> Traditional Homes (₹92 Lac)</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6>🏗️ Developer Features:</h6>
                            <p>Each project includes professional amenities, quality construction, and comprehensive facilities typical of registered developers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
