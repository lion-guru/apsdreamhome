<?php

/**
 * APS Dream Home - Lead Management System Service
 * Save and manage customer leads
 */

namespace App\Services\Lead;

header('Content-Type: application/json');

// Get lead data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['name']) || empty($data['phone'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Name and phone number are required'
    ]);
    exit;
}

// Sanitize input
$lead_data = [
    'name' => htmlspecialchars(trim($data['name'])),
    'phone' => htmlspecialchars(trim($data['phone'])),
    'email' => htmlspecialchars(trim($data['email'] ?? '')),
    'property' => htmlspecialchars(trim($data['property'] ?? '')),
    'message' => htmlspecialchars(trim($data['message'] ?? '')),
    'role' => htmlspecialchars(trim($data['role'] ?? 'customer')),
    'source' => $data['auto_captured'] ? 'ai_auto_captured' : 'manual_form',
    'timestamp' => date('Y-m-d H:i:s'),
    'date' => date('Y-m-d'),
    'status' => 'new'
];

// Validate phone number (Indian format)
if (!preg_match('/^[6-9]\d{9}$/', preg_replace('/[^0-9]/', '', $lead_data['phone']))) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid phone number format'
    ]);
    exit;
}

// Validate email if provided
if (!empty($lead_data['email']) && !filter_var($lead_data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid email format'
    ]);
    exit;
}

try {
    // Database connection
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=apsdreamhome;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Check if lead already exists (by phone)
    $check_sql = "SELECT id FROM leads WHERE phone = :phone AND date = :date";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([
        ':phone' => $lead_data['phone'],
        ':date' => $lead_data['date']
    ]);

    if ($check_stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'error' => 'Lead with this phone number already exists today'
        ]);
        exit;
    }

    // Insert new lead
    $sql = "INSERT INTO leads (name, phone, email, property_type, message, user_role, source, created_at, status) 
            VALUES (:name, :phone, :email, :property, :message, :role, :source, :timestamp, :status)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $lead_data['name'],
        ':phone' => $lead_data['phone'],
        ':email' => $lead_data['email'],
        ':property' => $lead_data['property'],
        ':message' => $lead_data['message'],
        ':role' => $lead_data['role'],
        ':source' => $lead_data['source'],
        ':timestamp' => $lead_data['timestamp'],
        ':status' => $lead_data['status']
    ]);

    $lead_id = $pdo->lastInsertId();

    // Log the activity
    $log_sql = "INSERT INTO lead_logs (lead_id, action, details, created_at) 
                VALUES (:lead_id, 'created', :details, :timestamp)";

    $log_stmt = $pdo->prepare($log_sql);
    $log_stmt->execute([
        ':lead_id' => $lead_id,
        ':details' => json_encode([
            'role' => $lead_data['role'],
            'source' => $lead_data['source'],
            'property_interest' => $lead_data['property']
        ]),
        ':timestamp' => $lead_data['timestamp']
    ]);

    echo json_encode([
        'success' => true,
        'lead_id' => $lead_id,
        'message' => 'Lead saved successfully',
        'data' => $lead_data
    ]);
} catch (PDOException $e) {
    // If table doesn't exist, create it
    if (strpos($e->getMessage(), "Table 'apsdreamhome.leads' doesn't exist") !== false) {
        createLeadsTable($pdo);

        // Retry the insert
        try {
            $sql = "INSERT INTO leads (name, phone, email, property_type, message, user_role, source, created_at, status) 
                    VALUES (:name, :phone, :email, :property, :message, :role, :source, :timestamp, :status)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $lead_data['name'],
                ':phone' => $lead_data['phone'],
                ':email' => $lead_data['email'],
                ':property' => $lead_data['property'],
                ':message' => $lead_data['message'],
                ':role' => $lead_data['role'],
                ':source' => $lead_data['source'],
                ':timestamp' => $lead_data['timestamp'],
                ':status' => $lead_data['status']
            ]);

            $lead_id = $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'lead_id' => $lead_id,
                'message' => 'Lead saved successfully (table created)',
                'data' => $lead_data
            ]);
        } catch (PDOException $retry_error) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error after table creation: ' . $retry_error->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'General error: ' . $e->getMessage()
    ]);
}

/**
 * Create leads table if it doesn't exist
 */
function createLeadsTable($pdo)
{
    $create_sql = "CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL UNIQUE,
        email VARCHAR(255),
        property_type VARCHAR(100),
        message TEXT,
        user_role VARCHAR(50) DEFAULT 'customer',
        source VARCHAR(50) DEFAULT 'manual_form',
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('new', 'contacted', 'interested', 'converted', 'closed') DEFAULT 'new',
        assigned_to VARCHAR(100),
        follow_up_date DATE,
        notes TEXT,
        INDEX idx_phone (phone),
        INDEX idx_date (created_at),
        INDEX idx_status (status),
        INDEX idx_role (user_role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($create_sql);

    // Create lead logs table
    $create_logs_sql = "CREATE TABLE IF NOT EXISTS lead_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        details TEXT,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
        INDEX idx_lead_id (lead_id),
        INDEX idx_action (action)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($create_logs_sql);
}
