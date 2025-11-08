<?php
/**
 * Associate Permissions Test Script
 * Tests the permissions system with different associate levels
 */

require_once 'includes/config.php';
require_once 'includes/associate_permissions.php';

// Initialize database connection
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<h1>🧪 Associate Permissions System Test</h1>\n";

// Test data - replace with actual associate IDs from your database
$test_associates = [
    ['id' => 1, 'name' => 'Associate Level', 'level' => 'Associate'],
    ['id' => 2, 'name' => 'Senior Associate', 'level' => 'Sr. Associate'],
    ['id' => 3, 'name' => 'BDM Level', 'level' => 'BDM'],
    ['id' => 4, 'name' => 'Senior BDM', 'level' => 'Sr. BDM'],
    ['id' => 5, 'name' => 'Vice President', 'level' => 'Vice President']
];

foreach ($test_associates as $associate) {
    echo "<h2>📊 Testing: {$associate['name']} (Level: {$associate['level']})</h2>\n";

    echo "<h3>✅ Accessible Modules:</h3>\n";
    $modules = getAccessibleModules($associate['id']);
    echo "<ul>\n";
    foreach ($modules as $module_key => $module_name) {
        echo "<li><strong>$module_name</strong> ($module_key)</li>\n";
    }
    echo "</ul>\n";

    echo "<h3>🔐 Permission Details:</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Module</th><th>Read</th><th>Write</th><th>Delete</th><th>Admin</th></tr>\n";

    $test_modules = ['dashboard', 'customers', 'crm', 'team_management', 'commission_management', 'reports'];

    foreach ($test_modules as $module) {
        echo "<tr>\n";
        echo "<td><strong>$module</strong></td>\n";
        echo "<td>" . (hasPermission($associate['id'], $module, 'read') ? '✅' : '❌') . "</td>\n";
        echo "<td>" . (hasPermission($associate['id'], $module, 'write') ? '✅' : '❌') . "</td>\n";
        echo "<td>" . (hasPermission($associate['id'], $module, 'delete') ? '✅' : '❌') . "</td>\n";
        echo "<td>" . (hasPermission($associate['id'], $module, 'admin') ? '✅' : '❌') . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";

    echo "<h3>🎯 Special Checks:</h3>\n";
    echo "<ul>\n";
    echo "<li>Can manage associates: " . (canManageAssociates($associate['id']) ? '✅ Yes' : '❌ No') . "</li>\n";
    echo "<li>Can manage commissions: " . (canManageCommissions($associate['id']) ? '✅ Yes' : '❌ No') . "</li>\n";
    echo "<li>Is admin: " . (isAssociateAdmin($associate['id']) ? '✅ Yes' : '❌ No') . "</li>\n";
    echo "</ul>\n";

    echo "<hr style='margin: 30px 0;'>\n";
}

echo "<h2>📋 Test Summary</h2>\n";
echo "<p>This test shows how permissions work for different associate levels.</p>\n";
echo "<p><strong>Note:</strong> Make sure to replace the test associate IDs with actual IDs from your database.</p>\n";

// Show available functions
echo "<h2>🔧 Available Functions</h2>\n";
echo "<ul>\n";
echo "<li><code>hasPermission(\$associate_id, \$module, \$permission_type)</code></li>\n";
echo "<li><code>canAccessModule(\$associate_id, \$module)</code></li>\n";
echo "<li><code>canPerformAction(\$associate_id, \$module, \$action)</code></li>\n";
echo "<li><code>getAccessibleModules(\$associate_id)</code></li>\n";
echo "<li><code>updateAssociatePermission(\$associate_id, \$module, \$permission_type, \$is_allowed)</code></li>\n";
echo "<li><code>isAssociateAdmin(\$associate_id)</code></li>\n";
echo "<li><code>canManageAssociates(\$associate_id)</code></li>\n";
echo "<li><code>canManageCommissions(\$associate_id)</code></li>\n";
echo "</ul>\n";
?>
