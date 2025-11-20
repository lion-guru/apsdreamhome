<?php
require_once __DIR__ . '/../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1 extra property
$conn->query("INSERT INTO properties (title, description, price, status, created_at) VALUES
('Plot B-205', '1200 sqft corner plot in Sector 22', 3500000, 'Available', NOW())");

// 1 extra booking linked to customer 1 and the new property
$prop_id = $conn->insert_id;
$conn->query("INSERT INTO bookings (customer_id, property_id, booking_date, amount, status, created_at) VALUES
(1, $prop_id, CURDATE(), 500000, 'Confirmed', NOW())");

echo "✅ Added 1 extra property & 1 confirmed booking.\n";

$conn->close();