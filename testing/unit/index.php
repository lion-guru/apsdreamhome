<?php
/**
 * Unit Tests Index
 */

echo '<h1>Unit Tests Index</h1>';
echo '<p>Directory: testing/unit</p>';

// List all PHP files in this directory
 = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'index.php') {
        echo '<a href="' . $file . '">' . $file . '</a><br>';
    }
}
?>
