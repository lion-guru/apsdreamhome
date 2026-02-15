<?php
// Test PHP configuration
echo "<h1>PHP Configuration Test</h1>";

// Test short open tags
echo "<h2>Short Open Tags Test</h2>";
echo "<p>Testing short open tags...</p>";

// Test if short_open_tag is enabled
$short_open_tag = ini_get('short_open_tag');
echo "<p>short_open_tag setting: " . ($short_open_tag ? 'On' : 'Off') . "</p>";

// Test if we're using the correct PHP opening tag
echo "<p>This file uses <?php and should work regardless of short_open_tag setting</p>";

// Test error reporting
echo "<h2>Error Reporting</h2>";
echo "<p>Error reporting level: " . error_reporting() . "</p>";

// Test if we can access the original file
if (file_exists('homepage.php')) {
    echo "<p>homepage.php exists and is readable</p>";
    
    // Check the first few lines
    $lines = file('homepage.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "<p>First line: " . htmlspecialchars(substr($lines[0], 0, 20)) . "...</p>";
}

echo "<p>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>