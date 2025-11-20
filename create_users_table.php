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
    }
    echo "<div style='color: $color; margin: 10px 0;'>$message</div>";
}

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    showMessage("✅ Database connection successful!", 'success');
} catch (PDOException $e) {
    showMessage("❌ Database connection failed: " . $e->getMessage(), 'error');
    exit;
}

try {
    // Drop the table if it exists
    $pdo->exec("DROP TABLE IF EXISTS `users`");
    showMessage("Table `users` dropped if it existed.", 'info');

    // Create the users table
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
      `api_access` tinyint(1) DEFAULT '0',
      `api_rate_limit` int(11) DEFAULT '100',
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($createUsersTable);
    showMessage("✅ 'users' table created successfully.", 'success');

} catch (PDOException $e) {
    showMessage("❌ Error during table creation: " . $e->getMessage(), 'error');
}
?>