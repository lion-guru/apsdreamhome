<?php
// Database connection test
try {
    $db = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
    echo "✅ Database connection successful!\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>