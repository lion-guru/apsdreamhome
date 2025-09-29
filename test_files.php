<?php
$files = [
    'c:/xampp/htdocs/apsdreamhomefinal/includes/enhanced_universal_template.php',
    'c:/xampp/htdocs/apsdreamhomefinal/includes/db_connection.php',
    'c:/xampp/htdocs/apsdreamhomefinal/properties_template.php',
    'c:/xampp/htdocs/apsdreamhomefinal/contact_template.php',
    'c:/xampp/htdocs/apsdreamhomefinal/about_template.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo '✅ ' . basename($file) . ' exists\n';
    } else {
        echo '❌ ' . basename($file) . ' missing\n';
    }
}
?>
