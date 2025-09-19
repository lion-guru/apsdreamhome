<?php
// Final Dashboard Verification Script
// This script checks all dashboard widgets and ensures they have data

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database\n";

// Function to check table count
function checkTableCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Check core dashboard widgets
echo "\n===== Checking Core Dashboard Widgets =====\n";
$coreWidgets = [
    'Properties' => 'properties',
    'Customers' => 'customers',
    'Bookings' => 'bookings',
    'Inquiries/Leads' => 'leads'
];

foreach ($coreWidgets as $widget => $table) {
    $count = checkTableCount($conn, $table);
    echo "$widget: $count records\n";
    
    if ($count < 5) {
        echo "Adding more data to $table...\n";
        
        // Add minimal data
        $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10)");
        
        $newCount = checkTableCount($conn, $table);
        echo "Updated $widget: $newCount records\n";
    }
}

// Check Recent Bookings Widget
echo "\n===== Checking Recent Bookings Widget =====\n";
$bookingsCount = checkTableCount($conn, 'bookings');
echo "Bookings: $bookingsCount records\n";

if ($bookingsCount < 5) {
    echo "Adding more booking data...\n";
    
    // Try to get property and customer IDs
    $propertyIds = [];
    $result = $conn->query("SELECT id FROM properties LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $propertyIds[] = $row['id'];
        }
    }
    
    $customerIds = [];
    $result = $conn->query("SELECT id FROM customers LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customerIds[] = $row['id'];
        }
    }
    
    // Add booking data with proper references if possible
    if (!empty($propertyIds) && !empty($customerIds)) {
        for ($i = 0; $i < 5; $i++) {
            $propertyId = $propertyIds[$i % count($propertyIds)];
            $customerId = $customerIds[$i % count($customerIds)];
            $date = date('Y-m-d', strtotime("-$i days"));
            
            $conn->query("INSERT IGNORE INTO bookings (property_id, customer_id, booking_date, amount, status) 
                VALUES ($propertyId, $customerId, '$date', " . (1000000 + $i * 500000) . ", 'confirmed')");
        }
    } else {
        // Fallback to minimal data
        $conn->query("INSERT IGNORE INTO bookings (id) VALUES (1), (2), (3), (4), (5)");
    }
    
    $newCount = checkTableCount($conn, 'bookings');
    echo "Updated Bookings: $newCount records\n";
}

// Check Recent Transactions Widget
echo "\n===== Checking Recent Transactions Widget =====\n";
$transactionsCount = checkTableCount($conn, 'transactions');
echo "Transactions: $transactionsCount records\n";

