<?php
$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
if($pdo) {
    echo "✅ Connected successfully!";
} else {
    echo "❌ Connection failed";
}
?>
