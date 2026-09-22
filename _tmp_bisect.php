<?php
echo "a\n"; flush();
require 'config/bootstrap.php';
echo "b\n"; flush();
$db = \App\Core\Database\Database::getInstance();
echo "c\n"; flush();
$conn = $db->getConnection();
echo "d\n"; flush();
echo 'users=' . $conn->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
echo "e\n";
