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
    echo "<div style='color: $color;'>$message</div>";
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    showMessage("✅ Database connection successful!", 'success');
} catch (PDOException $e) {
    showMessage("❌ Database connection failed: " . $e->getMessage(), 'error');
    exit;
}

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    showMessage("Temporarily disabled foreign key checks.", 'info');

    // Drop the users table if it exists
    $pdo->exec("DROP TABLE IF EXISTS `users`");
    showMessage("Dropped `users` table if it existed.", 'info');

    $createUsersTable = "
    CREATE TABLE `users` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createUsersTable);
    showMessage("✅ `users` table created successfully with a minimal schema.", 'success');

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    showMessage("✅ Re-enabled foreign key checks.", 'success');

} catch (PDOException $e) {
    showMessage("❌ An error occurred: " . $e->getMessage(), 'error');
    // Ensure checks are re-enabled on failure
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
?>