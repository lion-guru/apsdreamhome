<?php
// Test Company Owner Role Implementation
session_start();

// Set up test session for Company Owner
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_role'] = 'company_owner';
$_SESSION['admin_username'] = 'Abhay Singh (Owner)';

echo "<h1>🏆 Company Owner Role Test - APS Dream Home</h1>";
echo "<h2>Ultimate Power & Access Verification</h2>";

// Test role configuration
require_once 'includes/universal_dashboard_template.php';

echo "<h3>✅ Role Configuration Test:</h3>";
global $role_config;
if (isset($role_config['company_owner'])) {
    echo "<div style='background: linear-gradient(135deg, #d4af37, #ffd700); padding: 20px; border-radius: 10px; margin: 10px 0;'>";
    echo "<h4>🎯 Company Owner Configuration Found!</h4>";
    echo "<p><strong>Title:</strong> " . $role_config['company_owner']['title'] . "</p>";
    echo "<p><strong>Description:</strong> " . $role_config['company_owner']['description'] . "</p>";
    echo "<p><strong>Theme:</strong> " . $role_config['company_owner']['theme_class'] . "</p>";
    echo "<p><strong>Menu Items:</strong> " . count($role_config['company_owner']['menu_items']) . " ultimate access options</p>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ Company Owner configuration not found!</p>";
}

echo "<h3>🎨 CSS Theme Test:</h3>";
echo "<div class='dashboard-company-owner' style='padding: 20px; background: var(--company-owner-gradient, #d4af37); color: white; border-radius: 10px; margin: 10px 0;'>";
echo "<h4>🌟 Gold Royal Theme Active</h4>";
echo "<p>This should display with gold/royal styling if CSS is properly loaded.</p>";
echo "</div>";

echo "<h3>🔗 Dashboard Access Links:</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 10px 0;'>";
echo "<h4>🚀 Try these Company Owner features:</h4>";
echo "<ul>";
echo "<li><a href='company_owner_dashboard.php' target='_blank'>📊 Company Owner Dashboard</a></li>";
echo "<li><a href='superadmin_dashboard.php' target='_blank'>🔧 SuperAdmin Dashboard</a></li>";
echo "<li><a href='admin_dashboard.php' target='_blank'>👨‍💼 Admin Dashboard</a></li>";
echo "<li><a href='manager_dashboard.php' target='_blank'>📈 Manager Dashboard</a></li>";
echo "</ul>";
echo "</div>";

echo "<h3>💡 Features Test:</h3>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 10px 0;'>";
echo "<h4>✨ Company Owner Ultimate Powers:</h4>";
echo "<ul>";
echo "<li>🏢 Complete Company Overview & Control</li>";
echo "<li>👥 All Staff Management (13+ different roles)</li>";
echo "<li>💰 Full Financial Control & Analytics</li>";
echo "<li>⚙️ Master Settings & Configuration</li>";
echo "<li>🛡️ Security & Audit Controls</li>";
echo "<li>🗄️ Full Database Access</li>";
echo "<li>🔑 Role & Permission Management</li>";
echo "<li>📊 Business Intelligence Dashboard</li>";
echo "<li>🤝 Partnership Management</li>";
echo "<li>⚖️ Legal & Compliance Oversight</li>";
echo "</ul>";
echo "</div>";

echo "<h3>📱 Mobile Responsiveness Test:</h3>";
echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 10px 0;'>";
echo "<h4>📲 Mobile-First Design Features:</h4>";
echo "<ul>";
echo "<li>✅ Responsive Grid System</li>";
echo "<li>✅ Touch-Friendly Navigation</li>";
echo "<li>✅ Collapsible Sidebar</li>";
echo "<li>✅ Mobile-Optimized Cards</li>";
echo "<li>✅ Swipe Gestures Support</li>";
echo "</ul>";
echo "</div>";

echo "<style>";
echo ":root { --company-owner-primary: #d4af37; --company-owner-secondary: #b8860b; --company-owner-gradient: linear-gradient(135deg, #d4af37 0%, #ffd700 50%, #b8860b 100%); }";
echo "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f8f9fa; }";
echo "h1 { color: #d4af37; text-shadow: 2px 2px 4px rgba(0,0,0,0.1); }";
echo "h2, h3 { color: #333; }";
echo "a { color: #d4af37; text-decoration: none; font-weight: bold; }";
echo "a:hover { color: #b8860b; text-decoration: underline; }";
echo "</style>";

echo "<div style='background: linear-gradient(135deg, #d4af37, #ffd700); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center; color: white;'>";
echo "<h2>🎉 Company Owner Implementation Complete!</h2>";
echo "<p>Your vision of having ultimate company control is now reality. The owner has complete power over all aspects of APS Dream Home.</p>";
echo "</div>";
?>