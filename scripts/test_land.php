<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/../config/bootstrap.php';
$pdo = App\Core\Database\Database::getInstance()->getConnection();
$rows = $pdo->query("SELECT * FROM land_records")->fetchAll(PDO::FETCH_ASSOC);
echo "Rows: " . count($rows) . "\n";
foreach($rows as $r){
  echo "Row: {$r['id']} {$r['land_title']}\n";
}
echo "Done\n";