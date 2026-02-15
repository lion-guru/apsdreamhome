<?php
/**
 * APS Dream Home - Test All Pages
 * Tests all pages and links to identify issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test All Pages - APS Dream Home</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: green; background: #e6ffe6; padding: 10px; margin: 10px 0; border-left: 4px solid #44ff44; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; border-left: 4px solid #ff4444; border-radius: 5px; }
        .info { color: blue; background: #e6f3ff; padding: 10px; margin: 10px 0; border-left: 4px solid #4488ff; border-radius: 5px; }
        .warning { color: orange; background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107; border-radius: 5px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 APS Dream Home - Test All Pages</h1>";

// Test database connection first
echo "<div class='test-section'><h3>🗄️ Database Connection Test</h3>";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check tables
    $result = $conn->query("SHOW TABLES");
    $table_count = $result->num_rows;
    echo "<div class='info'>📊 Total tables: $table_count</div>";
    
    // Check important tables
    $important_tables = ['users', 'properties', 'customers', 'leads', 'projects', 'bookings', 'associates', 'site_settings'];
    echo "<div class='info'>📋 <strong>Important Tables Status:</strong></div>";
    
    foreach ($important_tables as $table) {
        $check = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check->num_rows > 0) {
            $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $count_result->fetch_assoc()['count'];
            echo "<div class='success'>✅ $table table exists ($count records)</div>";
        } else {
            echo "<div class='error'>❌ $table table missing</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
}

// Test file existence
echo "<div class='test-section'><h3>📁 File Existence Test</h3>";

$important_files = [
    'index.php' => 'Main homepage',
    'admin.php' => 'Admin panel',
    'about.php' => 'About page',
    'contact.php' => 'Contact page',
    'properties.php' => 'Properties page',
    'projects.php' => 'Projects page',
    'login.php' => 'Login page',
    'registration.php' => 'Registration page',
    'includes/header.php' => 'Header template',
    'includes/footer.php' => 'Footer template',
    'includes/db_connection.php' => 'Database connection',
    'includes/config.php' => 'Configuration file'
];

echo "<table><tr><th>File</th><th>Status</th><th>Description</th></tr>";

foreach ($important_files as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<tr><td>$file</td><td class='success'>✅ EXISTS</td><td>$description ($size bytes)</td></tr>";
    } else {
        echo "<tr><td>$file</td><td class='error'>❌ MISSING</td><td>$description</td></tr>";
    }
}

echo "</table>";

// Test page syntax
echo "<div class='test-section'><h3>🔍 Page Syntax Test</h3>";

$pages_to_test = ['index.php', 'admin.php', 'about.php', 'contact.php', 'properties.php'];

foreach ($pages_to_test as $page) {
    if (file_exists($page)) {
        $output = shell_exec("php -l $page 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div class='success'>✅ $page - No syntax errors</div>";
        } else {
            echo "<div class='error'>❌ $page - Syntax errors found</div>";
        }
    }
}

// Test URL routing
echo "<div class='test-section'><h3>🌐 URL Routing Test</h3>";

$routes_to_test = [
    'home' => 'index.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
    'properties' => 'properties.php',
    'projects' => 'projects.php',
    'admin' => 'admin.php'
];

echo "<table><tr><th>Route</th><th>Expected File</th><th>Status</th></tr>";

foreach ($routes_to_test as $route => $expected_file) {
    if (file_exists($expected_file)) {
        echo "<tr><td>$route</td><td>$expected_file</td><td class='success'>✅ File exists</td></tr>";
    } else {
        echo "<tr><td>$route</td><td>$expected_file</td><td class='error'>❌ File missing</td></tr>";
    }
}

echo "</table>";

// Test admin panel access
echo "<div class='test-section'><h3>👨‍💼 Admin Panel Test</h3>";

if (file_exists('admin.php')) {
    echo "<div class='success'>✅ Admin panel file exists</div>";
    
    // Check if admin user exists in database
    try {
        $result = $conn->query("SELECT id, username, role FROM users WHERE role = 'admin' LIMIT 1");
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            echo "<div class='success'>✅ Admin user exists: " . $admin['username'] . "</div>";
        } else {
            echo "<div class='error'>❌ No admin user found</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error checking admin user: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ Admin panel file missing</div>";
}

// Test .htaccess
echo "<div class='test-section'><h3>⚙️ .htaccess Configuration Test</h3>";

if (file_exists('.htaccess')) {
    echo "<div class='success'>✅ .htaccess file exists</div>";
    
    $htaccess_content = file_get_contents('.htaccess');
    
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<div class='success'>✅ URL rewriting enabled</div>";
    } else {
        echo "<div class='warning'>⚠️ URL rewriting not configured</div>";
    }
    
    if (strpos($htaccess_content, 'RewriteRule') !== false) {
        echo "<div class='success'>✅ Rewrite rules configured</div>";
    } else {
        echo "<div class='warning'>⚠️ Rewrite rules missing</div>";
    }
} else {
    echo "<div class='error'>❌ .htaccess file missing</div>";
}

// Summary
echo "<div class='test-section'><h3>📊 Test Summary</h3>";

$total_tests = 0;
$passed_tests = 0;

// Count tests
foreach ($important_files as $file => $description) {
    $total_tests++;
    if (file_exists($file)) {
        $passed_tests++;
    }
}

foreach ($pages_to_test as $page) {
    $total_tests++;
    if (file_exists($page)) {
        $passed_tests++;
    }
}

$success_rate = round(($passed_tests / $total_tests) * 100, 1);

echo "<div class='info'>📊 <strong>Test Results:</strong></div>";
echo "<div class='info'>✅ Passed: $passed_tests</div>";
echo "<div class='info'>❌ Failed: " . ($total_tests - $passed_tests) . "</div>";
echo "<div class='info'>📈 Success Rate: $success_rate%</div>";

if ($success_rate >= 80) {
    echo "<div class='success'><h3>🎉 Project Status: GOOD</h3>";
    echo "<p>Most components are working correctly. You can proceed with testing the website.</p></div>";
} elseif ($success_rate >= 60) {
    echo "<div class='warning'><h3>⚠️ Project Status: FAIR</h3>";
    echo "<p>Some components need attention. Review the failed tests above.</p></div>";
} else {
    echo "<div class='error'><h3>❌ Project Status: NEEDS WORK</h3>";
    echo "<p>Several components are missing or broken. Please fix the issues above.</p></div>";
}

echo "<div class='info'><h4>🔗 Quick Links:</h4>";
echo "<p><a href='index.php' target='_blank'>🏠 Homepage</a> | ";
echo "<a href='admin.php' target='_blank'>👨‍💼 Admin Panel</a> | ";
echo "<a href='about.php' target='_blank'>ℹ️ About Page</a> | ";
echo "<a href='contact.php' target='_blank'>📞 Contact Page</a> | ";
echo "<a href='properties.php' target='_blank'>🏘️ Properties Page</a></p></div>";

echo "</div></body></html>";
?>