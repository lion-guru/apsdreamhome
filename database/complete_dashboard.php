<?php
// Script to complete dashboard data for remaining empty widgets

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

// 1. Add data to property_visits and visit_reminders
echo "Checking property_visits table...\n";
$result = $conn->query("SHOW TABLES LIKE 'property_visits'");
if($result && $result->num_rows > 0) {
    echo "property_visits table exists\n";
    
    // Create visit_reminders table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS property_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        property_id INT,
        visit_date DATE,
        visit_time TIME,
        status VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add visit data
    $conn->query("INSERT IGNORE INTO property_visits (id, customer_id, property_id, visit_date, visit_time, status) VALUES
        (1, 1, 1, '2025-05-20', '14:00:00', 'scheduled'),
        (2, 2, 2, '2025-05-21', '11:00:00', 'scheduled'),
        (3, 3, 3, '2025-05-22', '16:00:00', 'scheduled')
    ");
    echo "Added visit data\n";
    
    // Create visit_reminders table if it doesn't exist
    $conn->query("CREATE TABLE IF NOT EXISTS visit_reminders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_id INT,
        reminder_type VARCHAR(50),
        status VARCHAR(50),
        scheduled_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add reminder data
    $conn->query("INSERT IGNORE INTO visit_reminders (visit_id, reminder_type, status, scheduled_at) VALUES
        (1, '24h', 'pending', '2025-05-19 14:00:00'),
        (2, '24h', 'pending', '2025-05-20 11:00:00'),
        (3, '24h', 'pending', '2025-05-21 16:00:00')
    ");
    echo "Added visit reminders\n";
} else {
    echo "property_visits table does not exist, creating...\n";
    
    // Create property_visits table
    $conn->query("CREATE TABLE IF NOT EXISTS property_visits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        property_id INT,
        visit_date DATE,
        visit_time TIME,
        status VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add visit data
    $conn->query("INSERT INTO property_visits (customer_id, property_id, visit_date, visit_time, status) VALUES
        (1, 1, '2025-05-20', '14:00:00', 'scheduled'),
        (2, 2, '2025-05-21', '11:00:00', 'scheduled'),
        (3, 3, '2025-05-22', '16:00:00', 'scheduled')
    ");
    
    // Create visit_reminders table
    $conn->query("CREATE TABLE IF NOT EXISTS visit_reminders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visit_id INT,
        reminder_type VARCHAR(50),
        status VARCHAR(50),
        scheduled_at DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add reminder data
    $conn->query("INSERT INTO visit_reminders (visit_id, reminder_type, status, scheduled_at) VALUES
        (1, '24h', 'pending', '2025-05-19 14:00:00'),
        (2, '24h', 'pending', '2025-05-20 11:00:00'),
        (3, '24h', 'pending', '2025-05-21 16:00:00')
    ");
    echo "Created property_visits and visit_reminders tables with data\n";
}

// 2. Add data to notifications
echo "Checking notifications table...\n";
$result = $conn->query("SHOW TABLES LIKE 'notifications'");
if($result && $result->num_rows > 0) {
    echo "notifications table exists\n";
    
    // Add notification data
    $conn->query("INSERT IGNORE INTO notifications (id, user_id, type, title, message, status, created_at) VALUES
        (1, 1, 'system', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', NOW()),
        (2, 1, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Customer One.', 'unread', NOW()),
        (3, 1, 'visit', 'Visit Scheduled: City Apartment', 'Customer Two has scheduled a visit for City Apartment on 2025-05-21 at 11:00.', 'unread', NOW())
    ");
    echo "Added notification data\n";
} else {
    echo "notifications table does not exist, creating...\n";
    
    // Create notifications table
    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type VARCHAR(50),
        title VARCHAR(255),
        message TEXT,
        status VARCHAR(50) DEFAULT 'unread',
        link VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add notification data
    $conn->query("INSERT INTO notifications (user_id, type, title, message, status, created_at) VALUES
        (1, 'system', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread', NOW()),
        (1, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Customer One.', 'unread', NOW()),
        (1, 'visit', 'Visit Scheduled: City Apartment', 'Customer Two has scheduled a visit for City Apartment on 2025-05-21 at 11:00.', 'unread', NOW())
    ");
    echo "Created notifications table with data\n";
}

// 3. Update leads with more complete data
echo "Updating leads with more complete data...\n";
$conn->query("UPDATE leads SET 
    name = CASE 
        WHEN id = 1 THEN 'Rahul Sharma'
        WHEN id = 2 THEN 'Priya Singh'
        WHEN id = 3 THEN 'Amit Kumar'
        WHEN id = 4 THEN 'Neha Patel'
        WHEN id = 5 THEN 'Vikram Mehta'
        WHEN id = 6 THEN 'Anjali Gupta'
        WHEN id = 7 THEN 'Rajesh Verma'
        WHEN id = 8 THEN 'Sunita Jain'
        WHEN id = 9 THEN 'Deepak Sharma'
        WHEN id = 10 THEN 'Kavita Singh'
        ELSE 'Customer'
    END,
    email = CASE
        WHEN id = 1 THEN 'rahul@example.com'
        WHEN id = 2 THEN 'priya@example.com'
        WHEN id = 3 THEN 'amit@example.com'
        WHEN id = 4 THEN 'neha@example.com'
        WHEN id = 5 THEN 'vikram@example.com'
        WHEN id = 6 THEN 'anjali@example.com'
        WHEN id = 7 THEN 'rajesh@example.com'
        WHEN id = 8 THEN 'sunita@example.com'
        WHEN id = 9 THEN 'deepak@example.com'
        WHEN id = 10 THEN 'kavita@example.com'
        ELSE 'customer@example.com'
    END,
    phone = CASE
        WHEN id = 1 THEN '9876543210'
        WHEN id = 2 THEN '9876543211'
        WHEN id = 3 THEN '9876543212'
        WHEN id = 4 THEN '9876543213'
        WHEN id = 5 THEN '9876543214'
        WHEN id = 6 THEN '9876543215'
        WHEN id = 7 THEN '9876543216'
        WHEN id = 8 THEN '9876543217'
        WHEN id = 9 THEN '9876543218'
        WHEN id = 10 THEN '9876543219'
        ELSE '9876543200'
    END,
    status = CASE
        WHEN id % 5 = 0 THEN 'closed_won'
        WHEN id % 5 = 1 THEN 'new'
        WHEN id % 5 = 2 THEN 'contacted'
        WHEN id % 5 = 3 THEN 'qualified'
        WHEN id % 5 = 4 THEN 'negotiation'
        ELSE 'new'
    END,
    source = CASE
        WHEN id % 4 = 0 THEN 'website'
        WHEN id % 4 = 1 THEN 'referral'
        WHEN id % 4 = 2 THEN 'direct'
        WHEN id % 4 = 3 THEN 'other'
        ELSE 'website'
    END
WHERE id BETWEEN 1 AND 10");
echo "Updated leads data\n";

// Close connection
$conn->close();
echo "Dashboard completion finished\n";
echo "Your dashboard should now be fully populated with data in all widgets\n";
?>
