<?php
define('APS_ROOT', __DIR__);
require __DIR__ . '/config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getPdo();
try {
    $rows = $db->query("SELECT id, name, url, section FROM admin_menu_items WHERE url LIKE '%document%' OR url LIKE '%esign%' OR url LIKE '%landmark%' OR url LIKE '%circle%' OR url LIKE '%stamp%' OR url LIKE '%whatsapp%' ORDER BY section, order_index")->fetchAll(PDO::FETCH_ASSOC);
    echo "MATCHED: " . count($rows) . PHP_EOL;
    foreach($rows as $r) echo $r['section'] . ' | ' . $r['name'] . ' | ' . $r['url'] . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
echo "---LEGAL SECTION---" . PHP_EOL;
try {
    $rows2 = $db->query("SELECT id, name, url, section FROM admin_menu_items WHERE section = 'legal' ORDER BY order_index")->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows2 as $r) echo $r['id'] . ' | ' . $r['name'] . ' | ' . $r['url'] . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
echo "---TECHNOLOGY SECTION---" . PHP_EOL;
try {
    $rows3 = $db->query("SELECT id, name, url, section FROM admin_menu_items WHERE section = 'technology' ORDER BY order_index")->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows3 as $r) echo $r['id'] . ' | ' . $r['name'] . ' | ' . $r['url'] . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
echo "---LOCATIONS SECTION---" . PHP_EOL;
try {
    $rows5 = $db->query("SELECT id, name, url, section FROM admin_menu_items WHERE section = 'locations' ORDER BY order_index")->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows5 as $r) echo $r['id'] . ' | ' . $r['name'] . ' | ' . $r['url'] . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}?>