<?php
// Quick debug: Show header_menu_items from DB
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();
$row = $db->fetchOne("SELECT value FROM site_settings WHERE setting_name='header_menu_items'");
if ($row) {
    echo '<pre>';
    echo h($row['value']);
    echo '</pre>';
} else {
    echo 'No menu found in DB.';
}
?>
