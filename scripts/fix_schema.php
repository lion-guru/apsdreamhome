<?php
// Lightweight schema fixer: add missing columns if they don't exist (non-destructive)
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // admin_users: ensure password_hash and status columns exist
  $cols = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='admin_users'");
  $existing = $cols->fetchAll(PDO::FETCH_COLUMN, 0);
  if (!in_array('password_hash', $existing)) {
    $db->exec("ALTER TABLE admin_users ADD COLUMN password_hash VARCHAR(255) NULL");
    echo "Added admin_users.password_hash\n";
  }
  if (!in_array('status', $existing)) {
    $db->exec("ALTER TABLE admin_users ADD COLUMN status VARCHAR(32) NULL");
    echo "Added admin_users.status\n";
  }

  // customers: ensure name, password, created_at exist
  $ccols = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='customers'");
  $cex = $ccols->fetchAll(PDO::FETCH_COLUMN, 0);
  if (!in_array('name', $cex)) {
    $db->exec("ALTER TABLE customers ADD COLUMN name VARCHAR(255) NULL");
    echo "Added customers.name\n";
  }
  if (!in_array('password', $cex)) {
    $db->exec("ALTER TABLE customers ADD COLUMN password VARCHAR(255) NULL");
    echo "Added customers.password\n";
  }
  if (!in_array('created_at', $cex)) {
    $db->exec("ALTER TABLE customers ADD COLUMN created_at TIMESTAMP NULL DEFAULT NULL");
    echo "Added customers.created_at\n";
  }
} catch (PDOException $e) {
  echo 'Schema fix failed: ' . $e->getMessage() . "\n";
}
?>
