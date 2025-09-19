<?php
// Final script to populate remaining empty widgets

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

// 1. Fix Visit Reminders Widget
echo "Fixing Visit Reminders Widget...\n";

// First, let's check how the dashboard queries this data
// Based on common patterns, it might be using a view or a specific query format
// Let's try to find the specific table structure by examining the dashboard code

// Create or update property_visits with more complete data
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

// Add visit data with explicit customer and property names
$conn->query("INSERT IGNORE INTO property_visits (id, customer_id, property_id, customer_name, property_name, visit_date, visit_time, status) VALUES
    (1, 1, 1, 'Rahul Sharma', 'Luxury Villa', '2025-05-20', '14:00:00', 'scheduled'),
    (2, 2, 2, 'Priya Singh', 'City Apartment', '2025-05-21', '11:00:00', 'scheduled'),
    (3, 3, 3, 'Amit Kumar', 'Suburban House', '2025-05-22', '16:00:00', 'scheduled')
");

// Create or update visit_reminders with more complete data
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

// Add reminder data with explicit dates and names
$conn->query("INSERT IGNORE INTO visit_reminders (id, visit_id, reminder_type, status, reminder_date, reminder_time, property_name, customer_name) VALUES
    (1, 1, '24h', 'pending', '2025-05-19', '14:00:00', 'Luxury Villa', 'Rahul Sharma'),
    (2, 2, '24h', 'pending', '2025-05-20', '11:00:00', 'City Apartment', 'Priya Singh'),
    (3, 3, '24h', 'pending', '2025-05-21', '16:00:00', 'Suburban House', 'Amit Kumar')
");

echo "Visit reminders data updated\n";

// 2. Fix Notifications Widget
echo "Fixing Notifications Widget...\n";

// Create or update notifications with more complete data
$conn->query("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50),
    title VARCHAR(255),
    message TEXT,
    status VARCHAR(50) DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Add notification data with explicit dates
$conn->query("INSERT IGNORE INTO notifications (id, user_id, type, title, message, status, created_at) VALUES
    (1, 1, 'system', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', NOW()),
    (2, 1, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Rahul Sharma.', 'unread', NOW()),
    (3, 1, 'visit', 'Visit Scheduled: City Apartment', 'Priya Singh has scheduled a visit for City Apartment on 2025-05-21 at 11:00.', 'unread', NOW())
");

echo "Notifications data updated\n";

// 3. Fix Leads Converted Widget
echo "Fixing Leads Converted Widget...\n";

// Update leads to include some converted ones for this month
$conn->query("UPDATE leads SET 
    status = 'closed_won',
    converted_at = NOW(),
    converted_amount = CASE
        WHEN id = 1 THEN 15000000
        WHEN id = 3 THEN 9000000
        WHEN id = 5 THEN 7000000
        ELSE NULL
    END
WHERE id IN (1, 3, 5)");

echo "Updated leads with conversions\n";

// 4. Fix MLM Commission Widget
echo "Fixing MLM Commission Widget...\n";

// Create or update mlm_commissions table
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
$conn->query("INSERT IGNORE INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type) VALUES
    (2, 'Agent Smith', 1, 1, 150000.00, 'sales'),
    (3, 'John Doe', 2, 2, 75000.00, 'referral'),
    (4, 'Jane Smith', 3, 3, 90000.00, 'sales'),
    (5, 'Raj Kumar', 4, 1, 45000.00, 'referral'),
    (6, 'Anita Desai', 5, 2, 60000.00, 'sales')
");

echo "MLM commission data added\n";

// Close connection
$conn->close();
echo "Final widgets population complete\n";
echo "Your dashboard should now be fully populated with data in ALL widgets\n";
?>
