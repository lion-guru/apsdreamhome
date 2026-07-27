require_once 'C:/xampp/htdocs/apsdreamhome/app/Core/Autoloader.php';
$a = new \App\Core\Autoloader();
$a->register();
$db = \App\Core\Database::getInstance();
$db->connect();
$stmt = $db->getConnection()->prepare('SELECT id, label, url, icon, parent_id, sort_order FROM admin_menu_items WHERE url LIKE ? ORDER BY sort_order');
$stmt->execute(array('%tenant%'));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);

