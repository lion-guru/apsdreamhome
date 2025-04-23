<?php
// Super simple DB seeder for testing user/admin/associate/customer data
require_once(__DIR__ . '/../includes/db_config.php');
$conn = getDbConnection();
if (!$conn) { die('Database connection failed.'); }

$pw = password_hash('Aps@123', PASSWORD_DEFAULT);

// Seed admin table
$conn->query("DELETE FROM admin");
$conn->query("INSERT INTO admin (auser, apass, role, status, email, phone) VALUES
    ('superadmin', '$pw', 'superadmin', 'active', 'superadmin@aps.com', '9999999999'),
    ('admin1', '$pw', 'admin', 'active', 'admin1@aps.com', '8888888888'),
    ('finance1', '$pw', 'finance', 'active', 'finance1@aps.com', '7777777777'),
    ('associate1', '$pw', 'associate', 'active', 'associate1@aps.com', '6666666666')
");

// Seed users table
$conn->query("DELETE FROM users");
$conn->query("INSERT INTO users (name, email, phone, type, status, address) VALUES
    ('Customer One', 'cust1@aps.com', '1111111111', 'customer', 'active', 'Jaipur'),
    ('Investor One', 'invest1@aps.com', '2222222222', 'investor', 'active', 'Delhi'),
    ('Tenant One', 'tenant1@aps.com', '3333333333', 'tenant', 'inactive', 'Mumbai')
");

// Seed associates table
$conn->query("DELETE FROM associates");
$conn->query("INSERT INTO associates (name, email, phone, commission_percent, level, status) VALUES
    ('Associate Alpha', 'alpha@aps.com', '4444444444', 5.0, 1, 'active'),
    ('Associate Beta', 'beta@aps.com', '5555555555', 3.0, 2, 'active')
");

// Done
header('Content-Type: text/plain');
echo "Test data seeded successfully!\n";
