<?php
// Comprehensive script to fill all tables in apsdreamhomefinal database with demo data

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

// Get all tables in the database
$tables = array();
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    echo "Found " . count($tables) . " tables in database\n";
} else {
    echo "Error getting tables: " . $conn->error . "\n";
}

// Function to get table columns
function getTableColumns($conn, $tableName) {
    $columns = array();
    $sql = "SHOW COLUMNS FROM $tableName";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }
    
    return $columns;
}

// Function to check if a table has an auto-increment primary key
function hasAutoIncrementPK($conn, $tableName) {
    $sql = "SHOW COLUMNS FROM $tableName WHERE `Key` = 'PRI' AND Extra LIKE '%auto_increment%'";
    $result = $conn->query($sql);
    return ($result && $result->num_rows > 0);
}

// Process each table
foreach ($tables as $table) {
    echo "\nProcessing table: $table\n";
    
    // Get columns
    $columns = getTableColumns($conn, $table);
    echo "Columns: " . implode(", ", $columns) . "\n";
    
    // Check if table already has data
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    echo "Current record count: $count\n";
    
    // Only add data if table has fewer than 5 records
    if ($count < 5) {
        echo "Adding demo data to $table\n";
        
        // Different handling based on table name
        switch ($table) {
            case 'users':
                $sql = "INSERT IGNORE INTO users (name, email, password, phone, type, status) VALUES
                    ('Admin User', 'admin@apsdreamhome.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000001', 'admin', 'active'),
                    ('Agent Smith', 'agent@apsdreamhome.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000002', 'agent', 'active'),
                    ('John Doe', 'john@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000003', 'user', 'active'),
                    ('Jane Smith', 'jane@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000004', 'user', 'active'),
                    ('Raj Kumar', 'raj@example.com', '$2y$10$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000005', 'agent', 'active')";
                break;
                
            case 'properties':
                if (in_array('title', $columns)) {
                    $sql = "INSERT IGNORE INTO properties (title, description, address, price) VALUES
                        ('Luxury Villa', 'Beautiful luxury villa with garden', 'Delhi Premium Enclave', 15000000),
                        ('City Apartment', 'Modern apartment in city center', 'Mumbai Heights', 7000000),
                        ('Suburban House', 'Spacious family home', 'Bangalore Green Valley', 9000000),
                        ('Beach Property', 'Beachfront luxury home', 'Goa Seaside Lane', 20000000),
                        ('Penthouse', 'Luxury penthouse with terrace', 'Mumbai Skyline Tower', 25000000)";
                } else if (in_array('name', $columns)) {
                    $sql = "INSERT IGNORE INTO properties (name, description, address, price) VALUES
                        ('Luxury Villa', 'Beautiful luxury villa with garden', 'Delhi Premium Enclave', 15000000),
                        ('City Apartment', 'Modern apartment in city center', 'Mumbai Heights', 7000000),
                        ('Suburban House', 'Spacious family home', 'Bangalore Green Valley', 9000000),
                        ('Beach Property', 'Beachfront luxury home', 'Goa Seaside Lane', 20000000),
                        ('Penthouse', 'Luxury penthouse with terrace', 'Mumbai Skyline Tower', 25000000)";
                } else {
                    // If no name/title column, just use ID
                    $sql = "INSERT IGNORE INTO properties (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'customers':
                if (in_array('name', $columns)) {
                    $sql = "INSERT IGNORE INTO customers (name, email, phone, address) VALUES
                        ('Rahul Sharma', 'rahul@example.com', '9876543210', 'Delhi'),
                        ('Priya Singh', 'priya@example.com', '9876543211', 'Mumbai'),
                        ('Amit Kumar', 'amit@example.com', '9876543212', 'Bangalore'),
                        ('Neha Patel', 'neha@example.com', '9876543213', 'Ahmedabad'),
                        ('Vikram Mehta', 'vikram@example.com', '9876543214', 'Pune')";
                } else if (in_array('first_name', $columns) && in_array('last_name', $columns)) {
                    $sql = "INSERT IGNORE INTO customers (first_name, last_name, email, phone, address) VALUES
                        ('Rahul', 'Sharma', 'rahul@example.com', '9876543210', 'Delhi'),
                        ('Priya', 'Singh', 'priya@example.com', '9876543211', 'Mumbai'),
                        ('Amit', 'Kumar', 'amit@example.com', '9876543212', 'Bangalore'),
                        ('Neha', 'Patel', 'neha@example.com', '9876543213', 'Ahmedabad'),
                        ('Vikram', 'Mehta', 'vikram@example.com', '9876543214', 'Pune')";
                } else {
                    // If no name columns, just use ID
                    $sql = "INSERT IGNORE INTO customers (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'leads':
                if (in_array('name', $columns) && in_array('email', $columns)) {
                    $sql = "INSERT IGNORE INTO leads (name, email, phone, status, source) VALUES
                        ('Rahul Sharma', 'rahul@example.com', '9876543210', 'new', 'website'),
                        ('Priya Singh', 'priya@example.com', '9876543211', 'contacted', 'referral'),
                        ('Amit Kumar', 'amit@example.com', '9876543212', 'qualified', 'direct'),
                        ('Neha Patel', 'neha@example.com', '9876543213', 'negotiation', 'website'),
                        ('Vikram Mehta', 'vikram@example.com', '9876543214', 'closed_won', 'other')";
                } else if (in_array('customer_id', $columns) && in_array('property_id', $columns)) {
                    $sql = "INSERT IGNORE INTO leads (customer_id, property_id, source, status) VALUES
                        (1, 1, 'website', 'new'),
                        (2, 2, 'referral', 'contacted'),
                        (3, 3, 'direct', 'qualified'),
                        (4, 4, 'website', 'negotiation'),
                        (5, 5, 'other', 'closed_won')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO leads (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'bookings':
                if (in_array('property_id', $columns) && in_array('user_id', $columns)) {
                    $sql = "INSERT IGNORE INTO bookings (user_id, property_id, booking_date, amount, status) VALUES
                        (1, 1, '2025-05-01', 1500000, 'confirmed'),
                        (2, 2, '2025-05-02', 700000, 'pending'),
                        (3, 3, '2025-05-05', 900000, 'confirmed'),
                        (4, 4, '2025-05-07', 2000000, 'confirmed'),
                        (5, 5, '2025-05-10', 2500000, 'pending')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO bookings (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'transactions':
                if (in_array('amount', $columns) && in_array('user_id', $columns)) {
                    $sql = "INSERT IGNORE INTO transactions (user_id, amount, date) VALUES
                        (1, 1500000, '2025-05-01'),
                        (2, 3500000, '2025-05-10'),
                        (3, 700000, '2025-05-02'),
                        (4, 1500000, '2025-05-12'),
                        (5, 900000, '2025-05-05')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO transactions (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'property_visits':
                if (in_array('property_id', $columns) && in_array('customer_id', $columns)) {
                    $sql = "INSERT IGNORE INTO property_visits (customer_id, property_id, visit_date, visit_time, status) VALUES
                        (1, 1, '2025-05-20', '14:00:00', 'scheduled'),
                        (2, 2, '2025-05-21', '11:00:00', 'scheduled'),
                        (3, 3, '2025-05-22', '16:00:00', 'scheduled'),
                        (4, 4, '2025-05-15', '10:00:00', 'completed'),
                        (5, 5, '2025-05-16', '15:00:00', 'cancelled')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO property_visits (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'visit_reminders':
                if (in_array('visit_id', $columns)) {
                    $sql = "INSERT IGNORE INTO visit_reminders (visit_id, reminder_type, status, scheduled_at) VALUES
                        (1, '24h', 'pending', '2025-05-19 14:00:00'),
                        (2, '24h', 'pending', '2025-05-20 11:00:00'),
                        (3, '24h', 'pending', '2025-05-21 16:00:00'),
                        (4, '24h', 'sent', '2025-05-14 10:00:00'),
                        (5, '24h', 'cancelled', '2025-05-15 15:00:00')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO visit_reminders (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'notifications':
                if (in_array('title', $columns) && in_array('message', $columns)) {
                    $sql = "INSERT IGNORE INTO notifications (user_id, type, title, message, status) VALUES
                        (1, 'system', 'Welcome to APS Dream Home', 'Welcome to your admin dashboard. Start managing your real estate business.', 'unread'),
                        (1, 'lead', 'New Lead: Luxury Villa', 'You have received a new lead for Luxury Villa from Rahul Sharma.', 'unread'),
                        (1, 'visit', 'Visit Scheduled: City Apartment', 'Priya Singh has scheduled a visit for City Apartment on 2025-05-21 at 11:00.', 'unread'),
                        (1, 'lead', 'Lead Status Updated: City Apartment', 'The lead for City Apartment has been updated to contacted.', 'read'),
                        (1, 'visit', 'Visit Cancelled: Luxury Villa', 'The visit for Luxury Villa on 2025-05-16 has been cancelled.', 'read')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO notifications (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'feedback':
                if (in_array('rating', $columns) && in_array('feedback_text', $columns)) {
                    $sql = "INSERT IGNORE INTO feedback (user_id, property_id, rating, feedback_text) VALUES
                        (1, 1, 5, 'Excellent property with amazing amenities. The villa exceeded our expectations.'),
                        (2, 2, 4, 'Great apartment in a convenient location. Modern amenities and good value.'),
                        (3, 3, 5, 'Perfect family home in a quiet neighborhood. Very satisfied with the property.'),
                        (4, 4, 4, 'Beautiful beachfront property with stunning views. Minor maintenance issues.'),
                        (5, 5, 5, 'Luxurious penthouse with excellent city views. Perfect in every way.')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO feedback (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'gallery':
                if (in_array('property_id', $columns) && in_array('image_url', $columns)) {
                    $sql = "INSERT IGNORE INTO gallery (property_id, image_url, caption) VALUES
                        (1, 'images/properties/villa1.jpg', 'Front view of luxury villa'),
                        (1, 'images/properties/villa2.jpg', 'Swimming pool area'),
                        (2, 'images/properties/apartment1.jpg', 'Modern living room'),
                        (2, 'images/properties/apartment2.jpg', 'Kitchen with premium appliances'),
                        (3, 'images/properties/house1.jpg', 'Spacious garden area')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO gallery (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'testimonials':
                if (in_array('testimonial_text', $columns) && in_array('rating', $columns)) {
                    $sql = "INSERT IGNORE INTO testimonials (user_id, testimonial_text, rating) VALUES
                        (1, 'I found my dream home through APS Dream Home! The entire process was smooth and professional.', 5),
                        (2, 'Great experience working with the APS Dream Home team. They understood our requirements perfectly.', 5),
                        (3, 'Excellent service and a wide range of properties to choose from. Highly recommended!', 4),
                        (4, 'The team was very responsive and helped us find the perfect property within our budget.', 5),
                        (5, 'Professional service from start to finish. Will definitely use APS Dream Home again.', 5)";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO testimonials (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            case 'mlm_commissions':
                if (in_array('commission_amount', $columns)) {
                    $sql = "INSERT IGNORE INTO mlm_commissions (user_id, user_name, transaction_id, property_id, commission_amount, commission_type) VALUES
                        (2, 'Agent Smith', 1, 1, 150000.00, 'sales'),
                        (3, 'John Doe', 2, 2, 75000.00, 'referral'),
                        (4, 'Jane Smith', 3, 3, 90000.00, 'sales'),
                        (5, 'Raj Kumar', 4, 1, 45000.00, 'referral'),
                        (6, 'Anita Desai', 5, 2, 60000.00, 'sales')";
                } else {
                    // If no specific columns, just use ID
                    $sql = "INSERT IGNORE INTO mlm_commissions (id) VALUES (1), (2), (3), (4), (5)";
                }
                break;
                
            default:
                // For any other table, just add IDs if it has an auto-increment primary key
                if (hasAutoIncrementPK($conn, $table)) {
                    $sql = "INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5)";
                } else {
                    echo "Skipping table $table as it doesn't have an auto-increment primary key\n";
                    continue;
                }
                break;
        }
        
        // Execute the query
        if($conn->query($sql) === TRUE) {
            echo "Data added successfully to $table\n";
        } else {
            echo "Error adding data to $table: " . $conn->error . "\n";
            
            // Try a minimal approach if the specific approach failed
            if (hasAutoIncrementPK($conn, $table)) {
                $sql = "INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5)";
                if($conn->query($sql) === TRUE) {
                    echo "Minimal data added successfully to $table\n";
                } else {
                    echo "Error adding minimal data to $table: " . $conn->error . "\n";
                }
            }
        }
    } else {
        echo "Table $table already has data ($count records), skipping\n";
    }
}

// Close connection
$conn->close();
echo "\nDatabase seeding complete. All tables should now have data.\n";
?>
