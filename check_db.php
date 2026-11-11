<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
echo $db->query('SELECT COUNT(*) FROM users')->fetchColumn() . " users\n";