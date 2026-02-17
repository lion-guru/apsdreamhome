<?php
// Comprehensive Header Link Verification
echo "<h1>🔗 Header Links Verification</h1>";
echo "<style>
.verified { color: green; }
.error { color: red; }
.warning { color: orange; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";

echo "<table>";
echo "<tr><th>Menu Item</th><th>Link</th><th>Route</th><th>File Exists</th><th>Status</th></tr>";

// Define all routes from header
$routes = [
    // Main navigation
    ['Home', '/', ''],
    ['Projects', 'projects', 'projects'],
    ['Properties', 'properties', 'properties'],
    ['About', 'about', 'about'],
    ['Resources > Blog', 'blog', 'blog'],
    ['Resources > Gallery', 'gallery', 'gallery'],
    ['Resources > News', 'news', 'news'],
    ['Resources > Downloads', 'downloads', 'downloads'],
    ['Services > Property Management', 'property-management', 'property-management'],
    ['Services > Legal Services', 'legal-services', 'legal-services'],
    ['Services > Financial Services', 'financial-services', 'financial-services'],
    ['Services > Interior Design', 'interior-design', 'interior-design'],
    ['Careers', 'career', 'career'],
    ['Contact', 'contact', 'contact'],

    // Authentication
    ['Login', 'login', 'login'],
    ['Register', 'register', 'register'],

    // Property related
    ['Property Details', 'property?id=1', 'property'],
    ['Resale Properties', 'resell', 'resell'],
    ['Featured Properties', 'featured-properties', 'featured-properties'],

    // Dashboards
    ['Customer Dashboard', 'customer-dashboard', 'customer-dashboard'],
    ['Associate Dashboard', 'associate-dashboard', 'associate-dashboard'],
];

// Check each route
foreach ($routes as $route) {
    $menu_item = $route[0];
    $link = $route[1];
    $route_name = $route[2];

    // Check if file exists
    $file_exists = file_exists($route_name . '.php');

    // Determine status
    if ($file_exists) {
        $status = "<span class='verified'>✅ Verified</span>";
    } else {
        $status = "<span class='error'>❌ Missing File</span>";
    }

    echo "<tr>";
    echo "<td>$menu_item</td>";
    echo "<td><code>$link</code></td>";
    echo "<td><code>$route_name</code></td>";
    echo "<td>" . ($file_exists ? "✅ Yes" : "❌ No") . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>🎯 Routing System Status</h2>";
echo "<ul>";

// Check routing components
$routing_checks = [
    ['Root .htaccess', file_exists('../.htaccess')],
    ['Project .htaccess', file_exists('.htaccess')],
    ['Main Router (index.php)', file_exists('index.php')],
    ['Template System', file_exists('includes/enhanced_universal_template.php')],
    ['Database Connection', file_exists('includes/db_connection.php')],
];

foreach ($routing_checks as $check) {
    $name = $check[0];
    $exists = $check[1];
    $icon = $exists ? "✅" : "❌";
    $class = $exists ? "verified" : "error";
    echo "<li class='$class'>$icon $name</li>";
}

echo "</ul>";

echo "<h2>🔐 Authentication System</h2>";
echo "<ul>";

// Check authentication files
$auth_checks = [
    ['Login Page', file_exists('login.php')],
    ['Registration Page', file_exists('registration.php')],
    ['Customer Dashboard', file_exists('customer_dashboard.php')],
    ['Associate Dashboard', file_exists('dashasso.php')],
];

foreach ($auth_checks as $check) {
    $name = $check[0];
    $exists = $check[1];
    $icon = $exists ? "✅" : "❌";
    $class = $exists ? "verified" : "error";
    echo "<li class='$class'>$icon $name</li>";
}

echo "</ul>";

echo "<h2>📋 Test Links</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 20px 0;'>";

// Test links for main routes
$test_links = [
    ['/', 'Homepage'],
    ['/about', 'About Page'],
    ['/properties', 'Properties'],
    ['/projects', 'Projects'],
    ['/contact', 'Contact'],
    ['/login', 'Login'],
    ['/register', 'Register'],
    ['/career', 'Careers'],
];

foreach ($test_links as $link) {
    $url = $link[0];
    $name = $link[1];
    echo "<a href='$url' class='btn btn-outline-primary' target='_blank'>$name</a>";
}

echo "</div>";

echo "<div class='alert alert-info'>";
echo "<h4>📝 Instructions:</h4>";
echo "<ol>";
echo "<li>सभी menu items पर click करके test करें</li>";
echo "<li>Dropdown menus properly काम करनी चाहिए</li>";
echo "<li>Login/Register buttons functional होने चाहिए</li>";
echo "<li>सभी pages same header के साथ load होने चाहिए</li>";
echo "<li>Query parameters (जैसे ?type=residential) preserve होने चाहिए</li>";
echo "</ol>";
echo "</div>";
?>
