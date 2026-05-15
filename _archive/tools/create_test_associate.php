<?php
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  die("DB connection failed: " . $e->getMessage() . "\n");
}

$stmt = $db->query("SELECT id, name, email, user_type, role FROM users WHERE user_type = 'associate' LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) > 0) {
  foreach ($rows as $r) {
    echo json_encode($r) . "\n";
  }
  echo "Test associate already exists.\n";
  exit(0);
}

$hash = password_hash('password123', PASSWORD_DEFAULT);
$db->prepare("INSERT INTO users (name, email, phone, password, user_type, role, status, referral_code, created_at) VALUES (?, ?, ?, ?, 'associate', 'associate', 'active', ?, NOW())")->execute(['Test Associate', 'associate@test.com', '9999999991', $hash, 'TEST001']);
echo "Created test associate: associate@test.com / password123\n";

$uid = $db->lastInsertId();
try {
  $db->prepare("INSERT INTO wallet_points (user_id, points_balance, total_earned, total_used, referral_earnings, commission_earnings, bonus_earnings, status, created_at, updated_at) VALUES (?, 0, 0, 0, 0, 0, 0, 'active', NOW(), NOW())")->execute([$uid]);
  echo "Wallet created.\n";
} catch (Exception $e) {
  echo "Wallet note (ok if exists): " . $e->getMessage() . "\n";
}
