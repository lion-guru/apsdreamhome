<?php
// Migration: Employee Geo Check-In system — zones, check-ins, pings (idempotent, S690)
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;
$pdo = Database::getInstance()->getConnection();

echo "=== Employee Geo Check-In migration ===\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS employee_work_zones (
  id int(11) NOT NULL AUTO_INCREMENT,
  tenant_id int(10) unsigned NOT NULL DEFAULT '1',
  employee_id bigint(20) unsigned DEFAULT NULL,
  name varchar(150) NOT NULL,
  zone_type enum('office','site','client_site') NOT NULL DEFAULT 'office',
  latitude decimal(10,7) NOT NULL,
  longitude decimal(10,7) NOT NULL,
  radius_m int(11) NOT NULL DEFAULT '150',
  selfie_required tinyint(1) NOT NULL DEFAULT '0',
  is_active tinyint(1) NOT NULL DEFAULT '1',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_tenant (tenant_id),
  KEY idx_employee (employee_id),
  KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
echo "zones table OK\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS employee_checkins (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  tenant_id int(10) unsigned NOT NULL DEFAULT '1',
  employee_id bigint(20) unsigned NOT NULL,
  work_date date NOT NULL,
  check_in_at datetime NOT NULL DEFAULT current_timestamp(),
  check_out_at datetime DEFAULT NULL,
  check_in_lat decimal(10,7) DEFAULT NULL,
  check_in_lng decimal(10,7) DEFAULT NULL,
  check_in_accuracy_m float DEFAULT NULL,
  check_in_altitude_m float DEFAULT NULL,
  check_in_speed_kmh float DEFAULT NULL,
  is_mock_gps tinyint(1) NOT NULL DEFAULT '0',
  inside_zone tinyint(1) NOT NULL DEFAULT '1',
  zone_name varchar(150) DEFAULT NULL,
  distance_from_zone_m float DEFAULT NULL,
  selfie_path varchar(255) DEFAULT NULL,
  status enum('valid','out_of_zone','mock_gps','review_required','approved','rejected') NOT NULL DEFAULT 'valid',
  work_log text DEFAULT NULL,
  reviewed_by bigint(20) unsigned DEFAULT NULL,
  reviewed_at datetime DEFAULT NULL,
  review_note varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uniq_tenant_employee_date (tenant_id, employee_id, work_date),
  KEY idx_status (status),
  KEY idx_employee_date (employee_id, work_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
echo "checkins table OK\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS employee_pings (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  tenant_id int(10) unsigned NOT NULL DEFAULT '1',
  employee_id bigint(20) unsigned NOT NULL,
  work_date date NOT NULL,
  ping_at datetime NOT NULL DEFAULT current_timestamp(),
  latitude decimal(10,7) NOT NULL,
  longitude decimal(10,7) NOT NULL,
  accuracy_m float DEFAULT NULL,
  speed_kmh float DEFAULT NULL,
  is_mock_gps tinyint(1) NOT NULL DEFAULT '0',
  source enum('field','office','auto') NOT NULL DEFAULT 'field',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_emp_date (employee_id, work_date),
  KEY idx_date (work_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
echo "pings table OK\n";

$zones = [
  ['Kunraghat Office (Gorakhpur)', 'office', 26.8402340, 83.3012250, 150, 0],
  ['Braj Radha Nagri — Field Site', 'site', 26.8500000, 83.4500000, 250, 1],
  ['Raghunath Nagri — Field Site', 'site', 27.1000000, 83.3000000, 250, 1],
  ['Budh Bihar Colony — Field Site', 'site', 26.7800000, 83.4000000, 250, 1],
  ['Suryoday Colony — Field Site', 'site', 26.8402340, 83.3012250, 250, 1],
];
foreach ($zones as $z) {
    [$name, $type, $lat, $lng, $radius, $selfie] = $z;
    $row = $pdo->prepare('SELECT id FROM employee_work_zones WHERE name = ? AND tenant_id = 1');
    $row->execute([$name]);
    if ($row->fetchColumn()) {
        echo "SKIP zone: $name (exists)\n";
        continue;
    }
    $ins = $pdo->prepare('INSERT INTO employee_work_zones (tenant_id, name, zone_type, latitude, longitude, radius_m, selfie_required, is_active) VALUES (1, ?, ?, ?, ?, ?, ?, 1)');
    $ins->execute([$name, $type, $lat, $lng, $radius, $selfie]);
    echo "SEEDED zone: $name\n";
}

// Record migration
$mig = $pdo->prepare('SELECT COUNT(*) FROM schema_migrations WHERE migration_name = ?');
$mig->execute(['create_employee_geocheckin_s690']);
if (!$mig->fetchColumn()) {
    try {
        $ins = $pdo->prepare('INSERT INTO schema_migrations (migration_name, executed_at) VALUES (?, NOW())');
        $ins->execute(['create_employee_geocheckin_s690']);
        echo "Registered in schema_migrations\n";
    } catch (\Throwable $e) {
        echo "schema_migrations insert skipped: " . $e->getMessage() . "\n";
    }
}
echo "=== done ===\n";
