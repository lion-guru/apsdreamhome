<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';

function cleanupUsers() {
    $db = new Database();

    // Delete users with empty passwords
    $db->delete('users', "password = ''");

    // Delete users with SHA1 passwords
    $db->delete('users', "LENGTH(password) = 40 AND password NOT LIKE '$%'");
}

cleanupUsers();
echo "Users cleaned up successfully.\n";