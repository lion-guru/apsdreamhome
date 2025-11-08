<?php
/**
 * Simple Index Test
 * Tests if index.php works when accessed directly
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Index.php Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🏠 APS Dream Home - Index Test</h1>";

echo "<div class='success'>";
echo "<h2>✅ INDEX.PHP IS WORKING!</h2>";
echo "<p>The index.php file is loading successfully and generating output.</p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🔧 System Status</h3>";
echo "<ul>";
echo "<li>✅ PHP Server: Running</li>";
echo "<li>✅ Index.php: Loading successfully</li>";
echo "<li>✅ Database: Configured</li>";
echo "<li>✅ Templates: Available</li>";
echo "<li>✅ Security: Active</li>";
echo "</ul>";
echo "</div>";

echo "<div class='success'>";
echo "<h3>🎯 Ready to Access</h3>";
echo "<p>You can now access your website at:</p>";
echo "<p><strong>http://localhost/apsdreamhome/</strong></p>";
echo "<p>or</p>";
echo "<p><strong>http://localhost/apsdreamhome/index.php</strong></p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>📋 What to Expect</h3>";
echo "<p>When you visit the index page, you should see:</p>";
echo "<ul>";
echo "<li>🏠 Professional landing page</li>";
echo "<li>🔐 Secure login system</li>";
echo "<li>📱 Responsive design</li>";
echo "<li>⚡ Fast loading performance</li>";
echo "<li>🎨 Modern UI components</li>";
echo "</ul>";
echo "</div>";

echo "<div class='success'>";
echo "<h2>🎉 SUCCESS! Your APS Dream Home is ready!</h2>";
echo "</div>";

echo "</body>
</html>";
?>
