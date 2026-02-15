<?php
echo "<h1>Admin Session Debug</h1>";

// Start session
session_start();

echo "<h3>Session Variables:</h3>";
echo "<ul>";
if (isset($_SESSION['admin_logged_in'])) {
    echo "<li style='color: green;'>✅ admin_logged_in: " . $_SESSION['admin_logged_in'] . "</li>";
} else {
    echo "<li style='color: red;'>❌ admin_logged_in: NOT SET</li>";
}

if (isset($_SESSION['admin_username'])) {
    echo "<li style='color: green;'>✅ admin_username: " . $_SESSION['admin_username'] . "</li>";
} else {
    echo "<li style='color: red;'>❌ admin_username: NOT SET</li>";
}

if (isset($_SESSION['admin_role'])) {
    echo "<li style='color: green;'>✅ admin_role: " . $_SESSION['admin_role'] . "</li>";
} else {
    echo "<li style='color: red;'>❌ admin_role: NOT SET</li>";
}

if (isset($_SESSION['admin_id'])) {
    echo "<li style='color: green;'>✅ admin_id: " . $_SESSION['admin_id'] . "</li>";
} else {
    echo "<li style='color: red;'>❌ admin_id: NOT SET</li>";
}
echo "</ul>";

echo "<hr>";

echo "<h3>Current URL: " . $_SERVER['REQUEST_URI'] . "</h3>";
echo "<h3>Referrer: " . ($_SERVER['HTTP_REFERER'] ?? 'None') . "</h3>";

echo "<hr>";

echo "<h3>Available Actions:</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Go to Admin Login</a></li>";
echo "<li><a href='bookings.php'>Try Bookings Page</a></li>";
echo "<li><a href='enhanced_dashboard.php'>Try Dashboard</a></li>";
echo "<li><a href='../index.php'>Go to Main Site</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Current time:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
