<?php
/**
 * Migration: Create program_applications (Student Internship + Nari Shakti registrations)
 * Session: 681
 * Idempotent: Safe to re-run, prints SKIP if table already exists
 *
 * Both the Student Internship Program (careers page) and the Nari Shakti
 * women-empowerment initiative (team page) previously submitted to a jobs
 * endpoint / dead-ended on /contact. One unified, tenant-scoped intake table
 * serves both public programs, with program_type distinguishing them.
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$exists = $pdo->query("SHOW TABLES LIKE 'program_applications'")->fetchColumn();
if ($exists) {
    echo "SKIP program_applications - already exists\n";
} else {
    $pdo->exec("CREATE TABLE IF NOT EXISTS program_applications (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        program_type ENUM('student','nari_shakti') NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        college VARCHAR(255) DEFAULT NULL,
        course VARCHAR(100) DEFAULT NULL,
        current_year VARCHAR(20) DEFAULT NULL,
        city VARCHAR(150) DEFAULT NULL,
        motivation TEXT DEFAULT NULL,
        suggestion TEXT DEFAULT NULL,
        status ENUM('pending','reviewing','approved','rejected') NOT NULL DEFAULT 'pending',
        admin_notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_program_status (program_type, status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "CREATED program_applications\n";
}

echo "Migration complete\n";
