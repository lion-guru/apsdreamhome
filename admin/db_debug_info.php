<?php
require_once __DIR__ . '/../includes/db_connection.php';

try {
    $conn = getDbConnection();
    if (!$conn) {
        die('DB connection failed');
    }
    $dbname = $conn->query("SELECT DATABASE() as db")->fetch_assoc()['db'];
    echo "<h3>Connected Database: $dbname</h3>";

    // Show all tables
    $tables = $conn->query("SHOW TABLES");
    echo "<h4>Tables in database:</h4><ul>";
    while ($row = $tables->fetch_array()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";

    // Show all admin users
    $result = $conn->query("SELECT id, auser FROM admin");
    if (!$result) {
        die('Query failed: ' . $conn->error);
    }
    echo "<h4>Admin Users:</h4><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>ID: " . htmlspecialchars($row['id']) . " | Username: '" . htmlspecialchars($row['auser']) . "'</li>";
    }
    echo "</ul>";

    // Show sample of all users (if table exists)
    $userTable = $conn->query("SHOW TABLES LIKE 'users'");
    if ($userTable && $userTable->num_rows > 0) {
        $users = $conn->query("SELECT id, first_name, last_name, email FROM users LIMIT 10");
        echo "<h4>Sample Users:</h4><ul>";
        while ($row = $users->fetch_assoc()) {
            echo "<li>ID: " . htmlspecialchars($row['id']) . ", Name: " . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . ", Email: " . htmlspecialchars($row['email']) . "</li>";
        }
        echo "</ul>";
    }

    $conn->close();
} catch (Exception $e) {
    echo "<div style='color:red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
