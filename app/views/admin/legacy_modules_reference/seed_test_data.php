<?php
// Super simple DB seeder for testing user/admin/associate/customer data
require_once(__DIR__ . '/core/init.php');
$db = \App\Core\App::database();

$pw = password_hash('Aps@123', PASSWORD_DEFAULT);

// Seed admin table
$db->execute("DELETE FROM admin");
$db->execute("INSERT INTO admin (auser, apass, role, status, email, phone) VALUES
    ('superadmin', ?, 'superadmin', 'active', 'superadmin@aps.com', '9999999999'),
    ('admin1', ?, 'admin', 'active', 'admin1@aps.com', '8888888888'),
    ('finance1', ?, 'finance', 'active', 'finance1@aps.com', '7777777777'),
    ('associate1', ?, 'associate', 'active', 'associate1@aps.com', '6666666666')
", [$pw, $pw, $pw, $pw]);

// Seed user table
$db->execute("DELETE FROM user");
$db->execute("INSERT INTO user (uname, uemail, uphone, utype, upass) VALUES
    ('Customer One', 'cust1@aps.com', '1111111111', '3', ?),
    ('Investor One', 'invest1@aps.com', '2222222222', '4', ?),
    ('Tenant One', 'tenant1@aps.com', '3333333333', '5', ?)
", [$pw, $pw, $pw]);

// Seed associates table
$db->execute("DELETE FROM associates");
$db->execute("INSERT INTO associates (name, email, phone, commission_percent, level, status) VALUES
    ('Associate Alpha', 'alpha@aps.com', '4444444444', 5.0, 1, 'active'),
    ('Associate Beta', 'beta@aps.com', '5555555555', 3.0, 2, 'active')
");

// Done
header('Content-Type: text/plain');
echo "Test data seeded successfully!\n";
