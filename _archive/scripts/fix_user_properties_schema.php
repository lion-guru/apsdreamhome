<?php
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Get current columns of user_properties
  $cols = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='user_properties'");
  $existing = $cols->fetchAll(PDO::FETCH_COLUMN, 0);
  echo "Current user_properties columns: " . implode(', ', $existing) . "\n";

  // Add missing columns if they don't exist
  $adds = [
    'state_id' => "ALTER TABLE user_properties ADD COLUMN state_id INT NULL",
    'district_id' => "ALTER TABLE user_properties ADD COLUMN district_id INT NULL",
    'city_id' => "ALTER TABLE user_properties ADD COLUMN city_id INT NULL",
  ];
  foreach ($adds as $col => $sql) {
    if (!in_array($col, $existing)) {
      $db->exec($sql);
      echo "Added column: $col\n";
    } else {
      echo "Column already exists: $col\n";
    }
  }

  // Check/create cities table
  $citiesCheck = $db->query("SHOW TABLES LIKE 'cities'");
  if ($citiesCheck->rowCount() == 0) {
    $db->exec("CREATE TABLE IF NOT EXISTS cities (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      district_id INT NULL,
      state_id INT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created cities table\n";
  } else {
    echo "cities table already exists\n";
  }

  // Verify
  $cols2 = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='user_properties'");
  $final = $cols2->fetchAll(PDO::FETCH_COLUMN, 0);
  echo "Final user_properties columns: " . implode(', ', $final) . "\n";
} catch (PDOException $e) {
  echo 'Schema fix failed: ' . $e->getMessage() . "\n";
}
?>
