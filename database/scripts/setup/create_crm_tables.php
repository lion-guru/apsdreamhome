<?php
require_once __DIR__ . '/../../../app/Core/autoload.php';

use App\Core\Database;

$db = Database::getInstance();

echo "Setting up CRM tables...\n";

// 1. Visits Table (Field Employee Tracking)
$sql = "CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id BIGINT UNSIGNED NOT NULL,
    customer_id INT NULL,
    lead_id INT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    location_address VARCHAR(255) NULL,
    notes TEXT NULL,
    status ENUM('completed', 'scheduled', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
)";
try {
    $db->query($sql);
    echo "Table 'visits' created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating 'visits' table: " . $e->getMessage() . "\n";
}

// 2. Associate Appointments
$sql = "CREATE TABLE IF NOT EXISTS associate_appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id BIGINT UNSIGNED NOT NULL,
    customer_id INT NULL,
    lead_id INT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    type ENUM('site_visit', 'office_meeting', 'virtual_meeting', 'other') DEFAULT 'site_visit',
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    notes TEXT NULL,
    location VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
)";
try {
    $db->query($sql);
    echo "Table 'associate_appointments' created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating 'associate_appointments' table: " . $e->getMessage() . "\n";
}

// 3. Associate Activities (Log)
$sql = "CREATE TABLE IF NOT EXISTS associate_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id BIGINT UNSIGNED NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NULL,
    reference_id INT NULL, -- ID of lead, customer, etc.
    reference_type VARCHAR(50) NULL, -- 'lead', 'customer', 'booking'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
)";
try {
    $db->query($sql);
    echo "Table 'associate_activities' created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating 'associate_activities' table: " . $e->getMessage() . "\n";
}

// 4. Associate Messages
$sql = "CREATE TABLE IF NOT EXISTS associate_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id BIGINT UNSIGNED NOT NULL,
    sender_id INT NOT NULL, -- Admin or other user
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(id) ON DELETE CASCADE
)";
try {
    $db->query($sql);
    echo "Table 'associate_messages' created or already exists.\n";
} catch (PDOException $e) {
    echo "Error creating 'associate_messages' table: " . $e->getMessage() . "\n";
}

echo "CRM tables setup complete.\n";
