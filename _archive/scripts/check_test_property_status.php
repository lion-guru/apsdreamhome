<?php
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $stmt = $db->prepare("SELECT id, status FROM user_properties WHERE email = ? AND name = ? ORDER BY created_at DESC LIMIT 1");
  $stmt->execute(['testuser@example.com','Test Property']);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    $stmt = $db->prepare("SELECT id, status FROM user_properties WHERE email = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['testuser@example.com']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  if ($row) {
    echo 'Property: ' . json_encode($row) . "\n";
  } else {
    echo 'No test property found' . "\n";
  }
} catch (PDOException $e) {
  echo 'Error: ' . $e->getMessage() . "\n";
}
?>
