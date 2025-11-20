<?php
/**
 * APS Dream Home - Demo Data Setup (FIXED)
 * This script populates the database with sample data for testing and demonstration
 * Updated to match actual database schema
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'includes/db_connection.php';

try {
    $conn = getMysqliConnection();

    echo "<h1>🚀 APS Dream Home - Demo Data Setup (FIXED)</h1>";
    echo "<p>Setting up demo data for testing and demonstration...</p>";

    // Check existing tables
    $tables_result = $conn->query("SHOW TABLES");
    $tables = $tables_result->fetchAll(PDO::FETCH_COLUMN);

    // Property Types
    echo "<h2>📊 Setting up Property Types...</h2>";
    if (in_array('property_types', $tables)) {
        $property_types = [
            ['Apartment', 'Residential apartments and flats'],
            ['Villa', 'Independent villas and bungalows'],
            ['House', 'Individual houses and duplexes'],
            ['Plot', 'Land and plots for construction'],
            ['Commercial', 'Commercial properties and offices'],
            ['Penthouse', 'Luxury penthouse apartments']
        ];

        foreach ($property_types as $type) {
            $stmt = $conn->prepare("INSERT IGNORE INTO property_types (name, description) VALUES (?, ?)");
            $stmt->execute($type);
            echo "✅ Added property type: {$type[0]}<br>";
        }
    } else {
        echo "⚠️ property_types table does not exist - skipping<br>";
    }

    // Users - Check actual schema first
    echo "<h2>👥 Setting up Demo Users...</h2>";

    // Check users table structure
    $result = $conn->query('DESCRIBE users');
    $user_columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $user_columns = array_column($user_columns, 'Field');

    echo "<p><strong>Users table columns:</strong> " . implode(', ', $user_columns) . "</p>";

    // Admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    if (in_array('first_name', $user_columns) && in_array('last_name', $user_columns)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO users (first_name, last_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Admin', 'User', 'admin@apsdreamhome.com', '9876543210', $admin_password, 'admin', 'active']);
        echo "✅ Created admin user: admin@apsdreamhome.com<br>";
    } else {
        // Try alternative structure
        $stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Admin User', 'admin@apsdreamhome.com', '9876543210', $admin_password, 'admin', 'active']);
        echo "✅ Created admin user: admin@apsdreamhome.com (alternative structure)<br>";
    }

    // Demo agents
    $agents = [
        ['Rajesh', 'Kumar', 'rajesh@apsdreamhome.com', '9123456780', 'agent123'],
        ['Priya', 'Sharma', 'priya@apsdreamhome.com', '9234567891', 'agent123'],
        ['Amit', 'Singh', 'amit@apsdreamhome.com', '9345678922', 'agent123']
    ];

    foreach ($agents as $agent) {
        $password = password_hash($agent[4], PASSWORD_DEFAULT);
        if (in_array('first_name', $user_columns) && in_array('last_name', $user_columns)) {
            $stmt = $conn->prepare("INSERT IGNORE INTO users (first_name, last_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$agent[0], $agent[1], $agent[2], $agent[3], $password, 'agent', 'active']);
        } else {
            $stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(["{$agent[0]} {$agent[1]}", $agent[2], $agent[3], $password, 'agent', 'active']);
        }
        echo "✅ Created agent: {$agent[2]}<br>";
    }

    // Demo customers
    $customers = [
        ['Vikas', 'Gupta', 'vikas@demo.com', '9456789233', 'customer123'],
        ['Neha', 'Patel', 'neha@demo.com', '9567892344', 'customer123'],
        ['Rohit', 'Mehta', 'rohit@demo.com', '9678923455', 'customer123']
    ];

    foreach ($customers as $customer) {
        $password = password_hash($customer[4], PASSWORD_DEFAULT);
        if (in_array('first_name', $user_columns) && in_array('last_name', $user_columns)) {
            $stmt = $conn->prepare("INSERT IGNORE INTO users (first_name, last_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$customer[0], $customer[1], $customer[2], $customer[3], $password, 'customer', 'active']);
        } else {
            $stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(["{$customer[0]} {$customer[1]}", $customer[2], $customer[3], $password, 'customer', 'active']);
        }
        echo "✅ Created customer: {$customer[2]}<br>";
    }

    // Properties - Check actual schema first
    echo "<h2>🏠 Setting up Demo Properties...</h2>";

    // Check properties table structure
    $result = $conn->query('DESCRIBE properties');
    $property_columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $property_columns = array_column($property_columns, 'Field');

    echo "<p><strong>Properties table columns:</strong> " . implode(', ', $property_columns) . "</p>";

    // Get agent IDs for properties
    $agent_result = $conn->query("SELECT id FROM users WHERE role = 'agent' LIMIT 3");
    $agent_ids = $agent_result->fetchAll(PDO::FETCH_COLUMN);

    // Properties data - adapt to actual schema
    if (in_array('title', $property_columns) && in_array('description', $property_columns)) {
        $properties = [
            [
                'Luxury 3BHK Apartment in City Center',
                'Beautiful apartment with modern amenities, gym, swimming pool, and 24/7 security',
                7500000, 3, 2, 1200, '123 Main Street, Civil Lines, Gorakhpur',
                'available', 1, $agent_ids[0] ?? 1, 1
            ],
            [
                'Spacious 4BHK Villa with Garden',
                'Independent villa with private garden, parking, and modern kitchen',
                15000000, 4, 3, 2000, '456 Garden Road, Rajendra Nagar, Gorakhpur',
                'available', 2, $agent_ids[1] ?? 1, 1
            ],
            [
                'Commercial Office Space',
                'Prime location office space for business with parking and security',
                5000000, 0, 2, 800, '789 Business District, Golghar, Gorakhpur',
                'available', 5, $agent_ids[2] ?? 1, 0
            ],
            [
                '2BHK Apartment with Balcony',
                'Cozy apartment with city view, near market and transport',
                4500000, 2, 2, 900, '321 Residency Road, Alinagar, Gorakhpur',
                'available', 1, $agent_ids[0] ?? 1, 0
            ],
            [
                'Penthouse with Terrace Garden',
                'Luxury penthouse with private terrace, jacuzzi, and panoramic view',
                25000000, 5, 4, 3000, '999 Skyline Tower, VIP Road, Gorakhpur',
                'available', 6, $agent_ids[1] ?? 1, 1
            ],
            [
                'Residential Plot for Construction',
                'Prime residential plot in developing area, ready for construction',
                3000000, 0, 0, 1500, 'Plot No. 45, Sector 12, Gorakhpur',
                'available', 4, $agent_ids[2] ?? 1, 0
            ]
        ];

        foreach ($properties as $property) {
            // Build dynamic query based on available columns
            $columns = ['title', 'description', 'price', 'bedrooms', 'bathrooms', 'area', 'address', 'status', 'property_type_id', 'agent_id', 'is_featured'];
            $placeholders = array_fill(0, count($columns), '?');
            $query = "INSERT IGNORE INTO properties (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $conn->prepare($query);
            $stmt->execute($property);
            echo "✅ Added property: {$property[0]}<br>";
        }
    } else {
        echo "⚠️ Properties table structure not compatible - skipping properties<br>";
    }

    // Testimonials
    echo "<h2>💬 Setting up Demo Testimonials...</h2>";
    if (in_array('testimonials', $tables)) {
        $testimonials = [
            ['Vikas Gupta', 'Great experience finding my dream home. Professional service and excellent support.', 5, 'Gorakhpur'],
            ['Neha Patel', 'Found the perfect apartment for my family. Highly recommend APS Dream Home!', 5, 'Gorakhpur'],
            ['Rohit Mehta', 'Excellent property listings and smooth transaction process. Very satisfied!', 5, 'Gorakhpur']
        ];

        foreach ($testimonials as $testimonial) {
            $stmt = $conn->prepare("INSERT IGNORE INTO testimonials (name, message, rating, location, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$testimonial[0], $testimonial[1], $testimonial[2], $testimonial[3], 'approved']);
            echo "✅ Added testimonial from: {$testimonial[0]}<br>";
        }
    } else {
        echo "⚠️ testimonials table does not exist - skipping testimonials<br>";
    }

    // Contact Messages
    echo "<h2>📧 Setting up Demo Contact Messages...</h2>";
    if (in_array('contacts', $tables)) {
        $contacts = [
            ['John Doe', 'john@example.com', '9876543210', 'Looking for 2BHK apartment in Gorakhpur'],
            ['Jane Smith', 'jane@example.com', '9123456789', 'Interested in commercial property'],
            ['Bob Wilson', 'bob@example.com', '9345678901', 'Need help finding villa with garden']
        ];

        foreach ($contacts as $contact) {
            $stmt = $conn->prepare("INSERT IGNORE INTO contacts (name, email, phone, message, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$contact[0], $contact[1], $contact[2], $contact[3], 'new']);
            echo "✅ Added contact message from: {$contact[0]}<br>";
        }
    } else {
        echo "⚠️ contacts table does not exist - skipping contact messages<br>";
    }

    echo "<h2>🎉 Demo Data Setup Complete!</h2>";
    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Summary:</h4>";
    echo "<ul>";
    echo "<li>✅ Property Types: " . (in_array('property_types', $tables) ? count($property_types) : 0) . " added</li>";
    echo "<li>✅ Users: 1 admin, 3 agents, 3 customers</li>";
    echo "<li>✅ Properties: " . (in_array('properties', $tables) ? count($properties) : 0) . " demo properties</li>";
    echo "<li>✅ Testimonials: " . (in_array('testimonials', $tables) ? count($testimonials) : 0) . " customer reviews</li>";
    echo "<li>✅ Contact Messages: " . (in_array('contacts', $tables) ? count($contacts) : 0) . " inquiries</li>";
    echo "</ul>";
    echo "<p><strong>Your APS Dream Home is now ready with demo data!</strong></p>";
    echo "</div>";

    echo "<div class='mt-4'>";
    echo "<a href='index.php' class='btn btn-primary btn-lg me-3'>🏠 Go to Homepage</a>";
    echo "<a href='properties.php' class='btn btn-success btn-lg me-3'>🏢 View Properties</a>";
    echo "<a href='comprehensive_test.php' class='btn btn-info btn-lg'>🧪 Run System Tests</a>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error Setting Up Demo Data</h4>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p>Please check your database connection and table structure.</p>";
    echo "<p><strong>Debug Info:</strong></p>";
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
    echo "</div>";
}

echo "<style>
.alert { padding: 20px; margin: 20px 0; border-radius: 5px; }
.alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
.alert-danger { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
.btn { padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
.btn-primary { background-color: #007bff; color: white; }
.btn-success { background-color: #28a745; color: white; }
.btn-info { background-color: #17a2b8; color: white; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow: auto; }
</style>";
?>
