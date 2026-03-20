<?php
require_once 'app/Core/Database/Database.php';

$db = App\Core\Database\Database::getInstance();

// Set password for test employee
$password = password_hash('employee123', PASSWORD_DEFAULT);
$db->execute(
    'UPDATE users SET password = ? WHERE email = ?',
    [$password, 'test_1771178655@example.com']
);

echo "Password set for test_1771178655@example.com\n";
echo "Login credentials:\n";
echo "Email: test_1771178655@example.com\n";
echo "Password: employee123\n";
?>