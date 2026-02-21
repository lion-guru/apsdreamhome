<?php
/**
 * Script to create employee document repository tables
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

    // Create document categories table
    $sql = "CREATE TABLE IF NOT EXISTS `document_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT NULL,
        `parent_id` INT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`parent_id`) REFERENCES `document_categories`(`id`) ON DELETE SET NULL,
        INDEX `idx_category_parent` (`parent_id`),
        INDEX `idx_category_active` (`is_active`),
        INDEX `idx_category_sort` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Document categories table created successfully!\n";
    }

    // Create document types table
    $sql = "CREATE TABLE IF NOT EXISTS `document_types` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT NULL,
        `category_id` INT NULL,
        `file_extensions` JSON NULL COMMENT 'Allowed file extensions',
        `max_file_size` INT DEFAULT 5242880 COMMENT 'Max file size in bytes (5MB default)',
        `is_required` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`category_id`) REFERENCES `document_categories`(`id`) ON DELETE SET NULL,
        INDEX `idx_document_type_category` (`category_id`),
        INDEX `idx_document_type_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Document types table created successfully!\n";
    }

    // Create employee documents table
    $sql = "CREATE TABLE IF NOT EXISTS `employee_documents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `document_type_id` INT NULL,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `original_name` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(500) NOT NULL,
        `file_size` INT NOT NULL COMMENT 'File size in bytes',
        `file_extension` VARCHAR(10) NOT NULL,
        `mime_type` VARCHAR(100) NOT NULL,
        `version` INT DEFAULT 1,
        `is_latest` TINYINT(1) DEFAULT 1,
        `status` ENUM('active','archived','deleted') DEFAULT 'active',
        `uploaded_by` INT NULL,
        `expires_at` DATETIME NULL,
        `metadata` JSON NULL COMMENT 'Additional file metadata',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`document_type_id`) REFERENCES `document_types`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`uploaded_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_employee_doc_employee` (`employee_id`),
        INDEX `idx_employee_doc_type` (`document_type_id`),
        INDEX `idx_employee_doc_status` (`status`),
        INDEX `idx_employee_doc_latest` (`is_latest`),
        INDEX `idx_employee_doc_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Employee documents table created successfully!\n";
    }

    // Create document sharing table
    $sql = "CREATE TABLE IF NOT EXISTS `document_sharing` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `document_id` INT NOT NULL,
        `shared_with_employee_id` INT NULL,
        `shared_with_admin_id` INT NULL,
        `permissions` ENUM('view','download','edit') DEFAULT 'view',
        `expires_at` DATETIME NULL,
        `shared_by` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`document_id`) REFERENCES `employee_documents`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`shared_with_employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`shared_with_admin_id`) REFERENCES `admin`(`aid`) ON DELETE CASCADE,
        FOREIGN KEY (`shared_by`) REFERENCES `admin`(`aid`) ON DELETE SET NULL,

        INDEX `idx_sharing_document` (`document_id`),
        INDEX `idx_sharing_employee` (`shared_with_employee_id`),
        INDEX `idx_sharing_admin` (`shared_with_admin_id`),
        INDEX `idx_sharing_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Document sharing table created successfully!\n";
    }

    // Create document audit log table
    $sql = "CREATE TABLE IF NOT EXISTS `document_audit_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `document_id` INT NOT NULL,
        `action` ENUM('upload','download','view','share','delete','archive','restore') NOT NULL,
        `performed_by` INT NOT NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` TEXT NULL,
        `old_data` JSON NULL,
        `new_data` JSON NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`document_id`) REFERENCES `employee_documents`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`performed_by`) REFERENCES `admin`(`aid`) ON DELETE CASCADE,

        INDEX `idx_audit_document` (`document_id`),
        INDEX `idx_audit_action` (`action`),
        INDEX `idx_audit_performed_by` (`performed_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Document audit log table created successfully!\n";
    }

    // Insert default document categories
    $defaultCategories = [
        ['Personal Documents', 'Employee personal identification and certificates', null, 1, 1],
        ['Employment Documents', 'Job-related documents and contracts', null, 1, 2],
        ['Training & Certification', 'Training records and certifications', null, 1, 3],
        ['Performance Documents', 'Performance reviews and appraisals', null, 1, 4],
        ['Medical Documents', 'Health and medical records', null, 1, 5],
        ['Financial Documents', 'Salary slips and financial records', null, 1, 6]
    ];

    $insertSql = "INSERT IGNORE INTO `document_categories` (`name`, `description`, `parent_id`, `is_active`, `sort_order`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultCategories as $category) {
        $stmt->execute($category);
    }

    echo "✅ Default document categories inserted successfully!\n";

    // Insert default document types
    $defaultTypes = [
        ['Aadhaar Card', 'Indian national identity card', 1, '["pdf","jpg","jpeg","png"]', 2097152, 1, 1],
        ['PAN Card', 'Permanent Account Number card', 1, '["pdf","jpg","jpeg","png"]', 2097152, 1, 1],
        ['Passport', 'International travel document', 1, '["pdf","jpg","jpeg","png"]', 2097152, 0, 1],
        ['Resume/CV', 'Professional resume or curriculum vitae', 2, '["pdf","doc","docx"]', 5242880, 0, 1],
        ['Offer Letter', 'Employment offer document', 2, '["pdf","doc","docx"]', 5242880, 1, 1],
        ['Employment Contract', 'Official employment agreement', 2, '["pdf","doc","docx"]', 5242880, 1, 1],
        ['Salary Slip', 'Monthly salary statement', 6, '["pdf","jpg","jpeg","png"]', 2097152, 0, 1],
        ['Experience Certificate', 'Previous employment verification', 2, '["pdf","doc","docx"]', 5242880, 0, 1],
        ['Degree Certificate', 'Educational qualification certificate', 3, '["pdf","jpg","jpeg","png"]', 5242880, 0, 1],
        ['Training Certificate', 'Professional training completion certificate', 3, '["pdf","jpg","jpeg","png"]', 5242880, 0, 1]
    ];

    $insertTypeSql = "INSERT IGNORE INTO `document_types` (`name`, `description`, `category_id`, `file_extensions`, `max_file_size`, `is_required`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertTypeSql);

    foreach ($defaultTypes as $type) {
        $stmt->execute($type);
    }

    echo "✅ Default document types inserted successfully!\n";

    echo "\n🎉 Employee document repository system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
