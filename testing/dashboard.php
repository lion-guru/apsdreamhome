<?php
/**
 * APS Dream Home - Testing Dashboard
 */

echo "<h1>🧪 APS Dream Home Testing Suite</h1>";
echo "<p>Complete testing environment for APS Dream Home application</p>";

echo "<h2>📂 Testing Categories</h2>";

$categories = [
    "unit" => "Unit Tests",
    "integration" => "Integration Tests", 
    "system" => "System Tests",
    "database" => "Database Tests",
    "api" => "API Tests",
    "setup" => "Setup Scripts",
    "checks" => "Health Checks",
    "reports" => "Test Reports"
];

foreach ($categories as $dir => $title) {
    echo "<h3>📁 $title</h3>";
    echo "<ul>";
    
    $files = glob($dir . "/*.php");
    foreach ($files as $file) {
        $filename = basename($file);
        if ($filename !== "index.php") {
            echo "<li><a href=\"" . $dir . "/" . $filename . "\">" . $filename . "</a></li>";
        }
    }
    
    echo "</ul>";
}

echo "<h2>🚀 Quick Actions</h2>";
echo "<ul>";
echo "<li><a href='../'>← Back to Main Application</a></li>";
echo "<li><a href='unit/'>Run Unit Tests</a></li>";
echo "<li><a href='system/'>Run System Tests</a></li>";
echo "<li><a href='checks/'>Run Health Checks</a></li>";
echo "</ul>";

echo "<h2>📊 Test Statistics</h2>";
echo "<p>Total test files: " . count(glob("**/*.php", GLOB_BRACE)) . "</p>";
echo "<p>Last updated: " . date("Y-m-d H:i:s") . "</p>";
?>