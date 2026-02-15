<?php
// Database connection details
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Function to display messages
function showMessage($message, $type = 'info') {
    $color = 'black';
    if ($type == 'success') {
        $color = 'green';
    } elseif ($type == 'error') {
        $color = 'red';
    } elseif ($type == 'warning') {
        $color = 'orange';
    }
    
    echo "<div style='color: $color; margin: 10px 0;'>$message</div>";
}

// HTML header
echo '<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>डेटाबेस फिक्स</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1, h2, h3 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
    </style>
</head>
<body>
    <h1>डेटाबेस फिक्स रिपोर्ट</h1>';

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    showMessage("✅ डेटाबेस कनेक्शन सफल!", 'success');
} catch (PDOException $e) {
    showMessage("❌ डेटाबेस कनेक्शन विफल: " . $e->getMessage(), 'error');
    echo '</body></html>';
    exit;
}

// Disable foreign key checks first
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    showMessage("फॉरेन की चेक अस्थायी रूप से बंद किए गए हैं।", 'warning');
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $usersTableExists = $stmt->rowCount() > 0;
    
    if ($usersTableExists) {
        showMessage("'users' टेबल पहले से मौजूद है। इसे ड्रॉप करके फिर से बनाएंगे।", 'warning');
        
        // Drop the users table
        $pdo->exec("DROP TABLE IF EXISTS `users`");
        showMessage("'users' टेबल सफलतापूर्वक हटा दिया गया है।", 'success');
    }
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
    showMessage("Foreign key checks temporarily disabled.", 'warning');

    // Create the users table
    showMessage("🔧 'users' टेबल बना रहे हैं...", 'info');
    
    try {
        $createUsersTable = "
        CREATE TABLE `users` (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `profile_picture` varchar(255) DEFAULT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `type` enum('admin','agent','customer','employee') NOT NULL DEFAULT 'customer',
          `password` varchar(255) NOT NULL,
          `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `api_access` tinyint(1) DEFAULT 0,
          `api_rate_limit` int(11) DEFAULT 100,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($createUsersTable);
        showMessage("✅ 'users' टेबल सफलतापूर्वक बनाया गया है।", 'success');
    } catch (PDOException $e) {
        showMessage("❌ डेटाबेस ऑपरेशन में त्रुटि: " . $e->getMessage(), 'error');
    }
    
    // Insert some sample data
    $insertSampleData = "
    INSERT INTO `users` (`id`, `name`, `email`, `profile_picture`, `phone`, `type`, `password`, `status`, `created_at`, `updated_at`, `api_access`, `api_rate_limit`) VALUES
    (1, 'Admin User', 'admin@example.com', NULL, '9876543210', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2023-01-01 00:00:00', '2023-01-01 00:00:00', 1, 1000),
    (2, 'Agent User', 'agent@example.com', NULL, '9876543211', 'agent', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2023-01-01 00:00:00', '2023-01-01 00:00:00', 0, 100),
    (3, 'Customer User', 'customer@example.com', NULL, '9876543212', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2023-01-01 00:00:00', '2023-01-01 00:00:00', 0, 100),
    (4, 'Employee User', 'employee@example.com', NULL, '9876543213', 'employee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active', '2023-01-01 00:00:00', '2023-01-01 00:00:00', 0, 100);
    ";
    
    $pdo->exec($insertSampleData);
    showMessage("✅ सैंपल डेटा सफलतापूर्वक जोड़ा गया है।", 'success');
    
    // Check if required tables for the view exist
    $requiredTables = ['bookings', 'customers', 'properties', 'associates'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        showMessage("❌ निम्नलिखित आवश्यक टेबल्स मौजूद नहीं हैं: " . implode(', ', $missingTables), 'warning');
        
        // Create missing tables with basic structure
        if (in_array('customers', $missingTables)) {
            showMessage("🔧 'customers' टेबल बना रहे हैं...");
            try {
                $pdo->exec("
                CREATE TABLE `customers` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `user_id` bigint(20) UNSIGNED NOT NULL,
                  `address` text,
                  `city` varchar(100) DEFAULT NULL,
                  `state` varchar(100) DEFAULT NULL,
                  `pincode` varchar(20) DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
                
                // Add foreign key separately after table creation
                $pdo->exec("ALTER TABLE `customers` ADD KEY `user_id` (`user_id`)");
                $pdo->exec("ALTER TABLE `customers` ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)");
                
                showMessage("✅ 'customers' टेबल सफलतापूर्वक बनाया गया है।", 'success');
                
                // Insert sample customer data
                $pdo->exec("
                INSERT INTO `customers` (`user_id`, `address`, `city`, `state`, `pincode`) 
                VALUES (3, 'Sample Address', 'Sample City', 'Sample State', '123456');
                ");
                showMessage("✅ सैंपल कस्टमर डेटा जोड़ा गया है।", 'success');
            } catch (PDOException $e) {
                showMessage("❌ customers टेबल बनाने में त्रुटि: " . $e->getMessage(), 'error');
            }
        }
        
        if (in_array('properties', $missingTables)) {
            showMessage("🔧 'properties' टेबल बना रहे हैं...");
            try {
                $pdo->exec("
                CREATE TABLE `properties` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) NOT NULL,
                  `description` text,
                  `price` decimal(10,2) NOT NULL,
                  `location` varchar(255) NOT NULL,
                  `type` enum('apartment','house','land','commercial') NOT NULL,
                  `status` enum('available','sold','booked') NOT NULL DEFAULT 'available',
                  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
                  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
                
                // Add foreign keys separately after table creation
                $pdo->exec("ALTER TABLE `properties` ADD KEY `created_by` (`created_by`)");
                $pdo->exec("ALTER TABLE `properties` ADD KEY `updated_by` (`updated_by`)");
                $pdo->exec("ALTER TABLE `properties` ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL");
                $pdo->exec("ALTER TABLE `properties` ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL");
                
            showMessage("✅ 'properties' टेबल सफलतापूर्वक बनाया गया है।", 'success');
            
            // Insert sample property data
            $pdo->exec("
            INSERT INTO `properties` (`title`, `description`, `price`, `location`, `type`, `status`, `created_by`) 
            VALUES ('Sample Property', 'This is a sample property description', 1000000.00, 'Sample Location', 'apartment', 'available', 2);
            ");
            showMessage("✅ सैंपल प्रॉपर्टी डेटा जोड़ा गया है।", 'success');
            } catch (PDOException $e) {
                showMessage("❌ properties टेबल बनाने में त्रुटि: " . $e->getMessage(), 'error');
            }
        }
        
        if (in_array('associates', $missingTables)) {
            showMessage("🔧 'associates' टेबल बना रहे हैं...");
            try {
                $pdo->exec("
                CREATE TABLE `associates` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `user_id` bigint(20) UNSIGNED NOT NULL,
                  `company_name` varchar(255) DEFAULT NULL,
                  `registration_number` varchar(100) DEFAULT NULL,
                  `commission_rate` decimal(5,2) DEFAULT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
                
                // Add foreign key separately
                $pdo->exec("ALTER TABLE `associates` ADD KEY `user_id` (`user_id`)");
                $pdo->exec("ALTER TABLE `associates` ADD CONSTRAINT `fk_associate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
                
                showMessage("✅ 'associates' टेबल सफलतापूर्वक बनाया गया है।", 'success');
                
                // Insert sample associate data
                $pdo->exec("
                INSERT INTO `associates` (`user_id`, `company_name`, `registration_number`, `commission_rate`) 
                VALUES (2, 'Sample Company', 'REG123456', 5.00);
                ");
                showMessage("✅ सैंपल एसोसिएट डेटा जोड़ा गया है।", 'success');
            } catch (PDOException $e) {
                showMessage("❌ associates टेबल बनाने में त्रुटि: " . $e->getMessage(), 'error');
            }
        }
        
        if (in_array('bookings', $missingTables)) {
            showMessage("🔧 'bookings' टेबल बना रहे हैं...");
            try {
                $pdo->exec("
                CREATE TABLE `bookings` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `property_id` bigint(20) UNSIGNED NOT NULL,
                  `customer_id` bigint(20) UNSIGNED NOT NULL,
                  `booking_date` date NOT NULL,
                  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
                  `amount` decimal(10,2) NOT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
                
                // Add foreign keys separately
                $pdo->exec("ALTER TABLE `bookings` ADD KEY `property_id` (`property_id`)");
                $pdo->exec("ALTER TABLE `bookings` ADD KEY `customer_id` (`customer_id`)");
                $pdo->exec("ALTER TABLE `bookings` ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE");
                $pdo->exec("ALTER TABLE `bookings` ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE");
                
            showMessage("✅ 'bookings' टेबल सफलतापूर्वक बनाया गया है।", 'success');
            
            // Insert sample booking data
            $pdo->exec("
            INSERT INTO `bookings` (`property_id`, `customer_id`, `booking_date`, `status`, `amount`) 
            VALUES (1, 3, CURDATE(), 'confirmed', 50000.00);
            ");
            showMessage("✅ सैंपल बुकिंग डेटा जोड़ा गया है।", 'success');
            } catch (PDOException $e) {
                showMessage("❌ bookings टेबल बनाने में त्रुटि: " . $e->getMessage(), 'error');
            }
        }
    }
    
    // Check if booking_summary view exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'booking_summary'");
    $viewExists = $stmt->rowCount() > 0;
    
    if ($viewExists) {
        showMessage("'booking_summary' व्यू पहले से मौजूद है। इसे ड्रॉप करके फिर से बनाएंगे।", 'warning');
        $pdo->exec("DROP VIEW IF EXISTS `booking_summary`");
    }
    
    // Create the booking_summary view
    showMessage("🔧 'booking_summary' व्यू बना रहे हैं...");
    try {
        $createBookingSummaryView = "
        CREATE VIEW `booking_summary` AS
        SELECT 
            b.id AS booking_id,
            b.booking_date,
            b.status AS booking_status,
            b.amount,
            u.id AS customer_id,
            u.name AS customer_name,
            u.email AS customer_email,
            u.phone AS customer_phone,
            p.id AS property_id,
            p.title AS property_title,
            p.location AS property_location,
            p.price AS property_price,
            a.id AS associate_id,
            au.name AS associate_name,
            a.company_name
        FROM 
            `bookings` b
        LEFT JOIN 
            `users` u ON b.customer_id = u.id
        LEFT JOIN 
            `properties` p ON b.property_id = p.id
        LEFT JOIN 
            `associates` a ON p.created_by = a.user_id
        LEFT JOIN 
            `users` au ON a.user_id = au.id
        ";
        
        $pdo->exec($createBookingSummaryView);
        showMessage("✅ 'booking_summary' व्यू सफलतापूर्वक बनाया गया है।", 'success');
    } catch (PDOException $e) {
        showMessage("❌ booking_summary व्यू बनाने में त्रुटि: " . $e->getMessage(), 'error');
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    showMessage("✅ फॉरेन की चेक फिर से चालू किए गए हैं।", 'success');
    
    // Test the view
    showMessage("<h2>booking_summary व्यू का परीक्षण कर रहे हैं...</h2>");
    try {
        $stmt = $pdo->query("SELECT * FROM booking_summary LIMIT 5");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($bookings) > 0) {
            showMessage("✅ 'booking_summary' व्यू सफलतापूर्वक काम कर रहा है।", 'success');
            
            echo "<h3>बुकिंग सारांश:</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr>";
            foreach (array_keys($bookings[0]) as $column) {
                echo "<th>$column</th>";
            }
            echo "</tr>";
            
            foreach ($bookings as $booking) {
                echo "<tr>";
                foreach ($booking as $value) {
                    echo "<td>" . (is_null($value) ? "NULL" : htmlspecialchars($value)) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            showMessage("⚠️ 'booking_summary' व्यू में कोई डेटा नहीं मिला।", 'warning');
        }
    } catch (PDOException $e) {
        showMessage("❌ 'booking_summary' व्यू परीक्षण में त्रुटि: " . $e->getMessage(), 'error');
    }
    
} catch (PDOException $e) {
    showMessage("❌ डेटाबेस ऑपरेशन में त्रुटि: " . $e->getMessage(), 'error');
}

// Summary
echo '<h2>समाधान सारांश:</h2>
<ol>
    <li>users टेबल को सही तरीके से बनाया गया है</li>
    <li>आवश्यक सैंपल डेटा जोड़ा गया है</li>
    <li>booking_summary व्यू को फिक्स किया गया है</li>
    <li>आवश्यक टेबल्स बनाए गए हैं (यदि वे मौजूद नहीं थे)</li>
    <li>फॉरेन की कंस्ट्रेंट को सही किया गया है</li>
</ol>
<p><a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">होम पेज पर वापस जाएं</a></p>
</body>
</html>';
?>