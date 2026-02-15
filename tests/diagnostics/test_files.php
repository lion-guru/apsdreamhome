<?php
$files = [
    'c:/xampp/htdocs/apsdreamhome/includes/enhanced_universal_template.php',
    'c:/xampp/htdocs/apsdreamhome/includes/db_connection.php',
    'c:/xampp/htdocs/apsdreamhome/properties_template.php',
    'c:/xampp/htdocs/apsdreamhome/contact_template.php',
    'c:/xampp/htdocs/apsdreamhome/about_template.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo '✅ ' . basename($file) . ' exists\n';
    } else {
        echo '❌ ' . basename($file) . ' missing\n';
    }
}
?>
