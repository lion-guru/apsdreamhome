<?php
/**
 * Database Migration - Add Remember Me Columns
 * Add remember_token and token_expiry columns to employees table
 */

require_once 'includes/config.php';

require_once dirname(__DIR__, 2) . '/app/helpers.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h2>Database Migration - Add Remember Me Columns</h2>";

try {
    // Check if remember_token column exists
    $check_token = $conn->prepare("SHOW COLUMNS FROM employees LIKE 'remember_token'");
    $check_token->execute();
    $token_exists = $check_token->get_result()->num_rows > 0;

    if (!$token_exists) {
        // Add remember_token column
        $sql = "ALTER TABLE employees ADD COLUMN remember_token VARCHAR(255) NULL AFTER email";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Added 'remember_token' column successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add 'remember_token' column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ 'remember_token' column already exists</p>";
    }

    // Check if token_expiry column exists
    $check_expiry = $conn->prepare("SHOW COLUMNS FROM employees LIKE 'token_expiry'");
    $check_expiry->execute();
    $expiry_exists = $check_expiry->get_result()->num_rows > 0;

    if (!$expiry_exists) {
        // Add token_expiry column
        $sql = "ALTER TABLE employees ADD COLUMN token_expiry DATETIME NULL AFTER remember_token";
        if ($conn->query($sql)) {
            echo "<p style='color: green;'>✅ Added 'token_expiry' column successfully</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add 'token_expiry' column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ 'token_expiry' column already exists</p>";
    }

    // Verify table structure
    echo "<h3>Current Employees Table Structure:</h3>";
    $result = $conn->query("DESCRIBE employees");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . h($row['Field']) . "</td>";
        echo "<td>" . h($row['Type']) . "</td>";
        echo "<td>" . h($row['Null']) . "</td>";
        echo "<td>" . h($row['Key']) . "</td>";
        echo "<td>" . h($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3>Test Employee Data:</h3>";
    $result = $conn->query("SELECT id, name, email, department, status, remember_token IS NOT NULL as has_token, token_expiry IS NOT NULL as has_expiry FROM employees LIMIT 5");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Status</th><th>Has Token</th><th>Has Expiry</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . h($row['id']) . "</td>";
        echo "<td>" . h($row['name']) . "</td>";
        echo "<td>" . h($row['email']) . "</td>";
        echo "<td>" . h($row['department']) . "</td>";
        echo "<td>" . h($row['status']) . "</td>";
        echo "<td>" . ($row['has_token'] ? '✅' : '❌') . "</td>";
        echo "<td>" . ($row['has_expiry'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<p style='color: green; font-weight: bold;'>✅ Migration completed successfully!</p>";
    echo "<p><a href='employee_login.php'>🔗 Go to Employee Login</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Migration failed: " . $e->getMessage() . "</p>";
}
?>
