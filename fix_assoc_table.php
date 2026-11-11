<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
try {
    $db->exec("ALTER TABLE associate_profiles ADD COLUMN pan_number VARCHAR(20), ADD COLUMN aadhaar_number VARCHAR(20), ADD COLUMN bank_account VARCHAR(50), ADD COLUMN bank_ifsc VARCHAR(20)");
    echo "associate_profiles columns added OK\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}