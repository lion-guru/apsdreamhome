<?php
require_once __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// 1. Property Verification Levels table
$db->exec("
CREATE TABLE IF NOT EXISTS property_verification_levels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'INR',
    features JSON,
    badge_icon VARCHAR(50),
    badge_color VARCHAR(7),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "Created property_verification_levels table\n";

// 2. Property Verification Requests table
$db->exec("
CREATE TABLE IF NOT EXISTS property_verification_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    property_type ENUM('plot', 'house', 'apartment', 'commercial', 'land') NOT NULL,
    verification_level_id INT UNSIGNED NOT NULL,
    requested_by INT UNSIGNED NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'documents_pending', 'on_site_pending', 'approved', 'rejected', 'cancelled') DEFAULT 'draft',
    priority ENUM('standard', 'express') DEFAULT 'standard',
    
    -- Document submissions
    title_deed TEXT,
    sale_deed TEXT,
    tax_receipts TEXT,
    encumbrance_certificate TEXT,
    approved_building_plan TEXT,
    identity_proof TEXT,
    additional_documents JSON,
    
    -- Verification results
    assigned_to INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    approved_at DATETIME NULL,
    rejected_at DATETIME NULL,
    rejection_reason TEXT,
    verification_notes TEXT,
    
    -- On-site inspection
    inspection_scheduled_at DATETIME NULL,
    inspection_completed_at DATETIME NULL,
    inspector_id INT UNSIGNED NULL,
    inspection_report TEXT,
    inspection_photos JSON,
    
    -- Badge details
    badge_awarded_at DATETIME NULL,
    badge_expires_at DATETIME NULL,
    badge_verification_code VARCHAR(64),
    
    -- Payment
    amount_paid DECIMAL(10,2) DEFAULT 0,
    payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
    payment_id VARCHAR(100),
    
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_property (property_id),
    INDEX idx_requester (requested_by),
    INDEX idx_level (verification_level_id),
    INDEX idx_status (status),
    INDEX idx_tenant (tenant_id),
    INDEX idx_code (badge_verification_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "Created property_verification_requests table\n";

// 3. Property Verification Documents table
$db->exec("
CREATE TABLE IF NOT EXISTS property_verification_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED DEFAULT 0,
    mime_type VARCHAR(100),
    uploaded_by INT UNSIGNED NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    verified_by INT UNSIGNED NULL,
    verified_at DATETIME NULL,
    verification_notes TEXT,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_request (request_id),
    INDEX idx_type (document_type),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "Created property_verification_documents table\n";

// 4. Property Verification Badges table (issued badges)
$db->exec("
CREATE TABLE IF NOT EXISTS property_verification_badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    property_id INT UNSIGNED NOT NULL,
    verification_level_id INT UNSIGNED NOT NULL,
    badge_code VARCHAR(32) NOT NULL UNIQUE,
    qr_code_path VARCHAR(500),
    status ENUM('active', 'expired', 'revoked', 'suspended') DEFAULT 'active',
    issued_at DATETIME NOT NULL,
    expires_at DATETIME NULL,
    revoked_at DATETIME NULL,
    revoked_by INT UNSIGNED NULL,
    revocation_reason TEXT,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_property (property_id),
    INDEX idx_request (request_id),
    INDEX idx_level (verification_level_id),
    INDEX idx_code (badge_code),
    INDEX idx_status (status),
    INDEX idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "Created property_verification_badges table\n";

// 5. Insert default verification levels
$levels = [
    [
        'name' => 'Basic Verified',
        'slug' => 'basic',
        'description' => 'Document verification & ownership check',
        'price' => 0,
        'features' => json_encode([
            'Title deed verified',
            'Owner identity confirmed',
            'No pending disputes'
        ]),
        'badge_icon' => 'check_circle_rounded',
        'badge_color' => '#4CAF50',
        'sort_order' => 1
    ],
    [
        'name' => 'Premium Verified',
        'slug' => 'premium',
        'description' => 'Full legal + physical verification',
        'price' => 999,
        'features' => json_encode([
            'All Basic features',
            'On-site inspection',
            'Measurement verification',
            'Encumbrance check'
        ]),
        'badge_icon' => 'shield_rounded',
        'badge_color' => '#1565C0',
        'sort_order' => 2
    ],
    [
        'name' => 'Gold Verified',
        'slug' => 'gold',
        'description' => 'Comprehensive due diligence + report',
        'price' => 2499,
        'features' => json_encode([
            'All Premium features',
            'Title search report',
            'Approved plan check',
            'Tax compliance audit',
            'Neighborhood analysis'
        ]),
        'badge_icon' => 'verified_rounded',
        'badge_color' => '#F9A825',
        'sort_order' => 3
    ],
    [
        'name' => 'Platinum Verified',
        'slug' => 'platinum',
        'description' => 'Complete assurance + legal cover',
        'price' => 4999,
        'features' => json_encode([
            'All Gold features',
            'Legal indemnity cover',
            'RERA compliance check',
            'Structural engineer report',
            'Title insurance worth ₹10L'
        ]),
        'badge_icon' => 'diamond_rounded',
        'badge_color' => '#6A1B9A',
        'sort_order' => 4
    ]
];

foreach ($levels as $level) {
    $level['tenant_id'] = 1;
    $level['is_active'] = 1;
    $level['created_at'] = date('Y-m-d H:i:s');
    $level['updated_at'] = date('Y-m-d H:i:s');
    
    $cols = implode(', ', array_keys($level));
    $placeholders = ':' . implode(', :', array_keys($level));
    
    $stmt = $db->prepare("INSERT IGNORE INTO property_verification_levels ($cols) VALUES ($placeholders)");
    $stmt->execute($level);
    echo "Inserted level: {$level['name']}\n";
}

echo "\nAll Property Verification tables created successfully!\n";