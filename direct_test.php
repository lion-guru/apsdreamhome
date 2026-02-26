<?php
// Test the website directly without curl
echo "🧪 Direct PHP Test\n";
echo "==================\n";

// Set environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/apsdreamhome/';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Include the main index file
ob_start();
include 'public/index.php';
$output = ob_get_clean();

echo "Output Length: " . strlen($output) . " bytes\n";

if (empty($output)) {
    echo "❌ ERROR: Empty output\n";
} else {
    echo "✅ SUCCESS: Got output\n";
    echo "First 500 characters:\n";
    echo substr($output, 0, 500) . "\n";
    
    // Check for errors
    if (strpos($output, 'Error') !== false) {
        echo "⚠️ Contains error messages\n";
    }
    if (strpos($output, 'Exception') !== false) {
        echo "⚠️ Contains exception messages\n";
    }
}
?>
