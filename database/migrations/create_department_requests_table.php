<?php
/**
 * Migration: Create department_requests table
 * 
 * Enables cross-department request workflow:
 * - Any user can submit a request to a specific department
 * - Department heads see requests in their dashboard
 * - Requests go through approval workflow
 * - Full audit trail with status changes
 */

use App\Core\Database\Database;

return function() {
    $db = Database::getInstance()->getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS department_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        request_type VARCHAR(50) NOT NULL COMMENT 'inquiry, verification, approval, escalation, info_request',
        department_code VARCHAR(20) NOT NULL COMMENT 'SALES, FIN, LEGAL, HR, IT, etc',
        title VARCHAR(255) NOT NULL,
        description TEXT,
        priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
        status ENUM('submitted','in_progress','review','approved','rejected','completed','cancelled') DEFAULT 'submitted',
        requester_id INT UNSIGNED NOT NULL,
        requester_role VARCHAR(50),
        requester_name VARCHAR(255),
        assigned_to INT UNSIGNED NULL COMMENT 'User ID of assignee',
        assigned_to_role VARCHAR(50) NULL COMMENT 'Role-based assignment (e.g. legal_head)',
        related_entity_type VARCHAR(50) NULL COMMENT 'booking, lead, property, user, etc',
        related_entity_id INT NULL,
        due_date DATETIME NULL,
        completed_at DATETIME NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX idx_tenant (tenant_id),
        INDEX idx_department (department_code),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_requester (requester_id),
        INDEX idx_assigned (assigned_to),
        INDEX idx_related (related_entity_type, related_entity_id),
        INDEX idx_created (created_at),
        INDEX idx_tenant_dept_status (tenant_id, department_code, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql);
    
    // Create request comments table for audit trail
    $sql2 = "CREATE TABLE IF NOT EXISTS department_request_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        request_id INT NOT NULL,
        commenter_id INT UNSIGNED NULL,
        commenter_name VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        is_internal TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        INDEX idx_tenant (tenant_id),
        INDEX idx_request (request_id),
        INDEX idx_created (created_at),
        FOREIGN KEY (request_id) REFERENCES department_requests(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql2);
    
    echo "Migration: department_requests tables created successfully.\n";
};