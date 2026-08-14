<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// RERA projects table
$pdo->exec("
CREATE TABLE IF NOT EXISTS rera_projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rera_number VARCHAR(50) NOT NULL,
    state_code VARCHAR(10) NOT NULL,
    project_name VARCHAR(255) NOT NULL,
    builder_name VARCHAR(255) NOT NULL,
    builder_license VARCHAR(100) DEFAULT '',
    project_type ENUM('Residential Plotted', 'Residential Apartments', 'Commercial', 'Mixed Use', 'Industrial') DEFAULT 'Residential Plotted',
    status ENUM('Registered', 'Under Construction', 'Completed', 'Cancelled', 'Suspended') DEFAULT 'Registered',
    registration_date DATE DEFAULT NULL,
    validity_date DATE DEFAULT NULL,
    city VARCHAR(100) DEFAULT '',
    district VARCHAR(100) DEFAULT '',
    area_sqm DECIMAL(15,2) DEFAULT 0,
    total_units INT DEFAULT 0,
    address TEXT DEFAULT '',
    latitude DECIMAL(10,8) DEFAULT 0,
    longitude DECIMAL(11,8) DEFAULT 0,
    completion_percentage DECIMAL(5,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_rera_state (rera_number, state_code),
    KEY idx_state (state_code),
    KEY idx_city (city),
    KEY idx_builder (builder_name),
    KEY idx_status (status),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "rera_projects table created/verified\n";

// RERA documents table
$pdo->exec("
CREATE TABLE IF NOT EXISTS rera_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    document_type ENUM('registration_certificate', 'layout_plan', 'approval_letter', 'encumbrance_certificate', 'title_deed', 'environmental_clearance', 'fire_noc', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    document_path VARCHAR(500) DEFAULT '',
    document_url TEXT DEFAULT '',
    file_size INT DEFAULT 0,
    mime_type VARCHAR(100) DEFAULT '',
    is_public TINYINT(1) DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES rera_projects(id) ON DELETE CASCADE,
    KEY idx_project (project_id),
    KEY idx_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "rera_documents table created/verified\n";

// RERA milestones table
$pdo->exec("
CREATE TABLE IF NOT EXISTS rera_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    milestone_name VARCHAR(255) NOT NULL,
    milestone_type ENUM('registration', 'layout_approval', 'construction_start', 'plinth_completion', 'structure_completion', 'finishing_start', 'completion_certificate', 'occupancy_certificate', 'handover') NOT NULL,
    planned_date DATE DEFAULT NULL,
    actual_date DATE DEFAULT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'delayed') DEFAULT 'pending',
    remarks TEXT DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES rera_projects(id) ON DELETE CASCADE,
    KEY idx_project (project_id),
    KEY idx_type (milestone_type),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "rera_milestones table created/verified\n";

// Seed sample data
$projects = [
    [
        'UPRERAPRJ12345', 'UP', 'Suryoday Enclave', 'APS Dream Home Developers', 'UPBL12345',
        'Residential Plotted', 'Registered', '2024-01-15', '2029-01-14',
        'Gorakhpur', 'Gorakhpur', 50000, 200, 'NH-29, Gorakhpur',
        26.7606, 83.3732, 65
    ],
    [
        'UPRERAPRJ67890', 'UP', 'Braj Radha Nagri', 'APS Dream Home Developers', 'UPBL12345',
        'Residential Plotted', 'Registered', '2024-03-20', '2029-03-19',
        'Gorakhpur', 'Gorakhpur', 40000, 150, 'Deoria Road, Gorakhpur',
        26.7200, 83.3500, 40
    ],
    [
        'UPRERAPRJ11111', 'UP', 'Raghunath Nagri', 'APS Dream Home Developers', 'UPBL12345',
        'Residential Plotted', 'Registered', '2023-11-10', '2028-11-09',
        'Gorakhpur', 'Gorakhpur', 200000, 800, 'Kushinagar Road, Gorakhpur',
        26.8000, 83.4000, 80
    ],
    [
        'UPRERAPRJ22222', 'UP', 'Budh Bihar Colony', 'APS Dream Home Developers', 'UPBL12345',
        'Residential Plotted', 'Under Construction', '2022-06-01', '2027-05-31',
        'Gorakhpur', 'Gorakhpur', 120000, 400, 'Budh Bihar Road, Gorakhpur',
        26.7800, 83.3800, 90
    ],
];

$stmt = $pdo->prepare("
    INSERT INTO rera_projects 
    (rera_number, state_code, project_name, builder_name, builder_license, 
     project_type, status, registration_date, validity_date, city, district, 
     area_sqm, total_units, address, latitude, longitude, completion_percentage, 
     created_at, updated_at, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)
    ON DUPLICATE KEY UPDATE
        project_name = VALUES(project_name),
        builder_name = VALUES(builder_name),
        builder_license = VALUES(builder_license),
        project_type = VALUES(project_type),
        status = VALUES(status),
        registration_date = VALUES(registration_date),
        validity_date = VALUES(validity_date),
        city = VALUES(city),
        district = VALUES(district),
        area_sqm = VALUES(area_sqm),
        total_units = VALUES(total_units),
        address = VALUES(address),
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        completion_percentage = VALUES(completion_percentage),
        updated_at = NOW()
");

$inserted = 0;
foreach ($projects as $p) {
    try {
        $stmt->execute($p);
        $inserted++;
    } catch (Exception $e) {
        // Ignore
    }
}

echo "Inserted/Updated $inserted sample RERA projects\n";

// Sample documents
$documents = [
    ['UPRERAPRJ12345', 'registration_certificate', 'Registration Certificate', 'docs/rera/UPRERAPRJ12345_reg_cert.pdf', 1024000, 'application/pdf', 1],
    ['UPRERAPRJ12345', 'layout_plan', 'Approved Layout Plan', 'docs/rera/UPRERAPRJ12345_layout.pdf', 2048000, 'application/pdf', 1],
    ['UPRERAPRJ12345', 'approval_letter', 'Layout Approval Letter', 'docs/rera/UPRERAPRJ12345_approval.pdf', 512000, 'application/pdf', 1],
    ['UPRERAPRJ67890', 'registration_certificate', 'Registration Certificate', 'docs/rera/UPRERAPRJ67890_reg_cert.pdf', 1024000, 'application/pdf', 1],
    ['UPRERAPRJ11111', 'registration_certificate', 'Registration Certificate', 'docs/rera/UPRERAPRJ11111_reg_cert.pdf', 1024000, 'application/pdf', 1],
    ['UPRERAPRJ22222', 'registration_certificate', 'Registration Certificate', 'docs/rera/UPRERAPRJ22222_reg_cert.pdf', 1024000, 'application/pdf', 1],
];

$stmt = $pdo->prepare("
    INSERT INTO rera_documents (project_id, document_type, document_name, document_path, file_size, mime_type, is_public)
    SELECT id, ?, ?, ?, ?, ?, 1 FROM rera_projects WHERE rera_number = ?
    ON DUPLICATE KEY UPDATE document_name = VALUES(document_name)
");

$docInserted = 0;
foreach ($documents as $doc) {
    try {
        $stmt->execute([$doc[1], $doc[2], $doc[3], $doc[4], $doc[5], $doc[6], $doc[0]]);
        $docInserted++;
    } catch (Exception $e) {
        // Ignore
    }
}

echo "Inserted $docInserted sample documents\n";

// Sample milestones
$milestones = [
    ['UPRERAPRJ12345', 'RERA Registration', 'registration', '2024-01-15', '2024-01-15', 'completed', 'Registration completed successfully'],
    ['UPRERAPRJ12345', 'Layout Plan Approval', 'layout_approval', '2024-02-01', '2024-02-10', 'completed', 'Approved by Gorakhpur Development Authority'],
    ['UPRERAPRJ12345', 'Construction Start', 'construction_start', '2024-03-01', '2024-03-05', 'completed', 'Ground breaking ceremony held'],
    ['UPRERAPRJ12345', 'Plinth Completion', 'plinth_completion', '2024-06-01', '2024-06-15', 'completed', 'Plinth level completed for all blocks'],
    ['UPRERAPRJ12345', 'Structure Completion', 'structure_completion', '2024-12-01', NULL, 'in_progress', 'RCC work in progress'],
    ['UPRERAPRJ12345', 'Finishing Start', 'finishing_start', '2025-03-01', NULL, 'pending', ''],
    ['UPRERAPRJ12345', 'Completion Certificate', 'completion_certificate', '2025-09-01', NULL, 'pending', ''],
    ['UPRERAPRJ12345', 'Occupancy Certificate', 'occupancy_certificate', '2025-10-01', NULL, 'pending', ''],
    ['UPRERAPRJ12345', 'Handover', 'handover', '2025-11-01', NULL, 'pending', ''],
    
    ['UPRERAPRJ67890', 'RERA Registration', 'registration', '2024-03-20', '2024-03-20', 'completed', ''],
    ['UPRERAPRJ67890', 'Layout Plan Approval', 'layout_approval', '2024-04-15', '2024-04-20', 'completed', ''],
    ['UPRERAPRJ67890', 'Construction Start', 'construction_start', '2024-05-01', '2024-05-10', 'completed', ''],
    ['UPRERAPRJ67890', 'Plinth Completion', 'plinth_completion', '2024-08-01', NULL, 'in_progress', ''],
    
    ['UPRERAPRJ11111', 'RERA Registration', 'registration', '2023-11-10', '2023-11-10', 'completed', ''],
    ['UPRERAPRJ11111', 'Layout Plan Approval', 'layout_approval', '2023-12-01', '2023-12-10', 'completed', ''],
    ['UPRERAPRJ11111', 'Construction Start', 'construction_start', '2024-01-15', '2024-01-20', 'completed', ''],
    ['UPRERAPRJ11111', 'Plinth Completion', 'plinth_completion', '2024-04-01', '2024-04-10', 'completed', ''],
    ['UPRERAPRJ11111', 'Structure Completion', 'structure_completion', '2024-08-01', '2024-08-15', 'completed', ''],
    ['UPRERAPRJ11111', 'Finishing Start', 'finishing_start', '2024-10-01', '2024-10-05', 'completed', ''],
    ['UPRERAPRJ11111', 'Completion Certificate', 'completion_certificate', '2025-02-01', '2025-02-10', 'completed', ''],
    ['UPRERAPRJ11111', 'Occupancy Certificate', 'occupancy_certificate', '2025-03-01', '2025-03-05', 'completed', ''],
    ['UPRERAPRJ11111', 'Handover', 'handover', '2025-04-01', '2025-04-10', 'completed', 'All plots handed over'],
];

$stmt = $pdo->prepare("
    INSERT INTO rera_milestones 
    (project_id, milestone_name, milestone_type, planned_date, actual_date, status, remarks)
    SELECT id, ?, ?, ?, ?, ?, ? FROM rera_projects WHERE rera_number = ?
    ON DUPLICATE KEY UPDATE milestone_name = VALUES(milestone_name)
");

$msInserted = 0;
foreach ($milestones as $m) {
    try {
        $stmt->execute([$m[1], $m[2], $m[3], $m[4], $m[5], $m[6], $m[0]]);
        $msInserted++;
    } catch (Exception $e) {
        // Ignore
    }
}

echo "Inserted $msInserted sample milestones\n";

echo "\n=== RERA Verification tables and sample data created successfully ===\n";?>