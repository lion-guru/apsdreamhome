<?php
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$db = new PDO($dsn, 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tables = ['customers','user_properties','newsletter_subscribers','service_interests','inquiries','projects','districts','states','users','admin_users'];
foreach ($tables as $t) {
  $stmt = $db->query("SHOW TABLES LIKE '$t'");
  $exists = $stmt && $stmt->rowCount() > 0;
  echo $t . ' => ' . ($exists ? 'exists' : 'missing') . PHP_EOL;
}
?>
