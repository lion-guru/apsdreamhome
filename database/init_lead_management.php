<?php
/**
 * Lead Management System Database Initialization
 * 
 * This script sets up all necessary database tables and initial data
 * for the APS Dream Homes Lead Management System.
 */

// Load configuration
require_once __DIR__ . '/../includes/config.php';

// Get database connection from config
$config = AppConfig::getInstance();
$dbConfig = $config->get('database');

// Create database connection
$conn = new mysqli(
    $dbConfig['host'],
    $dbConfig['user'],
    $dbConfig['pass'],
    $dbConfig['name'],
    $dbConfig['port']
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset('utf8mb4');

// Start transaction
$conn->begin_transaction();

try {
    echo "Starting database initialization...\n";
    
    // 1. Add new user roles if they don't exist
    echo "Updating users table...\n";
    $sql = "ALTER TABLE `users` 
            MODIFY COLUMN `utype` ENUM('user','agent','builder','admin','sales_agent','lead_manager') 
            NOT NULL DEFAULT 'user'";
    $conn->query($sql);
    
    // 2. Create contact_inquiries table
    echo "Creating contact_inquiries table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `contact_inquiries` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `subject` varchar(200) NOT NULL,
        `message` text NOT NULL,
        `source` varchar(50) DEFAULT 'website',
        `status` enum('new','contacted','qualified','converted','lost') NOT NULL DEFAULT 'new',
        `assigned_to` int(11) DEFAULT NULL,
        `client_id` int(11) DEFAULT NULL,
        `last_contacted_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `email` (`email`),
        KEY `status` (`status`),
        KEY `assigned_to` (`assigned_to`),
        KEY `client_id` (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 3. Create lead_activities table
    echo "Creating lead_activities table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `lead_activities` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `lead_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `activity_type` enum('call','email','meeting','note','status_change') NOT NULL,
        `activity_details` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `lead_id` (`lead_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 4. Create clients table
    echo "Creating clients table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `clients` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `city` varchar(100) DEFAULT NULL,
        `state` varchar(50) DEFAULT NULL,
        `postal_code` varchar(20) DEFAULT NULL,
        `country` varchar(50) DEFAULT 'India',
        `source` varchar(50) DEFAULT NULL,
        `status` enum('active','inactive','prospect','lead') NOT NULL DEFAULT 'active',
        `assigned_to` int(11) DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_by` int(11) NOT NULL,
        `updated_by` int(11) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `email` (`email`),
        KEY `assigned_to` (`assigned_to`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 5. Create client_contacts table
    echo "Creating client_contacts table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `client_contacts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `client_id` int(11) NOT NULL,
        `name` varchar(100) NOT NULL,
        `position` varchar(100) DEFAULT NULL,
        `email` varchar(100) DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `mobile` varchar(20) DEFAULT NULL,
        `is_primary` tinyint(1) NOT NULL DEFAULT 0,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `client_id` (`client_id`),
        KEY `is_primary` (`is_primary`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 6. Create client_notes table
    echo "Creating client_notes table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `client_notes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `client_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `note` text NOT NULL,
        `is_important` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `client_id` (`client_id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 7. Create client_tasks table
    echo "Creating client_tasks table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `client_tasks` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `client_id` int(11) DEFAULT NULL,
        `lead_id` int(11) DEFAULT NULL,
        `assigned_to` int(11) NOT NULL,
        `assigned_by` int(11) NOT NULL,
        `title` varchar(255) NOT NULL,
        `description` text DEFAULT NULL,
        `due_date` date DEFAULT NULL,
        `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
        `status` enum('not_started','in_progress','completed','deferred','cancelled') NOT NULL DEFAULT 'not_started',
        `completed_at` timestamp NULL DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `client_id` (`client_id`),
        KEY `lead_id` (`lead_id`),
        KEY `assigned_to` (`assigned_to`),
        KEY `assigned_by` (`assigned_by`),
        KEY `status` (`status`),
        KEY `due_date` (`due_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 8. Create notifications table
    echo "Creating notifications table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `notifications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `title` varchar(100) NOT NULL,
        `message` text NOT NULL,
        `type` varchar(50) DEFAULT NULL,
        `reference_id` int(11) DEFAULT NULL,
        `reference_type` varchar(50) DEFAULT NULL,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `read_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `reference` (`reference_type`,`reference_id`),
        KEY `is_read` (`is_read`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 9. Create audit_log table
    echo "Creating audit_log table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS `audit_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(50) NOT NULL,
        `table_name` varchar(50) NOT NULL,
        `record_id` int(11) NOT NULL,
        `old_values` text DEFAULT NULL,
        `new_values` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` varchar(255) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `table_record` (`table_name`,`record_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
    
    // 10. Add foreign key constraints
    echo "Adding foreign key constraints...\n";
    $constraints = [
        // Contact Inquiries
        "ALTER TABLE `contact_inquiries` 
         ADD CONSTRAINT `fk_lead_assigned_to` 
         FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL",
        
        "ALTER TABLE `contact_inquiries`
         ADD CONSTRAINT `fk_lead_client` 
         FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL",
        
        // Lead Activities
        "ALTER TABLE `lead_activities`
         ADD CONSTRAINT `fk_activity_lead` 
         FOREIGN KEY (`lead_id`) REFERENCES `contact_inquiries` (`id`) ON DELETE CASCADE",
        
        "ALTER TABLE `lead_activities`
         ADD CONSTRAINT `fk_activity_user` 
         FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",
        
        // Clients
        "ALTER TABLE `clients`
         ADD CONSTRAINT `fk_client_assigned_to` 
         FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL",
        
        "ALTER TABLE `clients`
         ADD CONSTRAINT `fk_client_created_by` 
         FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)",
        
        // Client Contacts
        "ALTER TABLE `client_contacts`
         ADD CONSTRAINT `fk_contact_client` 
         FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE",
        
        // Client Notes
        "ALTER TABLE `client_notes`
         ADD CONSTRAINT `fk_note_client` 
         FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE",
        
        "ALTER TABLE `client_notes`
         ADD CONSTRAINT `fk_note_user` 
         FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)",
        
        // Client Tasks
        "ALTER TABLE `client_tasks`
         ADD CONSTRAINT `fk_task_client` 
         FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE",
        
        "ALTER TABLE `client_tasks`
         ADD CONSTRAINT `fk_task_lead` 
         FOREIGN KEY (`lead_id`) REFERENCES `contact_inquiries` (`id`) ON DELETE SET NULL",
        
        "ALTER TABLE `client_tasks`
         ADD CONSTRAINT `fk_task_assigned_to` 
         FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)",
        
        "ALTER TABLE `client_tasks`
         ADD CONSTRAINT `fk_task_assigned_by` 
         FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`)",
        
        // Notifications
        "ALTER TABLE `notifications`
         ADD CONSTRAINT `fk_notification_user` 
         FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE",
        
        // Audit Log
        "ALTER TABLE `audit_log`
         ADD CONSTRAINT `fk_audit_user` 
         FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL"
    ];
    
    foreach ($constraints as $constraint) {
        try {
            $conn->query($constraint);
        } catch (Exception $e) {
            echo "Warning: " . $e->getMessage() . "\n";
        }
    }
    
    // 11. Create indexes for better performance
    echo "Creating indexes...\n";
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_lead_created_at ON contact_inquiries(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_lead_status_created ON contact_inquiries(status, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_lead_assigned_status ON contact_inquiries(assigned_to, status)",
        "CREATE INDEX IF NOT EXISTS idx_activity_created ON lead_activities(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_notification_user_read ON notifications(user_id, is_read)",
        "CREATE INDEX IF NOT EXISTS idx_client_status ON clients(status)",
        "CREATE INDEX IF NOT EXISTS idx_task_due_status ON client_tasks(due_date, status)",
        "CREATE INDEX IF NOT EXISTS idx_client_name ON clients(name)",
        "CREATE INDEX IF NOT EXISTS idx_contact_email ON client_contacts(email)",
        "CREATE INDEX IF NOT EXISTS idx_note_important ON client_notes(is_important, created_at)"
    ];
    
    foreach ($indexes as $index) {
        try {
            $conn->query($index);
        } catch (Exception $e) {
            echo "Warning creating index: " . $e->getMessage() . "\n";
        }
    }
    
    // 12. Create default admin user if not exists
    echo "Creating default admin user...\n";
    $defaultPassword = password_hash('admin@123', PASSWORD_ARGON2ID);
    $sql = "INSERT IGNORE INTO `users` 
            (`name`, `email`, `password`, `utype`, `status`, `created_at`, `updated_at`) 
            VALUES 
            ('Admin User', 'admin@apsdreamhomes.com', ?, 'admin', 'active', NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $defaultPassword);
    $stmt->execute();
    
    // 13. Create sample sales agent
    echo "Creating sample sales agent...\n";
    $agentPassword = password_hash('agent@123', PASSWORD_ARGON2ID);
    $sql = "INSERT IGNORE INTO `users` 
            (`name`, `email`, `password`, `utype`, `status`, `created_at`, `updated_at`) 
            VALUES 
            ('Sales Agent', 'agent@apsdreamhomes.com', ?, 'sales_agent', 'active', NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $agentPassword);
    $stmt->execute();
    
    // 14. Create sample lead manager
    echo "Creating sample lead manager...\n";
    $managerPassword = password_hash('manager@123', PASSWORD_ARGON2ID);
    $sql = "INSERT IGNORE INTO `users` 
            (`name`, `email`, `password`, `utype`, `status`, `created_at`, `updated_at`) 
            VALUES 
            ('Lead Manager', 'manager@apsdreamhomes.com', ?, 'lead_manager', 'active', NOW(), NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $managerPassword);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo "\nDatabase initialization completed successfully!\n";
    echo "Default login credentials:\n";
    echo "Admin: admin@apsdreamhomes.com / admin@123\n";
    echo "Agent: agent@apsdreamhomes.com / agent@123\n";
    echo "Manager: manager@apsdreamhomes.com / manager@123\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Rolling back changes...\n";
}

// Close connection
$conn->close();

// Helper function to check if table exists
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result->num_rows > 0;
}
