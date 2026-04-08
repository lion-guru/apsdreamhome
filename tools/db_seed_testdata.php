<?php
// Safe seed script for test admin and customer data
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';
try {
  $db = new PDO($dsn, $user, $pass);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Admin seed
  if ($db->query("SHOW TABLES LIKE 'admin_users'")->rowCount() > 0) {
    $hash = password_hash('Test@123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = :u");
    $stmt->execute([':u'=>'testadmin']);
    if (!$stmt->fetchColumn()) {
      // Try to insert with status column if available
      try {
        $db->prepare("INSERT INTO admin_users (username, password_hash, status, created_at) VALUES (:u, :p, 'active', NOW())")
          ->execute([':u'=>'testadmin', ':p'=>$hash]);
      } catch (Exception $e) {
        // Fallback without status column
        $db->prepare("INSERT INTO admin_users (username, password_hash, created_at) VALUES (:u, :p, NOW())")
          ->execute([':u'=>'testadmin', ':p'=>$hash]);
      }
      echo "Seeded test admin.\n";
    } else {
      echo "Test admin already exists.\n";
    }
  } else {
    echo "admin_users table not found. Skipping admin seed.\n";
  }

  // Customer seed
  if ($db->query("SHOW TABLES LIKE 'customers'")->rowCount() > 0) {
    $hashUser = password_hash('Test@123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = :e");
    $stmt->execute([':e'=>'testuser@example.com']);
    if (!$stmt->fetchColumn()) {
      // Try with name column if exists
      $cols = $db->query("SHOW COLUMNS FROM customers LIKE 'name'");
      $hasName = $cols && $cols->rowCount() > 0;
      if ($hasName) {
        try {
          $db->prepare("INSERT INTO customers (name, email, phone, password, status, created_at) VALUES (:n, :e, :p, :pw, 'active', NOW())")
            ->execute([':n'=>'Test User', ':e'=>'testuser@example.com', ':p'=>'+9199999999', ':pw'=>$hashUser]);
        } catch (Exception $e) {
          $db->prepare("INSERT INTO customers (name, email, phone, password, created_at) VALUES (:n, :e, :p, :pw, NOW())")
            ->execute([':n'=>'Test User', ':e'=>'testuser@example.com', ':p'=>'+9199999999', ':pw'=>$hashUser]);
        }
      } else {
        // Fallback: insert without name
        $db->prepare("INSERT INTO customers (email, phone, password, created_at) VALUES (:e, :p, :pw, NOW())")
          ->execute([':e'=>'testuser@example.com', ':p'=>'+9199999999', ':pw'=>$hashUser]);
      }
      echo "Seeded test customer.\n";
    } else {
      echo "Test customer already exists.\n";
    }
  } else {
    echo "customers table not found. Skipping customer seed.\n";
  }

  // Seed a test property (pending) for admin approval flow if user exists
  if ($db->query("SHOW TABLES LIKE 'user_properties'")->rowCount() > 0) {
    // Attempt to find test user id
    $uidStmt = $db->prepare("SELECT id FROM customers WHERE email = :e LIMIT 1");
    $uidStmt->execute([':e' => 'testuser@example.com']);
    $uid = $uidStmt->fetchColumn();
    if ($uid) {
      // Check if a test property already exists for this user
      $exists = $db->query("SELECT 1 FROM user_properties WHERE email = 'testuser@example.com' LIMIT 1");
      if (!$exists || $exists->rowCount() == 0) {
        $db->prepare("INSERT INTO user_properties (user_id, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, views, inquiries, created_at) VALUES (:uid, :n, :p, :e, :pt, :lt, :addr, :area, :price, :ptype, :desc, 'pending', 0, 0, NOW())")
          ->execute([
            ':uid' => $uid,
            ':n' => 'Test Property',
            ':p' => '+9199999999',
            ':e' => 'testuser@example.com',
            ':pt' => 'Plot',
            ':lt' => 'Sell',
            ':addr' => 'Auto seed address',
            ':area' => 1000,
            ':price' => 1000000,
            ':ptype' => 'INR',
            ':desc' => 'Auto seed property for admin approval test'
          ]);
        echo "Seeded test property for admin approval.\n";
      } else {
        echo "Test property already exists.\n";
      }
    } else {
      echo "Test user for property seed not found. Skipping property seed.\n";
    }
  } else {
    echo "user_properties table not found. Skipping property seed.\n";
  }
} catch (PDOException $e) {
  echo 'DB Seed failed: ' . $e->getMessage() . "\n";
}
?>
