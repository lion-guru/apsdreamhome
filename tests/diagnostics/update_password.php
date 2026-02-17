<?php
/**
 * Script to update the password for the test user
 */

require_once __DIR__ . '/../../app/core/autoload.php';
use App\Core\App;
$db = \App\Core\App::database();

// Hash a known password
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "New password hash: " . $hashed_password . "\n";

// Update the password in the database
$mobile = '9123456789';
$updated = $db->execute(
    "UPDATE mlm_agents SET password = :password WHERE mobile = :mobile",
    ['password' => $hashed_password, 'mobile' => $mobile]
);

if ($updated) {
    echo "Password updated successfully!\n";
} else {
    echo "Error updating password.\n";
}
?>