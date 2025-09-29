<?php
/**
 * APS Dream Home - Sample Data Only
 * Adds sample data to existing tables
 */

echo "=== APS DREAM HOME - SAMPLE DATA INSERTION ===\n\n";

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhomefinal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n\n";

    // Check if users already exist
    $existingUsers = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    if ($existingUsers == 0) {
        echo "Creating sample users...\n";
        $users = [
            ['Abhay Pratap Singh', 'admin', 'admin@apsdreamhome.com', '9838123456', password_hash('admin123', PASSWORD_DEFAULT), 'admin'],
            ['Rajesh Kumar', 'rajesh.agent', 'rajesh@apsdreamhome.com', '9838234567', password_hash('agent123', PASSWORD_DEFAULT), 'agent'],
            ['Priya Sharma', 'priya.agent', 'priya@apsdreamhome.com', '9838345678', password_hash('agent123', PASSWORD_DEFAULT), 'agent'],
            ['Amit Singh', 'amit.customer', 'amit@example.com', '9838456789', password_hash('customer123', PASSWORD_DEFAULT), 'customer'],
            ['Sneha Patel', 'sneha.customer', 'sneha@example.com', '9838567890', password_hash('customer123', PASSWORD_DEFAULT), 'customer']
        ];

        foreach ($users as $user) {
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($user);
        }
        echo "✅ Created " . count($users) . " sample users\n";
    } else {
        echo "✅ Users table already has data (skipping)\n";
    }

    // Check if properties already exist
    $existingProperties = $pdo->query("SELECT COUNT(*) as count FROM properties")->fetch()['count'];
    if ($existingProperties == 0) {
        echo "Creating sample properties...\n";

        // Get agent IDs
        $agentIds = $pdo->query("SELECT id FROM users WHERE role = 'agent' ORDER BY id LIMIT 2")->fetchAll(PDO::FETCH_COLUMN);

        $properties = [
            ['Luxury 3BHK Apartment - Gomti Nagar', 'Spacious 3BHK apartment in prime location with modern amenities, parking, and 24/7 security.', 8500000, 'Gomti Nagar, Lucknow', 'Plot 123, Sector A, Gomti Nagar, Lucknow', 'apartment', 3, 2, 1500, 'available', $agentIds[0]],
            ['Modern Villa - Indira Nagar', 'Beautiful 4BHK villa with garden, parking space, and excellent connectivity to city center.', 15000000, 'Indira Nagar, Lucknow', 'Villa 45, Block C, Indira Nagar, Lucknow', 'villa', 4, 3, 2500, 'available', $agentIds[1]],
            ['Commercial Office Space - Hazratganj', 'Prime commercial property in business district with modern facilities and excellent footfall.', 25000000, 'Hazratganj, Lucknow', 'Office Complex, Mall Road, Hazratganj, Lucknow', 'commercial', 0, 2, 2000, 'available', $agentIds[0]],
            ['2BHK Apartment - Alambagh', 'Affordable 2BHK apartment perfect for small families with all basic amenities.', 4500000, 'Alambagh, Lucknow', 'Apartment 67, Sector B, Alambagh, Lucknow', 'apartment', 2, 2, 1200, 'available', $agentIds[1]],
            ['Independent House - Rajajipuram', 'Spacious independent house with garden and parking in peaceful neighborhood.', 12000000, 'Rajajipuram, Lucknow', 'House 89, Street 4, Rajajipuram, Lucknow', 'house', 3, 2, 1800, 'sold', $agentIds[0]]
        ];

        foreach ($properties as $property) {
            $stmt = $pdo->prepare("INSERT INTO properties (title, description, price, location, address, type, bedrooms, bathrooms, area_sqft, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($property);
        }
        echo "✅ Created " . count($properties) . " sample properties\n";
    } else {
        echo "✅ Properties table already has data (skipping)\n";
    }

    // Check if leads already exist
    $existingLeads = $pdo->query("SELECT COUNT(*) as count FROM leads")->fetch()['count'];
    if ($existingLeads == 0) {
        echo "Creating sample leads...\n";

        // Get agent IDs
        $agentIds = $pdo->query("SELECT id FROM users WHERE role = 'agent' ORDER BY id LIMIT 2")->fetchAll(PDO::FETCH_COLUMN);

        $leads = [
            ['Vikram Singh', 'vikram.singh@email.com', '9838678901', 'website', 'new', 10000000, 'Looking for 3BHK apartment in Lucknow', $agentIds[0]],
            ['Anita Gupta', 'anita.gupta@email.com', '9838789012', 'phone', 'contacted', 8000000, 'Interested in villa properties', $agentIds[1]],
            ['Ravi Kumar', 'ravi.kumar@email.com', '9838890123', 'email', 'qualified', 20000000, 'Need commercial property for business', $agentIds[0]],
            ['Meera Sharma', 'meera.sharma@email.com', '9838901234', 'referral', 'converted', 8500000, 'Found perfect apartment through referral', $agentIds[1]],
            ['Deepak Patel', 'deepak.patel@email.com', '9839012345', 'website', 'new', 6000000, 'Looking for budget-friendly options', null]
        ];

        foreach ($leads as $lead) {
            $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, source, status, budget, message, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($lead);
        }
        echo "✅ Created " . count($leads) . " sample leads\n";
    } else {
        echo "✅ Leads table already has data (skipping)\n";
    }

    // Check if site settings already exist
    $existingSettings = $pdo->query("SELECT COUNT(*) as count FROM site_settings")->fetch()['count'];
    if ($existingSettings == 0) {
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
            ['session_timeout', '60']
        ];

        foreach ($settings as $setting) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?)");
            $stmt->execute($setting);
        }
        echo "✅ Created " . count($settings) . " site settings\n";
    } else {
        echo "✅ Site settings table already has data (skipping)\n";
    }

    // Check if contact messages already exist
    $existingMessages = $pdo->query("SELECT COUNT(*) as count FROM contact_messages")->fetch()['count'];
    if ($existingMessages == 0) {
        echo "Creating sample contact messages...\n";
        $messages = [
            ['Suresh Kumar', 'suresh@example.com', '9838123456', 'Property Inquiry', 'I am interested in your properties. Please contact me.', 'new'],
            ['Kavita Singh', 'kavita@example.com', '9838234567', 'Partnership Opportunity', 'We would like to explore partnership opportunities with APS Dream Home.', 'read']
        ];

        foreach ($messages as $message) {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute($message);
        }
        echo "✅ Created " . count($messages) . " contact messages\n";
    } else {
        echo "✅ Contact messages table already has data (skipping)\n";
    }

    echo "\n=== SAMPLE DATA SETUP COMPLETE ===\n\n";
    echo "✅ Database populated with sample data\n\n";
    echo "✅ Login Credentials:\n";
    echo "   • Admin: admin@apsdreamhome.com / admin123\n";
    echo "   • Agent: rajesh@apsdreamhome.com / agent123\n";
    echo "   • Customer: amit@example.com / customer123\n\n";
    echo "✅ Ready for testing!\n";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
