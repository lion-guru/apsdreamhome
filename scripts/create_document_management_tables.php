<?php
require_once __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

echo "Creating document management tables...\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS doc_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    parent_id BIGINT UNSIGNED NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    color VARCHAR(20) NULL,
    allowed_extensions JSON NULL COMMENT 'Allowed file extensions',
    max_file_size_mb INT NOT NULL DEFAULT 10,
    requires_approval TINYINT(1) NOT NULL DEFAULT 0,
    retention_days INT NULL COMMENT 'Auto-archive after days',
    is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Cannot delete system categories',
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_category_code (tenant_id, code),
    KEY idx_parent (parent_id),
    KEY idx_active (is_active),
    KEY idx_system (is_system)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "doc_categories created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS documents (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    category_id BIGINT UNSIGNED NOT NULL,
    document_number VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_extension VARCHAR(20) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 for deduplication',
    version INT NOT NULL DEFAULT 1,
    is_latest TINYINT(1) NOT NULL DEFAULT 1,
    parent_document_id BIGINT UNSIGNED NULL COMMENT 'Previous version',
    entity_type ENUM('customer', 'booking', 'plot', 'invoice', 'tax_assessment', 'registry', 'lead', 'project', 'vendor', 'employee', 'associate', 'other') NOT NULL DEFAULT 'other',
    entity_id BIGINT UNSIGNED NULL,
    related_entity_type VARCHAR(50) NULL,
    related_entity_id BIGINT UNSIGNED NULL,
    tags JSON NULL,
    metadata JSON NULL COMMENT 'Flexible attributes per category',
    status ENUM('draft', 'pending_review', 'approved', 'rejected', 'expired', 'archived') NOT NULL DEFAULT 'draft',
    access_level ENUM('public', 'internal', 'confidential', 'restricted') NOT NULL DEFAULT 'internal',
    uploaded_by BIGINT UNSIGNED NOT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    review_notes TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    expiry_date DATE NULL,
    download_count INT NOT NULL DEFAULT 0,
    last_downloaded_at TIMESTAMP NULL DEFAULT NULL,
    last_downloaded_by BIGINT UNSIGNED NULL,
    esign_required TINYINT(1) NOT NULL DEFAULT 0,
    esign_status ENUM('not_required', 'pending', 'signed', 'declined', 'expired') NOT NULL DEFAULT 'not_required',
    esign_data JSON NULL COMMENT 'Signature details, timestamps, IP',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_number (tenant_id, document_number),
    KEY idx_category (category_id),
    KEY idx_entity (entity_type, entity_id),
    KEY idx_related (related_entity_type, related_entity_id),
    KEY idx_status (status),
    KEY idx_access (access_level),
    KEY idx_uploader (uploaded_by),
    KEY idx_hash (file_hash),
    KEY idx_expiry (expiry_date),
    KEY idx_parent_doc (parent_document_id),
    KEY idx_esign (esign_status),
    KEY idx_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "documents created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS document_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    document_id BIGINT UNSIGNED NOT NULL,
    version INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    change_summary TEXT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_doc_version (document_id, version),
    KEY idx_document (document_id),
    KEY idx_uploader (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "document_versions created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS document_shares (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    document_id BIGINT UNSIGNED NOT NULL,
    share_token VARCHAR(64) NOT NULL,
    shared_by BIGINT UNSIGNED NOT NULL,
    shared_with_type ENUM('user', 'role', 'email', 'public') NOT NULL,
    shared_with_id BIGINT UNSIGNED NULL COMMENT 'User ID or Role ID',
    shared_with_email VARCHAR(150) NULL,
    permission ENUM('view', 'download', 'comment', 'edit') NOT NULL DEFAULT 'view',
    expires_at TIMESTAMP NULL DEFAULT NULL,
    password_hash VARCHAR(255) NULL,
    access_count INT NOT NULL DEFAULT 0,
    last_accessed_at TIMESTAMP NULL DEFAULT NULL,
    last_accessed_by BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_share_token (share_token),
    KEY idx_document (document_id),
    KEY idx_shared_by (shared_by),
    KEY idx_shared_with (shared_with_type, shared_with_id),
    KEY idx_token (share_token),
    KEY idx_expires (expires_at),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "document_shares created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS document_comments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    document_id BIGINT UNSIGNED NOT NULL,
    parent_comment_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Internal notes vs client-visible',
    is_resolved TINYINT(1) NOT NULL DEFAULT 0,
    resolved_by BIGINT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_document (document_id),
    KEY idx_user (user_id),
    KEY idx_parent (parent_comment_id),
    KEY idx_resolved (is_resolved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "document_comments created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS document_workflows (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    category_ids JSON NOT NULL COMMENT 'Array of category IDs this workflow applies to',
    steps JSON NOT NULL COMMENT 'Workflow steps definition',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    auto_start TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Auto-start on upload',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_workflow_code (tenant_id, code),
    KEY idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "document_workflows created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS document_workflow_instances (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    workflow_id BIGINT UNSIGNED NOT NULL,
    document_id BIGINT UNSIGNED NOT NULL,
    current_step INT NOT NULL DEFAULT 1,
    status ENUM('pending', 'in_progress', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_workflow (workflow_id),
    KEY idx_document (document_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "document_workflow_instances created\n";

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS document_workflow_actions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
    instance_id BIGINT UNSIGNED NOT NULL,
    step INT NOT NULL,
    action ENUM('approved', 'rejected', 'returned', 'delegated') NOT NULL,
    acted_by BIGINT UNSIGNED NOT NULL,
    comments TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_instance (instance_id),
    KEY idx_actor (acted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
$db->execute($sql);
echo "document_workflow_actions created\n";

$defaultCategories = [
    ['customer_kyc', 'Customer KYC', 'Customer KYC documents (Aadhaar, PAN, Photo)', 'fas fa-id-card', '#3B82F6', '["pdf","jpg","png","jpeg"]', 10, 0, 730, 1],
    ['booking_docs', 'Booking Documents', 'Booking agreements, payment receipts', 'fas fa-file-contract', '#8B5CF6', '["pdf","docx"]', 10, 0, 2555, 1],
    ['property_docs', 'Property Documents', 'Layout plans, approvals, NOCs', 'fas fa-home', '#10B981', '["pdf","jpg","png","dwg","dxf"]', 50, 0, NULL, 1],
    ['gst_invoices', 'GST Invoices', 'GSTR-1/3B invoices, e-invoice JSON', 'fas fa-file-invoice-dollar', '#F59E0B', '["pdf","json"]', 10, 1, 2555, 1],
    ['tax_receipts', 'Tax Receipts', 'Property tax payment receipts', 'fas fa-receipt', '#EF4444', '["pdf"]', 10, 1, 2555, 1],
    ['registry_docs', 'Registry Documents', 'Sale deeds, registry certificates', 'fas fa-gavel', '#6366F1', '["pdf","jpg","png"]', 50, 0, NULL, 1],
    ['legal_docs', 'Legal Documents', 'Court orders, affidavits, notices', 'fas fa-balance-scale', '#EC4899', '["pdf","docx"]', 20, 1, 2555, 1],
    ['employee_docs', 'Employee Documents', 'HR files, contracts, payslips', 'fas fa-user-tie', '#06B6D4', '["pdf","jpg","png"]', 10, 0, 2555, 1],
    ['vendor_docs', 'Vendor Documents', 'Vendor contracts, invoices, MSME', 'fas fa-truck', '#84CC16', '["pdf","jpg","png"]', 10, 0, 2555, 1],
    ['project_docs', 'Project Documents', 'Project plans, drawings, BOQs', 'fas fa-project-diagram', '#F97316', '["pdf","dwg","dxf","xlsx"]', 100, 0, NULL, 1],
    ['marketing_docs', 'Marketing Materials', 'Brochures, videos, presentations', 'fas fa-bullhorn', '#D946EF', '["pdf","jpg","png","mp4","mov"]', 50, 0, 365, 1],
    ['compliance_docs', 'Compliance & Certificates', 'RERA, ISO, environmental certificates', 'fas fa-certificate', '#14B8A6', '["pdf","jpg","png"]', 20, 1, 2555, 1],
];
foreach ($defaultCategories as $c) {
    $db->execute("
        INSERT IGNORE INTO doc_categories (code, name, description, icon, color, allowed_extensions, max_file_size_mb, requires_approval, retention_days, is_system, is_active, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)", 
        [$c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6], $c[7], $c[8], $c[9]]);
}
echo "Default categories seeded\n";

$defaultWorkflow = [
    'standard_approval', 'Standard Approval', 
    'Standard 2-step review and approval workflow',
    json_encode(['customer_kyc', 'booking_docs', 'gst_invoices', 'tax_receipts', 'registry_docs', 'legal_docs']),
    json_encode([
        ['step' => 1, 'name' => 'Initial Review', 'approver_role' => 'manager', 'action' => 'approve_reject', 'notify' => true, 'timeout_hours' => 48],
        ['step' => 2, 'name' => 'Final Approval', 'approver_role' => 'director', 'action' => 'approve', 'notify' => true, 'timeout_hours' => 24]
    ]),
    1, 1, 1
];
$db->execute("
    INSERT IGNORE INTO document_workflows (code, name, description, category_ids, steps, is_active, auto_start, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $defaultWorkflow);
echo "Default workflow seeded\n";

echo "\nAll document management tables created and seeded successfully.\n";