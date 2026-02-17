<?php
/**
 * Admin Login Rate Limit Reset Tool
 * This script resets the rate limiting for admin login attempts
 */

require_once __DIR__ . '/core/init.php';

echo "<h2>🔓 Admin Login Rate Limit Reset</h2>\n";
echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 20px;'>\n";

// Show current rate limit status
echo "<h3>📊 Current Rate Limit Status:</h3>\n";

$ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$rate_limit_key = 'admin_login_attempts_' . md5($ip_address);

if (isset($_SESSION[$rate_limit_key])) {
    $rate_limit_data = $_SESSION[$rate_limit_key];
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>⚠️ Rate Limiting Active:</strong><br>\n";
    echo "• IP Address: " . h($ip_address) . "<br>\n";
    echo "• Failed Attempts: " . $rate_limit_data['attempts'] . "<br>\n";
    echo "• First Attempt: " . date('Y-m-d H:i:s', $rate_limit_data['first_attempt']) . "<br>\n";
    echo "• Last Attempt: " . date('Y-m-d H:i:s', $rate_limit_data['last_attempt']) . "<br>\n";
    
    if ($rate_limit_data['locked_until'] > time()) {
        echo "• 🔒 Locked Until: " . date('Y-m-d H:i:s', $rate_limit_data['locked_until']) . "<br>\n";
        echo "• ⏰ Time Remaining: " . ($rate_limit_data['locked_until'] - time()) . " seconds<br>\n";
    } else {
        echo "• ✅ Lock Expired<br>\n";
    }
    echo "</div>\n";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "✅ No rate limiting detected for IP: " . h($ip_address) . "\n";
    echo "</div>\n";
}

// Reset rate limiting
echo "<h3>🔄 Resetting Rate Limits...</h3>\n";

// Clear all admin login rate limiting
$cleared_keys = [];
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'admin_login_attempts_') === 0) {
        unset($_SESSION[$key]);
        $cleared_keys[] = $key;
    }
}

// Clear other login-related session data
$other_keys = [
    'admin_login_blocked_until',
    'admin_login_attempts',
    'login_error',
    'rate_limit_cleanup'
];

foreach ($other_keys as $key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
        $cleared_keys[] = $key;
    }
}

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<strong>✅ Rate Limits Reset Successfully!</strong><br>\n";
echo "• Cleared " . count($cleared_keys) . " rate limiting entries<br>\n";
echo "• IP address lockout removed<br>\n";
echo "• Failed attempt counter reset<br>\n";
echo "• All login restrictions lifted<br>\n";
echo "</div>\n";

// Show test credentials
echo "<h3>🔑 Test Admin Credentials:</h3>\n";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<strong>Use these credentials to login:</strong><br>\n";
echo "• <strong>Username:</strong> testadmin<br>\n";
echo "• <strong>Password:</strong> admin123<br>\n";
echo "• <strong>Role:</strong> admin<br>\n";
echo "• <strong>Status:</strong> active<br>\n";
echo "</div>\n";

// Provide login link
echo "<h3>🚀 Ready to Login:</h3>\n";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "<p><strong>Now you can login without restrictions:</strong></p>\n";
echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔓 Go to Admin Login</a></p>\n";
echo "<p><em>Note: The security system will track new attempts, but with a clean slate.</em></p>\n";
echo "</div>\n";

echo "<h3>💡 Tips for Successful Login:</h3>\n";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
echo "1. <strong>Use exact credentials:</strong> testadmin / admin123<br>\n";
echo "2. <strong>Answer security question:</strong> Simple math like 5+4=9<br>\n";
echo "3. <strong>Clear browser cache</strong> if you still see old errors<br>\n";
echo "4. <strong>Try incognito/private mode</strong> for a fresh session<br>\n";
echo "5. <strong>Make sure XAMPP is running</strong> Apache and MySQL<br>\n";
echo "</div>\n";

echo "</div>\n";

echo "<hr>\n";
echo "<p><em>Rate limit reset completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
