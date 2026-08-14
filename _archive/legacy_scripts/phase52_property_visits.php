<?php
/**
 * Phase 52: Property Visit Scheduling System
 * Customers book site visits; admins manage schedule
 */
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$db->exec("ALTER TABLE property_visits ADD COLUMN IF NOT EXISTS customer_name VARCHAR(150) NULL");
$db->exec("ALTER TABLE property_visits ADD COLUMN IF NOT EXISTS customer_email VARCHAR(150) NULL");
$db->exec("ALTER TABLE property_visits ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(20) NULL");
$db->exec("ALTER TABLE property_visits ADD COLUMN IF NOT EXISTS feedback_comments TEXT NULL");
$db->exec("ALTER TABLE property_visits ADD COLUMN IF NOT EXISTS cancellation_reason TEXT NULL");
$db->exec("ALTER TABLE property_visits ADD COLUMN IF NOT EXISTS assigned_to BIGINT(20) UNSIGNED NULL");
$db->exec("ALTER TABLE property_visits ADD INDEX IF NOT EXISTS idx_assigned (assigned_to, visit_date)");
$db->exec("ALTER TABLE property_visits ADD INDEX IF NOT EXISTS idx_status_date (status, visit_date)");
echo "OK property_visits enhanced\n";

$db->exec("DROP TABLE IF EXISTS visit_time_slots");
$db->exec("CREATE TABLE visit_time_slots (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT(20) UNSIGNED NULL,
    date DATE NOT NULL,
    time_slot TIME NOT NULL,
    max_bookings INT(11) NOT NULL DEFAULT 3,
    current_bookings INT(11) NOT NULL DEFAULT 0,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_slot (property_id, date, time_slot),
    INDEX idx_date (date, is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK visit_time_slots table created\n";

$db->exec("DROP TABLE IF EXISTS visit_feedback");
$db->exec("CREATE TABLE visit_feedback (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    visit_id INT(11) NOT NULL,
    user_id BIGINT(20) UNSIGNED NULL,
    rating INT(11) NOT NULL,
    agent_rating INT(11) NULL,
    property_rating INT(11) NULL,
    would_recommend TINYINT(1) NULL,
    comments TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_visit (visit_id),
    INDEX idx_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK visit_feedback table created\n";

$stmt = $db->query("SELECT COUNT(*) FROM visit_time_slots");
if ((int)$stmt->fetchColumn() < 30) {
    $db->exec("INSERT INTO visit_time_slots (date, time_slot, max_bookings) VALUES
        (CURDATE() + INTERVAL 1 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 1 DAY, '11:30:00', 3),
        (CURDATE() + INTERVAL 1 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 1 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 2 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 2 DAY, '11:30:00', 3),
        (CURDATE() + INTERVAL 2 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 2 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 3 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 3 DAY, '11:30:00', 3),
        (CURDATE() + INTERVAL 3 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 3 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 4 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 4 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 4 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 5 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 5 DAY, '11:30:00', 3),
        (CURDATE() + INTERVAL 5 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 5 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 6 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 6 DAY, '11:30:00', 3),
        (CURDATE() + INTERVAL 6 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 6 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 7 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 7 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 7 DAY, '16:00:00', 3),
        (CURDATE() + INTERVAL 8 DAY, '10:00:00', 3),
        (CURDATE() + INTERVAL 8 DAY, '11:30:00', 3),
        (CURDATE() + INTERVAL 8 DAY, '14:00:00', 3),
        (CURDATE() + INTERVAL 8 DAY, '16:00:00', 3)
        ON DUPLICATE KEY UPDATE max_bookings=VALUES(max_bookings)");
    echo "OK 30 sample time slots seeded (next 8 days)\n";
} else {
    echo "Time slots already exist\n";
}

echo "DONE\n";?>