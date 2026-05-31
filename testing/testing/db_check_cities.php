<?php
try {
  $db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root','');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $r = $db->query('SHOW TABLES LIKE "cities"');
  echo 'cities: ' . ($r->rowCount() > 0 ? 'exists' : 'missing') . "\n";
  // Check user_properties columns
  $cols = $db->query('DESCRIBE user_properties');
  $fields = $cols->fetchAll(PDO::FETCH_COLUMN, 0);
  echo "user_properties columns: " . implode(', ', $fields) . "\n";
  // Check if state_id and district_id exist
  echo "has state_id: " . (in_array('state_id', $fields) ? 'YES' : 'NO') . "\n";
  echo "has district_id: " . (in_array('district_id', $fields) ? 'YES' : 'NO') . "\n";
  echo "has city_id: " . (in_array('city_id', $fields) ? 'YES' : 'NO') . "\n";
} catch(Exception $e) {
  echo 'Error: ' . $e->getMessage() . "\n";
}
?>
