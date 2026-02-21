<?php
/**
 * Script to create AI Document OCR system tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Connected to database successfully.\n";

    // Function to execute SQL queries
    function executeQuery($pdo, $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                echo "Query executed successfully\n";
                return true;
            } else {
                echo "Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Create OCR documents table
    $sql = "CREATE TABLE IF NOT EXISTS `ocr_documents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `original_document_id` INT NULL COMMENT 'Link to employee_documents or other document tables',
        `file_path` VARCHAR(500) NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_size` INT NOT NULL,
        `mime_type` VARCHAR(100) NOT NULL,
        `document_type` ENUM('aadhar','pan','passport','driving_license','bank_statement','salary_slip','invoice','receipt','contract','other') DEFAULT 'other',
        `ocr_status` ENUM('pending','processing','completed','failed') DEFAULT 'pending',
        `processing_time` DECIMAL(5,2) NULL COMMENT 'Time taken for OCR processing in seconds',
        `confidence_score` DECIMAL(5,4) NULL COMMENT 'Overall OCR confidence score (0-1)',
        `extracted_text` LONGTEXT NULL,
        `structured_data` JSON NULL COMMENT 'Extracted structured data',
        `validation_status` ENUM('pending','valid','invalid','requires_review') DEFAULT 'pending',
        `validation_errors` JSON NULL COMMENT 'Validation errors if any',
        `processed_by` VARCHAR(50) NULL COMMENT 'OCR engine used',
        `processed_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_ocr_document` (`original_document_id`),
        INDEX `idx_ocr_status` (`ocr_status`),
        INDEX `idx_ocr_type` (`document_type`),
        INDEX `idx_ocr_validation` (`validation_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ OCR documents table created successfully!\n";
    }

    // Create OCR templates table
    $sql = "CREATE TABLE IF NOT EXISTS `ocr_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `template_name` VARCHAR(255) NOT NULL,
        `document_type` ENUM('aadhar','pan','passport','driving_license','bank_statement','salary_slip','invoice','receipt','contract','other') NOT NULL,
        `template_config` JSON NOT NULL COMMENT 'Template configuration for field extraction',
        `field_mappings` JSON NOT NULL COMMENT 'Field mappings for structured data extraction',
        `validation_rules` JSON NULL COMMENT 'Validation rules for extracted data',
        `sample_image_path` VARCHAR(500) NULL,
        `accuracy_score` DECIMAL(5,4) NULL COMMENT 'Template accuracy score',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_template_type` (`document_type`),
        INDEX `idx_template_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ OCR templates table created successfully!\n";
    }

    // Create extracted fields table
    $sql = "CREATE TABLE IF NOT EXISTS `ocr_extracted_fields` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ocr_document_id` INT NOT NULL,
        `field_name` VARCHAR(100) NOT NULL,
        `field_value` TEXT NULL,
        `confidence_score` DECIMAL(5,4) NULL,
        `bounding_box` JSON NULL COMMENT 'Coordinates of the field in the document',
        `validation_status` ENUM('valid','invalid','uncertain') DEFAULT 'valid',
        `validation_message` VARCHAR(255) NULL,
        `corrected_value` TEXT NULL,
        `corrected_by` INT NULL,
        `corrected_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`ocr_document_id`) REFERENCES `ocr_documents`(`id`) ON DELETE CASCADE,
        INDEX `idx_extracted_doc` (`ocr_document_id`),
        INDEX `idx_extracted_field` (`field_name`),
        INDEX `idx_extracted_validation` (`validation_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ OCR extracted fields table created successfully!\n";
    }

    // Create OCR processing queue table
    $sql = "CREATE TABLE IF NOT EXISTS `ocr_processing_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `document_id` INT NOT NULL,
        `priority` ENUM('low','normal','high','urgent') DEFAULT 'normal',
        `processing_attempts` INT DEFAULT 0,
        `max_attempts` INT DEFAULT 3,
        `last_attempt_at` DATETIME NULL,
        `next_attempt_at` DATETIME NULL,
        `error_message` TEXT NULL,
        `status` ENUM('queued','processing','completed','failed','cancelled') DEFAULT 'queued',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_queue_status` (`status`),
        INDEX `idx_queue_priority` (`priority`),
        INDEX `idx_queue_next_attempt` (`next_attempt_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ OCR processing queue table created successfully!\n";
    }

    // Create document classification table
    $sql = "CREATE TABLE IF NOT EXISTS `document_classification` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `document_content_hash` VARCHAR(64) NOT NULL UNIQUE,
        `predicted_type` ENUM('aadhar','pan','passport','driving_license','bank_statement','salary_slip','invoice','receipt','contract','other') NOT NULL,
        `confidence_score` DECIMAL(5,4) NOT NULL,
        `actual_type` ENUM('aadhar','pan','passport','driving_license','bank_statement','salary_slip','invoice','receipt','contract','other') NULL,
        `feedback_provided` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_classification_hash` (`document_content_hash`),
        INDEX `idx_classification_type` (`predicted_type`),
        INDEX `idx_classification_confidence` (`confidence_score`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Document classification table created successfully!\n";
    }

    // Insert default OCR templates
    $defaultTemplates = [
        [
            'Aadhaar Card Template',
            'aadhar',
            '{"regions": {"name": {"x": 100, "y": 200, "width": 300, "height": 50}, "aadhaar_number": {"x": 100, "y": 300, "width": 200, "height": 40}, "address": {"x": 50, "y": 400, "width": 400, "height": 100}}}',
            '{"name": {"pattern": "/^[A-Za-z\\s]+$/", "required": true}, "aadhaar_number": {"pattern": "/^\\d{4}\\s\\d{4}\\s\\d{4}$/", "required": true}, "address": {"required": true}}',
            '{"name": {"max_length": 100}, "aadhaar_number": {"exact_length": 14}, "address": {"max_length": 500}}'
        ],
        [
            'PAN Card Template',
            'pan',
            '{"regions": {"name": {"x": 50, "y": 150, "width": 250, "height": 40}, "pan_number": {"x": 50, "y": 200, "width": 150, "height": 35}, "dob": {"x": 50, "y": 250, "width": 120, "height": 30}}}',
            '{"name": {"pattern": "/^[A-Za-z\\s]+$/", "required": true}, "pan_number": {"pattern": "/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/", "required": true}, "dob": {"pattern": "/^\\d{2}\\/\\d{2}\\/\\d{4}$/", "required": true}}',
            '{"name": {"max_length": 100}, "pan_number": {"exact_length": 10}, "dob": {"format": "DD/MM/YYYY"}}'
        ],
        [
            'Invoice Template',
            'invoice',
            '{"regions": {"invoice_number": {"x": 400, "y": 50, "width": 150, "height": 30}, "invoice_date": {"x": 400, "y": 90, "width": 120, "height": 25}, "total_amount": {"x": 400, "y": 500, "width": 120, "height": 30}}}',
            '{"invoice_number": {"pattern": "/^(INV|INV-)\\d+$/", "required": true}, "invoice_date": {"pattern": "/^\\d{2}\\/\\d{2}\\/\\d{4}$/", "required": true}, "total_amount": {"pattern": "/^\\d+(\\.\\d{2})?$/", "required": true}}',
            '{"invoice_number": {"max_length": 50}, "total_amount": {"min_value": 0}}'
        ]
    ];

    $insertTemplateSql = "INSERT IGNORE INTO `ocr_templates` (`template_name`, `document_type`, `template_config`, `field_mappings`, `validation_rules`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertTemplateSql);

    foreach ($defaultTemplates as $template) {
        $stmt->execute($template);
    }

    echo "✅ Default OCR templates inserted successfully!\n";

    echo "\n🎉 AI Document OCR system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
