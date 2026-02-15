<?php
// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// HTML header
echo '<!DOCTYPE html>
<html>
<head>
    <title>Booking Summary View Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Booking Summary View Fix</h1>';

function showMessage($message, $type = 'info') {
    echo "<p class='$type'>$message</p>";
}

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    showMessage("✅ डेटाबेस कनेक्शन सफल!", 'success');
    
    // Check if bookings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'bookings'");
    $bookingsExists = $stmt->rowCount() > 0;
    
    if (!$bookingsExists) {
        showMessage("⚠️ 'bookings' टेबल मौजूद नहीं है। इसे बनाया जा रहा है...", 'warning');
        
        // Create bookings table
        $pdo->exec("
        CREATE TABLE `bookings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `property_id` int(11) NOT NULL,
            `customer_id` int(11) NOT NULL,
            `booking_date` date NOT NULL,
            `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
            `amount` decimal(10,2) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Insert sample data
        $pdo->exec("
        INSERT INTO `bookings` (`property_id`, `customer_id`, `booking_date`, `status`, `amount`) 
        VALUES (1, 1, '2023-05-15', 'confirmed', 50000.00);
        ");
        
        showMessage("✅ 'bookings' टेबल सफलतापूर्वक बनाया गया है।", 'success');
    } else {
        showMessage("✅ 'bookings' टेबल पहले से मौजूद है।", 'success');
    }
    
    // Check if properties table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'properties'");
    $propertiesExists = $stmt->rowCount() > 0;
    
    if (!$propertiesExists) {
        showMessage("⚠️ 'properties' टेबल मौजूद नहीं है। इसे बनाया जा रहा है...", 'warning');
        
        // Create properties table
        $pdo->exec("
        CREATE TABLE `properties` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text DEFAULT NULL,
            `location` varchar(255) NOT NULL,
            `price` decimal(10,2) NOT NULL,
            `status` enum('available','sold','rented') NOT NULL DEFAULT 'available',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Insert sample data
        $pdo->exec("
        INSERT INTO `properties` (`title`, `description`, `location`, `price`, `status`) 
        VALUES ('Luxury Villa', 'Beautiful luxury villa with garden', 'Mumbai', 5000000.00, 'available');
        ");
        
        showMessage("✅ 'properties' टेबल सफलतापूर्वक बनाया गया है।", 'success');
    } else {
        showMessage("✅ 'properties' टेबल पहले से मौजूद है।", 'success');
    }
    
    // Drop the booking_summary view if it exists
    $pdo->exec("DROP VIEW IF EXISTS `booking_summary`");
    showMessage("🔧 'booking_summary' व्यू को ड्रॉप किया गया है।", 'warning');
    
    // Create a very simple booking_summary view that only references tables we know exist
    $createViewSQL = "
    CREATE VIEW `booking_summary` AS
    SELECT 
        b.id AS booking_id,
        b.booking_date,
        b.status AS booking_status,
        b.amount,
        b.customer_id,
        p.id AS property_id,
        p.title AS property_title,
        p.location AS property_location,
        p.price AS property_price
    FROM 
        `bookings` b
    JOIN 
        `properties` p ON b.property_id = p.id
    ";
    
    try {
        $pdo->exec($createViewSQL);
        showMessage("✅ 'booking_summary' व्यू सफलतापूर्वक बनाया गया है।", 'success');
    } catch (PDOException $e) {
        showMessage("❌ 'booking_summary' व्यू बनाने में त्रुटि: " . $e->getMessage(), 'error');
    }
    
    // Test the booking_summary view
    showMessage("<h2>booking_summary व्यू का परीक्षण</h2>");
    
    try {
        $stmt = $pdo->query("SELECT * FROM booking_summary LIMIT 10");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($results) > 0) {
            echo "<table>";
            echo "<tr>";
            foreach (array_keys($results[0]) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            
            foreach ($results as $row) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            
            showMessage("✅ 'booking_summary' व्यू सफलतापूर्वक परीक्षण किया गया है।", 'success');
        } else {
            showMessage("⚠️ 'booking_summary' व्यू में कोई डेटा नहीं मिला।", 'warning');
        }
    } catch (PDOException $e) {
        showMessage("❌ 'booking_summary' व्यू परीक्षण में त्रुटि: " . $e->getMessage(), 'error');
    }
    
} catch (PDOException $e) {
    showMessage("❌ डेटाबेस ऑपरेशन में त्रुटि: " . $e->getMessage(), 'error');
} finally {
    // Summary of fixes
    echo "<h2>समाधान सारांश:</h2>";
    echo "<ul>";
    echo "<li>booking_summary व्यू को सरल बनाया गया है</li>";
    echo "<li>अमान्य टेबल और कॉलम संदर्भों को हटाया गया है</li>";
    echo "<li>आवश्यक टेबल्स की जांच की गई है और यदि आवश्यक हो तो बनाए गए हैं</li>";
    echo "</ul>";
    
    echo "<p><a href='check_database.php'>डेटाबेस जांच रिपोर्ट देखें</a></p>";
    echo "<p><a href='index.php'>होम पेज पर वापस जाएं</a></p>";
}

echo '</body></html>';
?>