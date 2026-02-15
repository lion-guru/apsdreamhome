<?php
// Quick debug: Show header_menu_items from DB
require_once __DIR__ . '/../config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die('DB Error');
$res = $conn->query("SELECT value FROM site_settings WHERE setting_name='header_menu_items'");
if ($row = $res->fetch_assoc()) {
    echo '<pre>';
    echo htmlspecialchars($row['value']);
    echo '</pre>';
} else {
    echo 'No menu found in DB.';
}
$conn->close();
?>
