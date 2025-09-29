<?php
// Run this script ONCE to update your menu in the database!
// Place this file in your web root and open it in the browser, then delete it after use.

$mysqli = new mysqli('localhost', 'root', '', 'realestatephp');
if ($mysqli->connect_errno) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$json = '[
  {"text": "Home", "url": "/", "icon": "fa-home"},
  {"text": "Properties", "url": "/property-listings.php", "icon": "fa-building"},
  {"text": "About", "url": "/about.php", "icon": "fa-info-circle"},
  {"text": "Contact", "url": "/contact.php", "icon": "fa-envelope"},
  {"text": "Blog/News", "url": "/blog.php", "icon": "fa-newspaper"},
  {"text": "Feedback", "url": "/submit_feedback.php", "icon": "fa-comments"},
  {"text": "Register", "url": "/register.php", "icon": "fa-user-plus"},
  {"text": "Login", "url": "/login.php", "icon": "fa-sign-in-alt"},
  {"text": "Dashboard", "url": "/user_dashboard.php", "icon": "fa-tachometer-alt"},
  {"text": "Logout", "url": "/logout.php", "icon": "fa-sign-out-alt"}
]';

$sql = "UPDATE site_settings SET value = '" . $mysqli->real_escape_string($json) . "' WHERE setting_name = 'header_menu_items'";

if ($mysqli->query($sql)) {
    echo '<h2>Menu updated successfully!</h2>';
} else {
    echo '<h2>Error updating menu: ' . $mysqli->error . '</h2>';
}

$mysqli->close();
?>
