<?php
require_once __DIR__ . '/core/init.php';

function cleanupUsers() {
    $db = \App\Core\App::database();

    // Delete users with empty passwords
    $db->execute("DELETE FROM user WHERE upass = ''");

    // Delete users with SHA1 passwords
    $db->execute("DELETE FROM user WHERE LENGTH(upass) = 40 AND upass NOT LIKE '$%'");
}

cleanupUsers();
echo "Users cleaned up successfully.\n";
