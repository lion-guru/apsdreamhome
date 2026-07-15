<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

// Find the property_id for plot 51
$plot = $db->fetch("SELECT * FROM plots WHERE id = 51");
print_r($plot);

$property = $db->fetch("SELECT * FROM properties WHERE id = ?", [$plot['colony_id']]);
print_r($property);

// Check properties table
$props = $db->fetchAll("SELECT id, name FROM properties LIMIT 5");
print_r($props);