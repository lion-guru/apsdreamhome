<?php
/**
 * Integration Tests Index
 */

echo '<h1>Integration Tests Index</h1>';
echo '<p>Directory: testing/integration</p>';

// List all PHP files in this directory
 = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
?>
