<?php
require_once 'public/index.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$sql = file_get_contents('scripts/create_whatsapp_tables.sql');
$statements = explode(';', $sql);
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if ($stmt && !str_starts_with($stmt, 'SELECT')) {
        try { $db->exec($stmt); echo "OK: " . substr($stmt, 0, 50) . "..." . PHP_EOL; } catch (\Exception $e) { echo 'Error: ' . $e->getMessage() . PHP_EOL; }
    }
}
$count = $db->query('SELECT COUNT(*) c FROM whatsapp_templates')->fetch(\PDO::FETCH_ASSOC)['c'];
echo 'Templates created: ' . $count . PHP_EOL;?>