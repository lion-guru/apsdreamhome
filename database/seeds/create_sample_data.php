<?php
/**
 * APS Dream Home - Sample Data Generator
 * Creates demo data for testing and development
 */

// Allow direct execution
define('DIRECT_ACCESS', true);

require_once 'includes/config.php';

try {
    // Use PDO connection instead of getDbConnection()
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            'password' => password_hash('agent123', PASSWORD_DEFAULT),
            'role' => 'user'
        ],
        [
            'username' => 'amit.customer',
            'email' => 'amit@example.com',
            'password' => password_hash('customer123', PASSWORD_DEFAULT),
            'role' => 'user'
        ],
        [
            'username' => 'sneha.customer',
            'email' => 'sneha@example.com',
            'password' => password_hash('customer123', PASSWORD_DEFAULT),
            'role' => 'user'
        ]
    ];

    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE role = ?");
        $stmt->execute([$user['username'], $user['email'], $user['password'], $user['role'], $user['role']]);
    }
    echo "✅ Created " . count($users) . " sample users\n\n";

    // Sample Properties (matching actual database schema)
    $properties = [
        [
            'title' => 'Luxury 3BHK Apartment in Heart of City',
            'description' => 'A premium 3BHK apartment with modern amenities, located in the prime area of Gorakhpur. Features include modular kitchen, spacious bedrooms, and excellent connectivity.',
            'price' => 7500000,
            'location' => 'Gorakhpur, Uttar Pradesh',
            'status' => 'available'
        ],
        [
            'title' => 'Spacious 4BHK Villa with Garden',
            'description' => 'Beautiful independent villa with private garden, perfect for families. Located in a peaceful neighborhood with all modern amenities.',
            'price' => 12000000,
            'location' => 'Gorakhpur, Uttar Pradesh',
            'status' => 'available'
        ],
        [
            'title' => 'Modern 2BHK Apartment - Ready to Move',
            'description' => 'Well-maintained 2BHK apartment in a gated community. Ready for immediate possession with all modern facilities.',
            'price' => 4500000,
            'location' => 'Gorakhpur, Uttar Pradesh',
            'status' => 'available'
        ],
        [
            'title' => 'Commercial Office Space in Prime Location',
            'description' => 'Premium commercial space ideal for office or retail. High footfall area with excellent visibility and parking.',
            'price' => 15000000,
            'location' => 'Gorakhpur, Uttar Pradesh',
            'status' => 'available'
        ],
        [
            'title' => 'Investment Plot in Developing Area',
            'description' => 'Prime investment opportunity in a rapidly developing area. Perfect for future construction or land banking.',
            'price' => 8000000,
            'location' => 'Gorakhpur, Uttar Pradesh',
            'status' => 'sold'
        ]
    ];

    foreach ($properties as $property) {
        $stmt = $pdo->prepare("INSERT INTO properties (title, description, price, location, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
        $stmt->execute([$property['title'], $property['description'], $property['price'], $property['location'], $property['status'], $property['status']]);
    }
    echo "✅ Created " . count($properties) . " sample properties\n\n";

    echo "=== SAMPLE DATA CREATION COMPLETE ===\n\n";
    echo "✅ Total Records Created:\n";
    echo "   • Users: " . count($users) . "\n";
    echo "   • Properties: " . count($properties) . "\n\n";
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
