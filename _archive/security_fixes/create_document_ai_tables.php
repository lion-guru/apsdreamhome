<?php
require_once 'config/bootstrap.php';
require_once 'app/Core/Database/Database.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Document extraction jobs table
$pdo->exec("
CREATE TABLE IF NOT EXISTS document_extraction_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('sale_deed', 'agreement_to_sell', 'gift_deed', 'lease_deed', 'mortgage_deed', 'release_deed', 'partition_deed', 'power_of_attorney', 'will', 'court_order', 'property_tax_receipt', 'mutation_certificate', 'encumbrance_certificate', 'khata_certificate', 'other') NOT NULL,
    source_type ENUM('upload', 'url', 'camera', 'scanner') DEFAULT 'upload',
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    file_url TEXT DEFAULT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    file_size BIGINT DEFAULT 0,
    status ENUM('queued', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'queued',
    extraction_engine ENUM('google_document_ai', 'azure_form_recognizer', 'aws_textract', 'tesseract_ocr', 'custom_ml', 'mock') DEFAULT 'mock',
    extracted_data JSON DEFAULT NULL,
    confidence_score DECIMAL(5,2) DEFAULT 0.00,
    review_required TINYINT(1) DEFAULT 1,
    reviewed_by BIGINT UNSIGNED DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    review_notes TEXT DEFAULT NULL,
    processing_time_ms INT DEFAULT 0,
    error_message TEXT DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_by BIGINT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    KEY idx_status (status),
    KEY idx_document_type (document_type),
    KEY idx_created_by (created_by),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "document_extraction_jobs table created/verified\n";

// Extracted field templates for different document types
$pdo->exec("
CREATE TABLE IF NOT EXISTS document_field_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('sale_deed', 'agreement_to_sell', 'gift_deed', 'lease_deed', 'mortgage_deed', 'release_deed', 'partition_deed', 'power_of_attorney', 'will', 'court_order', 'property_tax_receipt', 'mutation_certificate', 'encumbrance_certificate', 'khata_certificate', 'other') NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_label VARCHAR(150) NOT NULL,
    field_type ENUM('text', 'number', 'date', 'currency', 'percentage', 'boolean', 'select', 'multi_select', 'address', 'aadhaar', 'pan', 'phone', 'email', 'area') DEFAULT 'text',
    is_required TINYINT(1) DEFAULT 0,
    validation_regex VARCHAR(255) DEFAULT NULL,
    default_value TEXT DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_template_field (document_type, field_name),
    KEY idx_document_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "document_field_templates table created/verified\n";

// Insert field templates for sale deed
$fieldTemplates = [
    // Sale Deed fields
    ['sale_deed', 'document_number', 'Document Number', 'text', 1, '', '', 1],
    ['sale_deed', 'execution_date', 'Execution Date', 'date', 1, '', '', 2],
    ['sale_deed', 'registration_date', 'Registration Date', 'date', 0, '', '', 3],
    ['sale_deed', 'seller_name', 'Seller Name', 'text', 1, '', '', 4],
    ['sale_deed', 'seller_father_name', "Seller's Father/Husband Name", 'text', 0, '', '', 5],
    ['sale_deed', 'seller_address', 'Seller Address', 'address', 1, '', '', 6],
    ['sale_deed', 'seller_aadhaar', "Seller's Aadhaar", 'aadhaar', 0, '^[0-9]{12}$', '', 7],
    ['sale_deed', 'seller_pan', "Seller's PAN", 'pan', 0, '^[A-Z]{5}[0-9]{4}[A-Z]{1}$', '', 8],
    ['sale_deed', 'buyer_name', 'Buyer Name', 'text', 1, '', '', 9],
    ['sale_deed', 'buyer_father_name', "Buyer's Father/Husband Name", 'text', 0, '', '', 10],
    ['sale_deed', 'buyer_address', 'Buyer Address', 'address', 1, '', '', 11],
    ['sale_deed', 'buyer_aadhaar', "Buyer's Aadhaar", 'aadhaar', 0, '^[0-9]{12}$', '', 12],
    ['sale_deed', 'buyer_pan', "Buyer's PAN", 'pan', 0, '^[A-Z]{5}[0-9]{4}[A-Z]{1}$', '', 13],
    ['sale_deed', 'property_address', 'Property Address', 'address', 1, '', '', 14],
    ['sale_deed', 'property_city', 'Property City', 'text', 1, '', '', 15],
    ['sale_deed', 'property_state', 'Property State', 'text', 1, '', '', 16],
    ['sale_deed', 'property_district', 'Property District', 'text', 1, '', '', 17],
    ['sale_deed', 'property_tehsil', 'Property Tehsil/Taluk', 'text', 0, '', '', 18],
    ['sale_deed', 'property_village', 'Property Village', 'text', 0, '', '', 19],
    ['sale_deed', 'survey_number', 'Survey/Khasra Number', 'text', 1, '', '', 20],
    ['sale_deed', 'plot_number', 'Plot Number', 'text', 0, '', '', 21],
    ['sale_deed', 'area_sqft', 'Area (Sq Ft)', 'area', 1, '', '', 22],
    ['sale_deed', 'area_sqm', 'Area (Sq M)', 'area', 0, '', '', 23],
    ['sale_deed', 'area_acres', 'Area (Acres)', 'area', 0, '', '', 24],
    ['sale_deed', 'boundary_north', 'North Boundary', 'text', 0, '', '', 25],
    ['sale_deed', 'boundary_south', 'South Boundary', 'text', 0, '', '', 26],
    ['sale_deed', 'boundary_east', 'East Boundary', 'text', 0, '', '', 27],
    ['sale_deed', 'boundary_west', 'West Boundary', 'text', 0, '', '', 28],
    ['sale_deed', 'sale_consideration', 'Sale Consideration (â‚¹)', 'currency', 1, '', '', 29],
    ['sale_deed', 'stamp_duty_paid', 'Stamp Duty Paid (â‚¹)', 'currency', 1, '', '', 30],
    ['sale_deed', 'registration_charges', 'Registration Charges (â‚¹)', 'currency', 0, '', '', 31],
    ['sale_deed', 'registration_office', 'Registration Office', 'text', 1, '', '', 32],
    ['sale_deed', 'registration_book', 'Book Number', 'text', 0, '', '', 33],
    ['sale_deed', 'registration_volume', 'Volume Number', 'text', 0, '', '', 34],
    ['sale_deed', 'registration_page', 'Page Number', 'text', 0, '', '', 35],
    
    // Agreement to Sell fields
    ['agreement_to_sell', 'agreement_number', 'Agreement Number', 'text', 1, '', '', 1],
    ['agreement_to_sell', 'agreement_date', 'Agreement Date', 'date', 1, '', '', 2],
    ['agreement_to_sell', 'seller_name', 'Seller Name', 'text', 1, '', '', 3],
    ['agreement_to_sell', 'buyer_name', 'Buyer Name', 'text', 1, '', '', 4],
    ['agreement_to_sell', 'property_address', 'Property Address', 'address', 1, '', '', 5],
    ['agreement_to_sell', 'total_price', 'Total Price (â‚¹)', 'currency', 1, '', '', 6],
    ['agreement_to_sell', 'advance_paid', 'Advance Paid (â‚¹)', 'currency', 1, '', '', 7],
    ['agreement_to_sell', 'balance_due', 'Balance Due (â‚¹)', 'currency', 1, '', '', 8],
    ['agreement_to_sell', 'possession_date', 'Possession Date', 'date', 0, '', '', 9],
    ['agreement_to_sell', 'completion_timeline', 'Completion Timeline', 'text', 0, '', '', 10],
    ['agreement_to_sell', 'penalty_clause', 'Penalty Clause', 'text', 0, '', '', 11],
    
    // Gift Deed fields
    ['gift_deed', 'document_number', 'Document Number', 'text', 1, '', '', 1],
    ['gift_deed', 'execution_date', 'Execution Date', 'date', 1, '', '', 2],
    ['gift_deed', 'donor_name', 'Donor Name', 'text', 1, '', '', 3],
    ['gift_deed', 'donee_name', 'Donee Name', 'text', 1, '', '', 4],
    ['gift_deed', 'relationship', 'Relationship', 'text', 1, '', '', 5],
    ['gift_deed', 'property_address', 'Property Address', 'address', 1, '', '', 6],
    ['gift_deed', 'area_sqft', 'Area (Sq Ft)', 'area', 1, '', '', 7],
    ['gift_deed', 'survey_number', 'Survey Number', 'text', 1, '', '', 8],
    
    // Lease Deed fields
    ['lease_deed', 'lease_number', 'Lease Number', 'text', 1, '', '', 1],
    ['lease_deed', 'execution_date', 'Execution Date', 'date', 1, '', '', 2],
    ['lease_deed', 'lessor_name', 'Lessor Name', 'text', 1, '', '', 3],
    ['lease_deed', 'lessee_name', 'Lessee Name', 'text', 1, '', '', 4],
    ['lease_deed', 'property_address', 'Property Address', 'address', 1, '', '', 5],
    ['lease_deed', 'lease_term_years', 'Lease Term (Years)', 'number', 1, '', '', 6],
    ['lease_deed', 'lease_start_date', 'Lease Start Date', 'date', 1, '', '', 7],
    ['lease_deed', 'lease_end_date', 'Lease End Date', 'date', 1, '', '', 8],
    ['lease_deed', 'monthly_rent', 'Monthly Rent (â‚¹)', 'currency', 1, '', '', 9],
    ['lease_deed', 'security_deposit', 'Security Deposit (â‚¹)', 'currency', 0, '', '', 10],
    ['lease_deed', 'rent_escalation', 'Rent Escalation %', 'percentage', 0, '', '', 11],
    
    // Mortgage Deed fields
    ['mortgage_deed', 'mortgage_number', 'Mortgage Number', 'text', 1, '', '', 1],
    ['mortgage_deed', 'execution_date', 'Execution Date', 'date', 1, '', '', 2],
    ['mortgage_deed', 'mortgagor_name', 'Mortgagor Name', 'text', 1, '', '', 3],
    ['mortgage_deed', 'mortgagee_name', 'Mortgagee Name', 'text', 1, '', '', 4],
    ['mortgage_deed', 'loan_amount', 'Loan Amount (â‚¹)', 'currency', 1, '', '', 5],
    ['mortgage_deed', 'interest_rate', 'Interest Rate (%)', 'percentage', 1, '', '', 6],
    ['mortgage_deed', 'property_address', 'Property Address', 'address', 1, '', '', 7],
    ['mortgage_deed', 'repayment_terms', 'Repayment Terms', 'text', 0, '', '', 8],
    
    // Power of Attorney fields
    ['power_of_attorney', 'poa_number', 'POA Number', 'text', 1, '', '', 1],
    ['power_of_attorney', 'execution_date', 'Execution Date', 'date', 1, '', '', 2],
    ['power_of_attorney', 'principal_name', 'Principal Name', 'text', 1, '', '', 3],
    ['power_of_attorney', 'agent_name', 'Agent Name', 'text', 1, '', '', 3],
    ['power_of_attorney', 'scope', 'Scope of Authority', 'text', 1, '', '', 4],
    ['power_of_attorney', 'property_details', 'Property Details', 'address', 0, '', '', 5],
    ['power_of_attorney', 'validity_period', 'Validity Period', 'text', 0, '', '', 6],
    
    // Property Tax Receipt fields
    ['property_tax_receipt', 'receipt_number', 'Receipt Number', 'text', 1, '', '', 1],
    ['property_tax_receipt', 'receipt_date', 'Receipt Date', 'date', 1, '', '', 2],
    ['property_tax_receipt', 'assessment_year', 'Assessment Year', 'text', 1, '', '', 3],
    ['property_tax_receipt', 'owner_name', 'Owner Name', 'text', 1, '', '', 4],
    ['property_tax_receipt', 'property_address', 'Property Address', 'address', 1, '', '', 5],
    ['property_tax_receipt', 'property_id', 'Property ID/PTR Number', 'text', 1, '', '', 6],
    ['property_tax_receipt', 'tax_amount', 'Tax Amount (â‚¹)', 'currency', 1, '', '', 7],
    ['property_tax_receipt', 'penalty_amount', 'Penalty Amount (â‚¹)', 'currency', 0, '', '', 8],
    ['property_tax_receipt', 'total_paid', 'Total Paid (â‚¹)', 'currency', 1, '', '', 9],
    ['property_tax_receipt', 'payment_mode', 'Payment Mode', 'select', 0, '', '', 10],
    ['property_tax_receipt', 'ward_number', 'Ward Number', 'text', 0, '', '', 11],
    ['property_tax_receipt', 'zone', 'Zone', 'text', 0, '', '', 12],
    
    // Mutation Certificate fields
    ['mutation_certificate', 'certificate_number', 'Certificate Number', 'text', 1, '', '', 1],
    ['mutation_certificate', 'issue_date', 'Issue Date', 'date', 1, '', '', 2],
    ['mutation_certificate', 'applicant_name', 'Applicant Name', 'text', 1, '', '', 3],
    ['mutation_certificate', 'previous_owner', 'Previous Owner', 'text', 1, '', '', 4],
    ['mutation_certificate', 'new_owner', 'New Owner', 'text', 1, '', '', 5],
    ['mutation_certificate', 'property_address', 'Property Address', 'address', 1, '', '', 6],
    ['mutation_certificate', 'survey_number', 'Survey Number', 'text', 1, '', '', 7],
    ['mutation_certificate', 'area_sqft', 'Area (Sq Ft)', 'area', 1, '', '', 8],
    ['mutation_certificate', 'mutation_reason', 'Mutation Reason', 'select', 1, '', '', 9],
    ['mutation_certificate', 'authority', 'Issuing Authority', 'text', 1, '', '', 10],
    
    // Encumbrance Certificate fields
    ['encumbrance_certificate', 'certificate_number', 'Certificate Number', 'text', 1, '', '', 1],
    ['encumbrance_certificate', 'issue_date', 'Issue Date', 'date', 1, '', '', 2],
    ['encumbrance_certificate', 'period_from', 'Period From', 'date', 1, '', '', 3],
    ['encumbrance_certificate', 'period_to', 'Period To', 'date', 1, '', '', 4],
    ['encumbrance_certificate', 'property_address', 'Property Address', 'address', 1, '', '', 5],
    ['encumbrance_certificate', 'survey_number', 'Survey Number', 'text', 1, '', '', 6],
    ['encumbrance_certificate', 'owner_name', 'Owner Name', 'text', 1, '', '', 7],
    ['encumbrance_certificate', 'encumbrances', 'Encumbrances Found', 'boolean', 1, '', '', 8],
    ['encumbrance_certificate', 'encumbrance_details', 'Encumbrance Details', 'text', 0, '', '', 9],
    
    // Khata Certificate fields
    ['khata_certificate', 'khata_number', 'Khata Number', 'text', 1, '', '', 1],
    ['khata_certificate', 'issue_date', 'Issue Date', 'date', 1, '', '', 2],
    ['khata_certificate', 'owner_name', 'Owner Name', 'text', 1, '', '', 3],
    ['khata_certificate', 'property_address', 'Property Address', 'address', 1, '', '', 4],
    ['khata_certificate', 'property_type', 'Property Type', 'text', 1, '', '', 5],
    ['khata_certificate', 'area_sqft', 'Area (Sq Ft)', 'area', 1, '', '', 6],
    ['khata_certificate', 'tax_paid_upto', 'Tax Paid Upto', 'date', 0, '', '', 7],
    ['khata_certificate', 'ward_number', 'Ward Number', 'text', 0, '', '', 8],
];

$stmt = $pdo->prepare("
    INSERT INTO document_field_templates 
    (document_type, field_name, field_label, field_type, is_required, validation_regex, default_value, display_order)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        field_label = VALUES(field_label),
        field_type = VALUES(field_type),
        is_required = VALUES(is_required),
        validation_regex = VALUES(validation_regex),
        default_value = VALUES(default_value),
        display_order = VALUES(display_order),
        updated_at = CURRENT_TIMESTAMP
");

$inserted = 0;
foreach ($fieldTemplates as $template) {
    try {
        $stmt->execute($template);
        $inserted++;
    } catch (Exception $e) {
        // Ignore duplicates
    }
}

echo "Inserted/Updated $inserted document field templates\n";

echo "\n=== Document AI tables and templates created successfully ===\n";?>