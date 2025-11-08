<?php
// Test routing configuration

// Test 1: Check if .htaccess is working
echo "<h2>Testing .htaccess Configuration</h2>";
$htaccess = file_get_contents('.htaccess');
if (strpos($htaccess, 'RewriteEngine On') !== false) {
    echo "<p style='color:green;'>✓ .htaccess is properly configured</p>";
} else {
    echo "<p style='color:red;'>✗ .htaccess is not properly configured</p>";
}

// Test 2: Check if mod_rewrite is enabled
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color:green;'>✓ mod_rewrite is enabled</p>";
    } else {
        echo "<p style='color:red;'>✗ mod_rewrite is not enabled</p>";
    }
} else {
    echo "<p>⚠ Could not check mod_rewrite status (not running as Apache module)</p>";
}

// Test 3: Check if router.php exists
if (file_exists('router.php')) {
    echo "<p style='color:green;'>✓ router.php exists</p>";
    
    // Include the router to test its functions
    require_once 'router.php';
    
    // Test 4: Check if routes array is defined
    if (isset($routes) && is_array($routes)) {
        echo "<p style='color:green;'>✓ Routes array is properly defined with " . count($routes) . " routes</p>";
        
        // Test 5: Check if home route exists
        if (isset($routes['home'])) {
            echo "<p style='color:green;'>✓ Home route is defined: " . htmlspecialchars($routes['home']) . "</p>";
        } else {
            echo "<p style='color:red;'>✗ Home route is not defined</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ Routes array is not properly defined</p>";
    }
} else {
    echo "<p style='color:red;'>✗ router.php does not exist</p>";
}

// Test 6: Check if support.php is accessible
echo "<h2>Testing Support Page</h2>";
if (file_exists('support.php')) {
    echo "<p style='color:green;'>✓ support.php exists</p>";
    
    // Try to include the file to check for syntax errors
    ob_start();
    $support_included = @include 'support.php';
    $output = ob_get_clean();
    
    if ($support_included === false) {
        echo "<p style='color:red;'>✗ support.php has syntax errors</p>";
    } else {
        echo "<p style='color:green;'>✓ support.php is valid PHP</p>";
    }
    
    // Test direct access
    $support_url = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome/support.php';
    $headers = @get_headers($support_url);
    
    if ($headers && strpos($headers[0], '200')) {
        echo "<p style='color:green;'>✓ support.php is accessible via direct URL</p>";
    } else {
        echo "<p style='color:orange;'>⚠ support.php returned: " . ($headers[0] ?? 'No response') . "</p>";
        echo "<p>Try accessing it directly: <a href='$support_url' target='_blank'>$support_url</a></p>";
    }
} else {
    echo "<p style='color:red;'>✗ support.php does not exist</p>";
}

// Test 7: Check if 404 page works
echo "<h2>Testing 404 Page</h2>";
$test_url = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome/nonexistent-page';
$context = stream_context_create(['http' => ['ignore_errors' => true]]);
$response = @file_get_contents($test_url, false, $context);

if ($http_response_header) {
    $status_line = $http_response_header[0];
    preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
    $status = $match[1];
    
    if ($status == '404') {
        echo "<p style='color:green;'>✓ Custom 404 page is working</p>";
    } else {
        echo "<p style='color:orange;'>⚠ Expected 404 but got: $status</p>";
    }
} else {
    echo "<p style='color:red;'>✗ Could not test 404 page</p>";
}

// Display test links
echo "<h2>Test Links</h2>";
echo "<ul>";
echo "<li><a href='/apsdreamhome/' target='_blank'>Homepage</a></li>";
echo "<li><a href='/apsdreamhome/support' target='_blank'>Support Page (routed)</a></li>";
echo "<li><a href='/apsdreamhome/support.php' target='_blank'>Support Page (direct)</a></li>";
echo "<li><a href='/apsdreamhome/nonexistent-page' target='_blank'>Test 404 Page</a></li>";
echo "</ul>";
?>