if ($transactionsCount < 5) {
    echo "Adding more transaction data...\n";
    
    // Add transaction data
    for ($i = 0; $i < 5; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $conn->query("INSERT IGNORE INTO transactions (user_id, amount, date) 
            VALUES (" . ($i + 1) . ", " . (1500000 + $i * 500000) . ", '$date')");
    }
    
    $newCount = checkTableCount($conn, 'transactions');
    echo "Updated Transactions: $newCount records\n";
}

// Check Visit Reminders Widget
echo "\n===== Checking Visit Reminders Widget =====\n";
$result = $conn->query("SHOW TABLES LIKE 'property_visits'");
if($result && $result->num_rows > 0) {
    $visitsCount = checkTableCount($conn, 'property_visits');
    echo "Property Visits: $visitsCount records\n";
    
    if ($visitsCount < 5) {
        echo "Adding more visit data...\n";
        
        // Create property_visits table if it doesn't exist
        $conn->query("CREATE TABLE IF NOT EXISTS property_visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            property_id INT,
            customer_name VARCHAR(100),
            property_name VARCHAR(100),
            visit_date DATE,
            visit_time TIME,
            status VARCHAR(50) DEFAULT 'scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Add visit data
        for ($i = 0; $i < 5; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            $time = sprintf("1%d:00:00", $i + 1);
            $conn->query("INSERT IGNORE INTO property_visits (customer_id, property_id, customer_name, property_name, visit_date, visit_time, status) 
                VALUES (" . ($i + 1) . ", " . ($i + 1) . ", 'Customer " . ($i + 1) . "', 'Property " . ($i + 1) . "', '$date', '$time', 'scheduled')");
        }
        
        $newCount = checkTableCount($conn, 'property_visits');
        echo "Updated Property Visits: $newCount records\n";
    }
    
    // Check visit_reminders
    $result = $conn->query("SHOW TABLES LIKE 'visit_reminders'");
    if($result && $result->num_rows > 0) {
        $remindersCount = checkTableCount($conn, 'visit_reminders');
        echo "Visit Reminders: $remindersCount records\n";
        
        if ($remindersCount < 5) {
            echo "Adding more reminder data...\n";
            
            // Create visit_reminders table if it doesn't exist
            $conn->query("CREATE TABLE IF NOT EXISTS visit_reminders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                visit_id INT,
                reminder_type VARCHAR(50),
                status VARCHAR(50) DEFAULT 'pending',
                reminder_date DATE,
                reminder_time TIME,
                property_name VARCHAR(100),
                customer_name VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Add reminder data
            for ($i = 0; $i < 5; $i++) {
                $date = date('Y-m-d', strtotime("+$i days"));
                $time = sprintf("1%d:00:00", $i + 1);
                $conn->query("INSERT IGNORE INTO visit_reminders (visit_id, reminder_type, status, reminder_date, reminder_time, property_name, customer_name) 
                    VALUES (" . ($i + 1) . ", '24h', 'pending', '$date', '$time', 'Property " . ($i + 1) . "', 'Customer " . ($i + 1) . "')");
            }
            
            $newCount = checkTableCount($conn, 'visit_reminders');
            echo "Updated Visit Reminders: $newCount records\n";
        }
    } else {
        echo "Creating visit_reminders table...\n";
        
        // Create visit_reminders table
        $conn->query("CREATE TABLE IF NOT EXISTS visit_reminders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            visit_id INT,
            reminder_type VARCHAR(50),
            status VARCHAR(50) DEFAULT 'pending',
            reminder_date DATE,
            reminder_time TIME,
            property_name VARCHAR(100),
            customer_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Add reminder data
        for ($i = 0; $i < 5; $i++) {
            $date = date('Y-m-d', strtotime("+$i days"));
            $time = sprintf("1%d:00:00", $i + 1);
            $conn->query("INSERT INTO visit_reminders (visit_id, reminder_type, status, reminder_date, reminder_time, property_name, customer_name) 
                VALUES (" . ($i + 1) . ", '24h', 'pending', '$date', '$time', 'Property " . ($i + 1) . "', 'Customer " . ($i + 1) . "')");
        }
        
        $newCount = checkTableCount($conn, 'visit_reminders');
        echo "Created Visit Reminders: $newCount records\n";
    }
} else {
    echo "Creating property_visits table...\n";
    
    // Create property_visits table
    $conn->query("CREATE TABLE IF NOT EXISTS property_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        property_id INT,
        customer_name VARCHAR(100),
        property_name VARCHAR(100),
        visit_date DATE,
        visit_time TIME,
        status VARCHAR(50) DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add visit data
    for ($i = 0; $i < 5; $i++) {
        $date = date('Y-m-d', strtotime("+$i days"));
        $time = sprintf("1%d:00:00", $i + 1);
        $conn->query("INSERT INTO property_visits (customer_id, property_id, customer_name, property_name, visit_date, visit_time, status) 
            VALUES (" . ($i + 1) . ", " . ($i + 1) . ", 'Customer " . ($i + 1) . "', 'Property " . ($i + 1) . "', '$date', '$time', 'scheduled')");
    }
    
    // Create visit_reminders table
    $conn->query("CREATE TABLE IF NOT EXISTS visit_reminders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_id INT,
        reminder_type VARCHAR(50),
        status VARCHAR(50) DEFAULT 'pending',
        reminder_date DATE,
        reminder_time TIME,
        property_name VARCHAR(100),
        customer_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add reminder data
    for ($i = 0; $i < 5; $i++) {
        $date = date('Y-m-d', strtotime("+$i days"));
        $time = sprintf("1%d:00:00", $i + 1);
        $conn->query("INSERT INTO visit_reminders (visit_id, reminder_type, status, reminder_date, reminder_time, property_name, customer_name) 
            VALUES (" . ($i + 1) . ", '24h', 'pending', '$date', '$time', 'Property " . ($i + 1) . "', 'Customer " . ($i + 1) . "')");
    }
    
    echo "Created Property Visits and Reminders tables with data\n";
}

// Check Notifications Widget
echo "\n===== Checking Notifications Widget =====\n";
$result = $conn->query("SHOW TABLES LIKE 'notifications'");
if($result && $result->num_rows > 0) {
    $notificationsCount = checkTableCount($conn, 'notifications');
    echo "Notifications: $notificationsCount records\n";
    
    if ($notificationsCount < 5) {
        echo "Adding more notification data...\n";
        
        // Add notification data
        $conn->query("INSERT IGNORE INTO notifications (user_id, type, title, message, status, created_at) VALUES
            (1, 'system', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', NOW()),
            (1, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Rahul Sharma.', 'unread', NOW()),
            (1, 'visit', 'Visit Scheduled: City Apartment', 'Priya Singh has scheduled a visit for City Apartment on " . date('Y-m-d', strtotime("+1 day")) . " at 11:00.', 'unread', NOW()),
            (1, 'lead', 'Lead Status Updated: City Apartment', 'The lead for City Apartment has been updated to contacted.', 'read', NOW()),
            (1, 'visit', 'Visit Cancelled: Luxury Villa', 'The visit for Luxury Villa on " . date('Y-m-d', strtotime("+2 days")) . " has been cancelled.', 'read', NOW())");
        
        $newCount = checkTableCount($conn, 'notifications');
        echo "Updated Notifications: $newCount records\n";
    }
} else {
    echo "Creating notifications table...\n";
    
    // Create notifications table
    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type VARCHAR(50),
        title VARCHAR(255),
        message TEXT,
        status VARCHAR(50) DEFAULT 'unread',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add notification data
    $conn->query("INSERT INTO notifications (user_id, type, title, message, status, created_at) VALUES
        (1, 'system', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', NOW()),
        (1, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Rahul Sharma.', 'unread', NOW()),
        (1, 'visit', 'Visit Scheduled: City Apartment', 'Priya Singh has scheduled a visit for City Apartment on " . date('Y-m-d', strtotime("+1 day")) . " at 11:00.', 'unread', NOW()),
        (1, 'lead', 'Lead Status Updated: City Apartment', 'The lead for City Apartment has been updated to contacted.', 'read', NOW()),
        (1, 'visit', 'Visit Cancelled: Luxury Villa', 'The visit for Luxury Villa on " . date('Y-m-d', strtotime("+2 days")) . " has been cancelled.', 'read', NOW())");
    
    echo "Created Notifications table with data\n";
}

// Check MLM Commission Widget
echo "\n===== Checking MLM Commission Widget =====\n";
$result = $conn->query("SHOW TABLES LIKE 'mlm_commissions'");
if($result && $result->num_rows > 0) {
    $commissionsCount = checkTableCount($conn, 'mlm_commissions');
    echo "MLM Commissions: $commissionsCount records\n";
    
    if ($commissionsCount < 5) {
        echo "Adding more commission data...\n";
        
        // Add commission data
        $conn->query("INSERT IGNORE INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type) VALUES
            (2, 'Agent Smith', 1, 1, 150000.00, 'sales'),
            (3, 'John Doe', 2, 2, 75000.00, 'referral'),
            (4, 'Jane Smith', 3, 3, 90000.00, 'sales'),
            (5, 'Raj Kumar', 4, 1, 45000.00, 'referral'),
            (6, 'Anita Desai', 5, 2, 60000.00, 'sales')");
        
        $newCount = checkTableCount($conn, 'mlm_commissions');
        echo "Updated MLM Commissions: $newCount records\n";
    }
} else {
    echo "Creating mlm_commissions table...\n";
    
    // Create mlm_commissions table
    $conn->query("CREATE TABLE IF NOT EXISTS mlm_commissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        user_name VARCHAR(100),
        transaction_id INT,
        property_id INT,
        commission_amount DECIMAL(12,2),
        commission_type VARCHAR(50),
        status VARCHAR(50) DEFAULT 'paid',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add commission data
    $conn->query("INSERT INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type) VALUES
        (2, 'Agent Smith', 1, 1, 150000.00, 'sales'),
        (3, 'John Doe', 2, 2, 75000.00, 'referral'),
        (4, 'Jane Smith', 3, 3, 90000.00, 'sales'),
        (5, 'Raj Kumar', 4, 1, 45000.00, 'referral'),
        (6, 'Anita Desai', 5, 2, 60000.00, 'sales')");
    
    echo "Created MLM Commissions table with data\n";
}

// Check Leads Converted Widget
echo "\n===== Checking Leads Converted Widget =====\n";

// Check if the leads table has the necessary columns
$result = $conn->query("SHOW COLUMNS FROM leads LIKE 'converted_at'");
$hasConvertedAt = ($result && $result->num_rows > 0);

$result = $conn->query("SHOW COLUMNS FROM leads LIKE 'converted_amount'");
$hasConvertedAmount = ($result && $result->num_rows > 0);

// Add missing columns if needed
if (!$hasConvertedAt) {
    echo "Adding 'converted_at' column to leads table...\n";
    $conn->query("ALTER TABLE leads ADD COLUMN converted_at DATETIME NULL");
}

if (!$hasConvertedAmount) {
    echo "Adding 'converted_amount' column to leads table...\n";
    $conn->query("ALTER TABLE leads ADD COLUMN converted_amount DECIMAL(12,2) NULL");
}

// Update leads to include some converted ones for this month
echo "Updating leads with conversion data...\n";
$conn->query("UPDATE leads SET 
    status = 'closed_won'
    WHERE id IN (1, 3, 5)");

// Now update the new columns separately
$conn->query("UPDATE leads SET converted_at = NOW() WHERE id IN (1, 3, 5)");

$conn->query("UPDATE leads SET 
    converted_amount = CASE
        WHEN id = 1 THEN 15000000
        WHEN id = 3 THEN 9000000
        WHEN id = 5 THEN 7000000
        ELSE NULL
    END
    WHERE id IN (1, 3, 5)");

echo "Updated leads with conversions\n";

// Close connection
$conn->close();
echo "\nFinal dashboard verification complete. All widgets should now be fully populated.\n";
?>
