<?php
require_once __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

echo "Creating GST tables...\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_hsn_codes (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        hsn_code VARCHAR(20) NOT NULL,
        description TEXT NOT NULL,
        gst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cgst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        sgst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        igst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cess_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        unit VARCHAR(20) NULL COMMENT 'UOM like PCS, MTR, KG, SQFT',
        is_service TINYINT(1) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_by BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_hsn_tenant (tenant_id, hsn_code),
        KEY idx_active (is_active),
        KEY idx_service (is_service)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_hsn_codes created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_invoices (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        invoice_number VARCHAR(50) NOT NULL,
        invoice_date DATE NOT NULL,
        invoice_type ENUM('B2B', 'B2C', 'B2CL', 'EXP', 'SEZ', 'DE') NOT NULL DEFAULT 'B2B',
        supply_type ENUM('intra_state', 'inter_state') NOT NULL DEFAULT 'intra_state',
        place_of_supply VARCHAR(2) NULL COMMENT 'State code',
        reverse_charge TINYINT(1) NOT NULL DEFAULT 0,
        
        customer_gstin VARCHAR(15) NULL,
        customer_name VARCHAR(200) NULL,
        customer_address TEXT NULL,
        customer_state_code VARCHAR(2) NULL,
        customer_pincode VARCHAR(10) NULL,
        
        booking_id BIGINT UNSIGNED NULL,
        plot_id BIGINT UNSIGNED NULL,
        
        taxable_value DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        cgst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        sgst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        igst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        cess_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        total_tax DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        
        irn VARCHAR(50) NULL COMMENT 'Invoice Reference Number from IRP',
        irn_date DATETIME NULL,
        ack_no VARCHAR(50) NULL,
        ack_date DATETIME NULL,
        qr_code TEXT NULL,
        signed_invoice TEXT NULL,
        einvoice_status ENUM('not_applicable', 'pending', 'generated', 'failed', 'cancelled') NOT NULL DEFAULT 'not_applicable',
        einvoice_error TEXT NULL,
        
        gstr1_status ENUM('not_filed', 'ready', 'filed', 'error') NOT NULL DEFAULT 'not_filed',
        gstr1_filed_date DATE NULL,
        gstr1_error TEXT NULL,
        
        gstr3b_status ENUM('not_filed', 'ready', 'filed', 'error') NOT NULL DEFAULT 'not_filed',
        gstr3b_filed_date DATE NULL,
        gstr3b_error TEXT NULL,
        
        status ENUM('draft', 'validated', 'cancelled') NOT NULL DEFAULT 'draft',
        notes TEXT NULL,
        created_by BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_invoice_tenant (tenant_id, invoice_number),
        KEY idx_date (invoice_date),
        KEY idx_customer_gstin (customer_gstin),
        KEY idx_type (invoice_type),
        KEY idx_irn (irn),
        KEY idx_gstr1 (gstr1_status),
        KEY idx_gstr3b (gstr3b_status),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_invoices created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_invoice_items (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        invoice_id BIGINT UNSIGNED NOT NULL,
        line_number INT NOT NULL DEFAULT 1,
        hsn_code VARCHAR(20) NOT NULL,
        description TEXT NOT NULL,
        uom VARCHAR(20) NULL,
        quantity DECIMAL(14,3) NOT NULL DEFAULT 1.000,
        rate DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        taxable_value DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        discount_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        gst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cgst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        sgst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        igst_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cess_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        cgst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        sgst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        igst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        cess_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_invoice (invoice_id),
        KEY idx_hsn (hsn_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_invoice_items created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_gstr1_summary (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        return_period VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
        section ENUM('B2B', 'B2C', 'B2CL', 'EXP', 'SEZ', 'DE', 'CDNR', 'CDNUR', 'HSN', 'NIL', 'ADV', 'ADJ') NOT NULL,
        invoice_id BIGINT UNSIGNED NULL,
        customer_gstin VARCHAR(15) NULL,
        invoice_number VARCHAR(50) NULL,
        invoice_date DATE NULL,
        invoice_value DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        taxable_value DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        cgst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        sgst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        igst_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        cess_amount DECIMAL(16,2) NOT NULL DEFAULT 0.00,
        place_of_supply VARCHAR(2) NULL,
        supply_type ENUM('intra_state', 'inter_state') NULL,
        reverse_charge TINYINT(1) NOT NULL DEFAULT 0,
        hsn_code VARCHAR(20) NULL,
        uqc VARCHAR(10) NULL,
        total_quantity DECIMAL(14,3) NOT NULL DEFAULT 0.000,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_gstr1_tenant_period_section (tenant_id, return_period, section, invoice_id),
        KEY idx_period (return_period),
        KEY idx_section (section),
        KEY idx_gstin (customer_gstin)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_gstr1_summary created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_gstr3b_summary (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        return_period VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
        table_section ENUM('3.1', '3.2', '4', '5', '5.1', '5.2', '5.3', '5.4', '5.5', '5.6') NOT NULL,
        taxable_value DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        igst_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        cgst_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        sgst_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        cess_amount DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        itc_claimed DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        itc_reversed DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        net_itc DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        tax_liability DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        tax_paid_cash DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        tax_paid_itc DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        interest DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        late_fee DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        status ENUM('draft', 'computed', 'filed', 'error') NOT NULL DEFAULT 'draft',
        filed_date DATE NULL,
        filed_by BIGINT UNSIGNED NULL,
        notes TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_gstr3b_tenant_period_section (tenant_id, return_period, table_section),
        KEY idx_period (return_period),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_gstr3b_summary created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_einvoicing_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        invoice_id BIGINT UNSIGNED NOT NULL,
        request_payload JSON NOT NULL,
        response_payload JSON NULL,
        irn VARCHAR(50) NULL,
        ack_no VARCHAR(50) NULL,
        ack_date DATETIME NULL,
        status ENUM('success', 'failed', 'cancelled') NOT NULL,
        error_code VARCHAR(20) NULL,
        error_message TEXT NULL,
        api_endpoint VARCHAR(100) NULL,
        api_version VARCHAR(20) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_invoice (invoice_id),
        KEY idx_status (status),
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_einvoicing_log created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS gst_returns_filing_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        return_type ENUM('GSTR1', 'GSTR3B', 'GSTR9', 'GSTR9C') NOT NULL,
        return_period VARCHAR(7) NOT NULL,
        status ENUM('draft', 'computed', 'validated', 'filed', 'failed', 'cancelled') NOT NULL DEFAULT 'draft',
        payload_json JSON NULL,
        response_json JSON NULL,
        arn VARCHAR(50) NULL,
        filed_date DATETIME NULL,
        filed_by BIGINT UNSIGNED NULL,
        error_message TEXT NULL,
        retry_count INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_return_tenant_period_type (tenant_id, return_type, return_period),
        KEY idx_period (return_period),
        KEY idx_type (return_type),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "gst_returns_filing_log created\n";

$defaultHsn = [
    ['4520', 'Construction Services - Residential', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 1],
    ['4521', 'Construction Services - Commercial', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 1],
    ['4530', 'Construction Services - Industrial', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 1],
    ['9954', 'Construction Services - General', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 1],
    ['9971', 'Real Estate Services - Rental/Lease', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 1],
    ['9972', 'Real Estate Services - Sale', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 1],
    ['9983', 'Professional/Consulting Services', 18.00, 9.00, 9.00, 18.00, 0.00, 'PCS', 1],
    ['9985', 'Architectural/Engineering Services', 18.00, 9.00, 9.00, 18.00, 0.00, 'PCS', 1],
    ['7204', 'Steel/Structural Materials', 18.00, 9.00, 9.00, 18.00, 0.00, 'KG', 0],
    ['2523', 'Cement', 28.00, 14.00, 14.00, 28.00, 0.00, 'BAG', 0],
    ['2517', 'Sand/Aggregates', 5.00, 2.50, 2.50, 5.00, 0.00, 'CFT', 0],
    ['6810', 'Tiles/Ceramics', 18.00, 9.00, 9.00, 18.00, 0.00, 'SQFT', 0],
    ['3917', 'Pipes/Plumbing', 18.00, 9.00, 9.00, 18.00, 0.00, 'MTR', 0],
    ['8536', 'Electrical Fittings', 18.00, 9.00, 9.00, 18.00, 0.00, 'PCS', 0],
    ['9403', 'Furniture/Fixtures', 18.00, 9.00, 9.00, 18.00, 0.00, 'PCS', 0],
];
foreach ($defaultHsn as $h) {
    $db->execute("
        INSERT IGNORE INTO gst_hsn_codes (hsn_code, description, gst_rate, cgst_rate, sgst_rate, igst_rate, cess_rate, unit, is_service, is_active, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1)", $h);
}
echo "Default HSN codes seeded\n";

echo "\nAll GST tables created and seeded successfully.\n";