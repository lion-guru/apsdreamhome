<?php
/**
 * Rate Limit Reset Script
 * Clears rate limiting data for testing purposes
 */

// Start session
session_start();

echo "<h1>Rate Limit Reset</h1>";

if (isset($_POST['reset'])) {
    // Clear all rate limiting data from session
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'customer_dashboard_operations_') === 0) {
            unset($_SESSION[$key]);
        }
    }

    echo "<div style='color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Rate limiting data cleared successfully!";
    echo "</div>";
}

echo "<p>This will clear all rate limiting counters for the customer dashboard.</p>";
echo "<form method='POST'>";
echo "<button type='submit' name='reset' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "Reset Rate Limits";
echo "</button>";
echo "</form>";

echo "<hr>";
echo "<h3>Current Rate Limit Status:</h3>";
echo "<ul>";
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'customer_dashboard_operations_') === 0) {
        echo "<li><strong>$key:</strong> " . print_r($value, true) . "</li>";
    }
}
echo "</ul>";

if (empty($_SESSION)) {
    echo "<p>No rate limiting data found in session.</p>";
}

echo "<hr>";
echo "<a href='customer_dashboard.php' style='color: blue;'>← Back to Dashboard</a>";
?>
