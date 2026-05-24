<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8', 'root', '');
echo "=== admin_menu_items columns ===\n";
foreach ($db->query('SHOW COLUMNS FROM admin_menu_items') as $c) {
    echo $c['Field'] . ' (' . $c['Type'] . ')';
    if ($c['Key']) echo ' [KEY:' . $c['Key'] . ']';
    echo "\n";
}

echo "\n=== Sample rows ===\n";
$stmt = $db->query('SELECT * FROM admin_menu_items LIMIT 3');
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($r);
}
