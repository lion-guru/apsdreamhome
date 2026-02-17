<?php
/**
 * Complete Menu/Navigation Mapping - APS Dream Homes
 * Shows where all authentication links are implemented
 */

echo "🎯 APS DREAM HOMES - COMPLETE MENU/LINK MAPPING\n";
echo "===============================================\n\n";

// Check main header navigation
echo "📱 MAIN SITE HEADER NAVIGATION\n";
echo "----------------------------\n";
echo "File: includes/templates/header.php\n";
echo "Location: Line 1671-1676 (Account Dropdown)\n";
echo "Links Found:\n";
echo "  🔑 Login: BASE_URL . 'login.php'\n";
echo "  📝 Register: BASE_URL . 'register.php'\n";
echo "  🏠 Customer Dashboard: BASE_URL . 'customer-dashboard' (NEEDS FIX)\n";
echo "  📈 Associate Dashboard: BASE_URL . 'associate-dashboard' (NEEDS FIX)\n";
echo "\n";

// Check customer dashboard
echo "🏠 CUSTOMER DASHBOARD\n";
echo "--------------------\n";
echo "File: customer_dashboard.php\n";
echo "Location: Line 810 (Logout Section)\n";
echo "Links Found:\n";
echo "  🚪 Logout: customer_login.php?logout=1 (NEEDS FIX)\n";
echo "\n";

// Check admin dashboard
echo "🛡️ ADMIN DASHBOARD\n";
echo "------------------\n";
echo "File: admin/enhanced_dashboard.php\n";
echo "Location: No logout link found (NEEDS ADDITION)\n";
echo "Links Found:\n";
echo "  ❌ No logout link in admin dashboard\n";
echo "\n";

// Check other dashboards
echo "📊 OTHER DASHBOARDS\n";
echo "------------------\n";
echo "Files: agent_dashboard.php, builder_dashboard.php, investor_dashboard.php, associate_dir/associate_dashboard.php\n";
echo "Links Found:\n";
echo "  ❌ No logout links in any dashboards\n";
echo "\n";

// Check footer
echo "🦶 FOOTER NAVIGATION\n";
echo "------------------\n";
echo "File: includes/templates/footer.php\n";
echo "Links Found:\n";
echo "  ❌ No authentication links in footer\n";
echo "\n";

// Summary
echo "📋 SUMMARY OF ISSUES\n";
echo "-------------------\n";
echo "❌ Header Dashboard Links:\n";
echo "   - customer-dashboard should be customer_dashboard.php\n";
echo "   - associate-dashboard should be associate_dir/associate_dashboard.php\n";
echo "\n";
echo "❌ Missing Logout Links:\n";
echo "   - Customer dashboard: Points to wrong file\n";
echo "   - Admin dashboard: No logout link\n";
echo "   - Agent dashboard: No logout link\n";
echo "   - Builder dashboard: No logout link\n";
echo "   - Investor dashboard: No logout link\n";
echo "   - Associate dashboard: No logout link\n";
echo "\n";
echo "❌ Missing Dashboard Links:\n";
echo "   - Header missing agent, builder, investor dashboard links\n";
echo "\n";

echo "🔧 RECOMMENDED FIXES\n";
echo "-------------------\n";
echo "1. Fix header dashboard links to point to correct files\n";
echo "2. Add logout links to all dashboards\n";
echo "3. Add missing dashboard links to header\n";
echo "4. Update customer dashboard logout link\n";
echo "\n";

echo "✅ CURRENTLY WORKING LINKS\n";
echo "------------------------\n";
echo "✅ Main Login: login.php\n";
echo "✅ Main Register: register.php\n";
echo "✅ Admin Login: admin/login_unified.php\n";
echo "✅ Admin Register: admin/register_unified.php\n";
echo "\n";

echo "🎯 CONCLUSION\n";
echo "------------\n";
echo "Main authentication links work, but dashboard navigation\n";
echo "and logout links need fixes for complete user experience.\n";
?>
