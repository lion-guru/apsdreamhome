<?php
/**
 * APS Dream Home - Sample Data Generator
 * Creates demo data for testing and development
 */

// Allow direct execution
define('DIRECT_ACCESS', true);

require_once 'includes/db_connection.php';

try {
    $conn = getDbConnection();
    echo "=== APS DREAM HOME - SAMPLE DATA GENERATOR ===\n\n";

    // Sample Users
    echo "Creating sample users...\n";
    $users = [
        [
            'name' => 'Abhay Pratap Singh',
            'username' => 'admin',
            'email' => 'admin@apsdreamhome.com',
            'phone' => '9838123456',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Rajesh Kumar',
            'username' => 'rajesh.agent',
            'email' => 'rajesh@apsdreamhome.com',
            'phone' => '9838234567',
            'password' => password_hash('agent123', PASSWORD_DEFAULT),
            'role' => 'agent',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Priya Sharma',
            'username' => 'priya.agent',
            'email' => 'priya@apsdreamhome.com',
            'phone' => '9838345678',
            'password' => password_hash('agent123', PASSWORD_DEFAULT),
            'role' => 'agent',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Amit Singh',
            'username' => 'amit.customer',
            'email' => 'amit@example.com',
            'phone' => '9838456789',
            'password' => password_hash('customer123', PASSWORD_DEFAULT),
            'role' => 'customer',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Sneha Patel',
            'username' => 'sneha.customer',
            'email' => 'sneha@example.com',
            'phone' => '9838567890',
            'password' => password_hash('customer123', PASSWORD_DEFAULT),
            'role' => 'customer',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($users as $user) {
        $stmt = $conn->prepare("INSERT INTO users (name, username, email, phone, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_values($user));
    }
    echo "✅ Created " . count($users) . " sample users\n\n";

    // Sample Properties
    echo "Creating sample properties...\n";
    $properties = [
        [
            'title' => 'Luxury 3BHK Apartment - Gomti Nagar',
            'description' => 'Spacious 3BHK apartment in prime location with modern amenities, parking, and 24/7 security.',
            'price' => 8500000,
            'location' => 'Gomti Nagar, Lucknow',
            'address' => 'Plot 123, Sector A, Gomti Nagar, Lucknow',
            'type' => 'apartment',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area_sqft' => 1500,
            'status' => 'available',
            'user_id' => 2,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Modern Villa - Indira Nagar',
            'description' => 'Beautiful 4BHK villa with garden, parking space, and excellent connectivity to city center.',
            'price' => 15000000,
            'location' => 'Indira Nagar, Lucknow',
            'address' => 'Villa 45, Block C, Indira Nagar, Lucknow',
            'type' => 'villa',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'area_sqft' => 2500,
            'status' => 'available',
            'user_id' => 3,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Commercial Office Space - Hazratganj',
            'description' => 'Prime commercial property in business district with modern facilities and excellent footfall.',
            'price' => 25000000,
            'location' => 'Hazratganj, Lucknow',
            'address' => 'Office Complex, Mall Road, Hazratganj, Lucknow',
            'type' => 'commercial',
            'bedrooms' => 0,
            'bathrooms' => 2,
            'area_sqft' => 2000,
            'status' => 'available',
            'user_id' => 2,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => '2BHK Apartment - Alambagh',
            'description' => 'Affordable 2BHK apartment perfect for small families with all basic amenities.',
            'price' => 4500000,
            'location' => 'Alambagh, Lucknow',
            'address' => 'Apartment 67, Sector B, Alambagh, Lucknow',
            'type' => 'apartment',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area_sqft' => 1200,
            'status' => 'available',
            'user_id' => 3,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'title' => 'Independent House - Rajajipuram',
            'description' => 'Spacious independent house with garden and parking in peaceful neighborhood.',
            'price' => 12000000,
            'location' => 'Rajajipuram, Lucknow',
            'address' => 'House 89, Street 4, Rajajipuram, Lucknow',
            'type' => 'house',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area_sqft' => 1800,
            'status' => 'sold',
            'user_id' => 2,
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
        ]
    ];

    foreach ($properties as $property) {
        $stmt = $conn->prepare("INSERT INTO properties (title, description, price, location, address, type, bedrooms, bathrooms, area_sqft, status, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_values($property));
    }
    echo "✅ Created " . count($properties) . " sample properties\n\n";

    // Sample Leads
    echo "Creating sample leads...\n";
    $leads = [
        [
            'name' => 'Vikram Singh',
            'email' => 'vikram.singh@email.com',
            'phone' => '9838678901',
            'source' => 'website',
            'status' => 'new',
            'budget' => 10000000,
            'message' => 'Looking for 3BHK apartment in Lucknow',
            'assigned_to' => 2,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Anita Gupta',
            'email' => 'anita.gupta@email.com',
            'phone' => '9838789012',
            'source' => 'phone',
            'status' => 'contacted',
            'budget' => 8000000,
            'message' => 'Interested in villa properties',
            'assigned_to' => 3,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'name' => 'Ravi Kumar',
            'email' => 'ravi.kumar@email.com',
            'phone' => '9838890123',
            'source' => 'email',
            'status' => 'qualified',
            'budget' => 20000000,
            'message' => 'Need commercial property for business',
            'assigned_to' => 2,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ],
        [
            'name' => 'Meera Sharma',
            'email' => 'meera.sharma@email.com',
            'phone' => '9838901234',
            'source' => 'referral',
            'status' => 'converted',
            'budget' => 8500000,
            'message' => 'Found perfect apartment through referral',
            'assigned_to' => 3,
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
        ],
        [
            'name' => 'Deepak Patel',
            'email' => 'deepak.patel@email.com',
            'phone' => '9839012345',
            'source' => 'website',
            'status' => 'new',
            'budget' => 6000000,
            'message' => 'Looking for budget-friendly options',
            'assigned_to' => null,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];

    foreach ($leads as $lead) {
        $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, source, status, budget, message, assigned_to, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_values($lead));
    }
    echo "✅ Created " . count($leads) . " sample leads\n\n";

    // Sample Site Settings
    echo "Creating sample site settings...\n";
    $settings = [
        ['site_title', 'APS Dream Home'],
        ['site_description', 'Premium real estate platform for buying, selling, and renting properties in Gorakhpur and across India'],
        ['contact_email', 'info@apsdreamhome.com'],
        ['contact_phone', '9838123456'],
        ['contact_address', '123 Main Street, Gorakhpur, Uttar Pradesh'],
        ['default_language', 'en'],
        ['maintenance_mode', 'false'],
        ['items_per_page', '20'],
        ['smtp_host', 'smtp.gmail.com'],
        ['smtp_port', '587'],
        ['session_timeout', '60']
    ];

    foreach ($settings as $setting) {
        $stmt = $conn->prepare("INSERT INTO site_settings (setting_name, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
        $stmt->execute([$setting[0], $setting[1], $setting[1]]);
    }
    echo "✅ Created " . count($settings) . " site settings\n\n";

    // Sample Contact Messages
    echo "Creating sample contact messages...\n";
    $messages = [
        [
            'name' => 'Suresh Kumar',
            'email' => 'suresh@example.com',
            'phone' => '9838123456',
            'subject' => 'Property Inquiry',
            'message' => 'I am interested in your properties. Please contact me.',
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Kavita Singh',
            'email' => 'kavita@example.com',
            'phone' => '9838234567',
            'subject' => 'Partnership Opportunity',
            'message' => 'We would like to explore partnership opportunities with APS Dream Home.',
            'status' => 'read',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ]
    ];

    foreach ($messages as $message) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_values($message));
    }
    echo "✅ Created " . count($messages) . " contact messages\n\n";

    echo "=== SAMPLE DATA CREATION COMPLETE ===\n\n";
    echo "✅ Total Records Created:\n";
    echo "   • Users: " . count($users) . "\n";
    echo "   • Properties: " . count($properties) . "\n";
    echo "   • Leads: " . count($leads) . "\n";
    echo "   • Settings: " . count($settings) . "\n";
    echo "   • Messages: " . count($messages) . "\n\n";
    echo "✅ Login Credentials:\n";
    echo "   • Admin: admin@apsdreamhome.com / admin123\n";
    echo "   • Agent: rajesh@apsdreamhome.com / agent123\n";
    echo "   • Customer: amit@example.com / customer123\n\n";
    echo "✅ Ready for testing and development!\n";

} catch (Exception $e) {
    echo "❌ Error creating sample data: " . $e->getMessage() . "\n";
    exit(1);
}
?>
