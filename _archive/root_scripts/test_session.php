<?php
session_start();

echo "<h1>Session Debug</h1>";

echo "<h2>Session Variables:</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h2>User Info:</h2>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
echo "User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "<br>";
echo "User Type: " . ($_SESSION['user_type'] ?? 'Not set') . "<br>";
echo "User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "<br>";
echo "Associate Logged In: " . ($_SESSION['associate_logged_in'] ?? 'Not set') . "<br>";
echo "Logged In: " . ($_SESSION['logged_in'] ?? 'Not set') . "<br>";

echo "<h2>BASE_URL:</h2>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'Not defined') . "<br>";

echo "<h2>Page Test:</h2>";
echo "<a href='/associate/dashboard'>Go to Associate Dashboard</a><br>";
echo "<a href='/admin/dashboard'>Go to Admin Dashboard</a><br>";
echo "<a href='/'>Go to Home</a><br>";
?>
