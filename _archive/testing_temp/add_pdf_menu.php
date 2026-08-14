<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$menu = [
    'name'          => 'PDF Generator',
    'url'           => '/admin/pdfs',
    'icon'          => 'fas fa-file-pdf',
    'section'       => 'reports',
    'order_index'   => 150,
    'is_active'     => 1,
    'parent_id'     => null,
    'permission_key'=> 'admin',
];

$stmt = $pdo->prepare("SELECT id FROM admin_menu_items WHERE url = ? LIMIT 1");
$stmt->execute(['/admin/pdfs']);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "PDF Generator menu item already exists (id={$existing['id']})\n";
} else {
    $cols = ['name','url','icon','section','order_index','is_active','parent_id','permission_key'];
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $sql = "INSERT INTO admin_menu_items (" . implode(',', $cols) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($menu));
    echo "Inserted PDF Generator menu item (id=" . $pdo->lastInsertId() . ")\n";
}?>