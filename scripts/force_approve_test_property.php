<?php
// Force-approve a test property for admin workflow testing (safe and idempotent)
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Try to find the test property seeded earlier
  $stmt = $db->prepare("SELECT up.id, up.status FROM user_properties up JOIN customers c ON up.user_id = c.id WHERE c.email = :email AND up.name = :name ORDER BY up.created_at DESC LIMIT 1");
  $stmt->execute([':email' => 'testuser@example.com', ':name' => 'Test Property']);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    // Fallback: use the most recent property for the test user
    $stmt = $db->prepare("SELECT id, status FROM user_properties WHERE email = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['testuser@example.com']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  if ($row) {
    $db->prepare("UPDATE user_properties SET status = 'approved' WHERE id = ?")->execute([$row['id']]);
    echo "Property id {$row['id']} status updated to approved\n";
  } else {
    echo "No test property found to approve\n";
  }
} catch (PDOException $e) {
  echo 'DB operation failed: ' . $e->getMessage() . "\n";
}
?>
