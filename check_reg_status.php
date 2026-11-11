<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
$r = $db->query("SHOW COLUMNS FROM users LIKE 'registration_status'")->fetch();
if ($r) {
    echo "EXISTS: " . $r[0] . " (" . $r[1] . ")\n";
} else {
    echo "NOT FOUND\n";
}