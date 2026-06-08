<?php
try {
  $db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root','');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $cols = $db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='apsdreamhome' AND TABLE_NAME='user_properties'");
  $existing = $cols->fetchAll(PDO::FETCH_COLUMN, 0);
  if (!in_array('image', $existing)) {
    $db->exec("ALTER TABLE user_properties ADD COLUMN image VARCHAR(255) NULL AFTER description");
    echo "Added user_properties.image column\n";
  } else {
    echo "user_properties.image already exists\n";
  }
  // Also check area_sqft column
  if (!in_array('area_sqft', $existing)) {
    $db->exec("ALTER TABLE user_properties ADD COLUMN area_sqft INT NULL AFTER address");
    echo "Added user_properties.area_sqft column\n";
  } else {
    echo "user_properties.area_sqft already exists\n";
  }
  echo "Done.\n";
} catch(Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
?>
