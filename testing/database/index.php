<?php
/**
 * Database Tests Index
 */

echo '<h1>Database Tests Index</h1>';
echo '<p>Directory: testing/database</p>';

// List all PHP files in this directory
 = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
?>
