<?php
/**
 * Simple test script to verify password hashing and verification
 */

$password = 'password123';
$hash = '$argon2id$v=19$m=65536,t=4,p=3$dHdMT2ZIZjM2NEdqcXE5Vw$jG8bo679PP03JvWhxGW0hNBkbTmzxfvSF73T9t3NTas';

echo "Testing password verification\n";
echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";

if (password_verify($password, $hash)) {
    echo "Password verification successful!\n";
} else {
    echo "Password verification failed!\n";
}

// Test creating a new hash
echo "\nTesting password hashing\n";
$new_hash = password_hash($password, PASSWORD_DEFAULT);
echo "New hash: " . $new_hash . "\n";

if (password_verify($password, $new_hash)) {
    echo "New password verification successful!\n";
} else {
    echo "New password verification failed!\n";
}
?>