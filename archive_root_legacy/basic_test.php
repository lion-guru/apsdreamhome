<?php
// Ultra-simple test - APS Dream Home
// This is the most basic test possible

echo "<!DOCTYPE html>
<html>
<head>
    <title>Basic Test</title>
</head>
<body>
    <h1>✅ BASIC TEST WORKING!</h1>
    <p>PHP Version: " . PHP_VERSION . "</p>
    <p>Server Time: " . date('Y-m-d H:i:s') . "</p>
    <p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>
    <p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>
    <p>Status: Server is working correctly!</p>
</body>
</html>";
?>
