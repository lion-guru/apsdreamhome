<?php

use Database\Database;
/**
 * Migration for creating leads management system tables
 */

class CreateLeadsTables {
    /**
     * Run the migrations.
     */
    public function up() {
        // The Database class uses a singleton pattern, and getConnection() is not public.
        // Queries should be executed directly via Database::getInstance()->query().
        Database::getInstance()->query('SET FOREIGN_KEY_CHECKS=0');
        
        // Create leads table
        Database::getInstance()->query("\n            CREATE TABLE IF NOT EXISTS `leads` (\n                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n                `first_name` VARCHAR(255) NOT NULL,\n                `last_name` VARCHAR(255) NOT NULL,\n                `email` VARCHAR(255) NULL DEFAULT NULL,\n                `phone` VARCHAR(255) NULL DEFAULT NULL,\n                `source` VARCHAR(255) NULL DEFAULT NULL,\n                `status` VARCHAR(255) NULL DEFAULT NULL,\n                `assigned_to` BIGINT UNSIGNED NULL DEFAULT NULL,\n                `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,\n                `updated_by` BIGINT UNSIGNED NULL DEFAULT NULL,\n                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n                PRIMARY KEY (`id`),\n                INDEX `idx_leads_email` (`email`),\n                INDEX `idx_leads_phone` (`phone`),\n                INDEX `idx_leads_status` (`status`),\n                INDEX `idx_leads_assigned_to` (`assigned_to`),\n                INDEX `idx_leads_created_by` (`created_by`),\n                INDEX `idx_leads_updated_by` (`updated_by`),\n                CONSTRAINT `fk_leads_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,\n                CONSTRAINT `fk_leads_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,\n                CONSTRAINT `fk_leads_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `lead_activities` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `activity_type` VARCHAR(255) NOT NULL,
                `description` TEXT NULL DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_lead_activities_lead_id` (`lead_id`),
                CONSTRAINT `fk_lead_activities_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        
        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `lead_notes` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
                `note` TEXT NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_lead_notes_lead_id` (`lead_id`),
                INDEX `idx_lead_notes_user_id` (`user_id`),
                CONSTRAINT `fk_lead_notes_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_lead_notes_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        
        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `lead_files` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(255) NOT NULL,
                `file_type` VARCHAR(255) NULL DEFAULT NULL,
                `file_size` INT NULL DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_lead_files_lead_id` (`lead_id`),
                INDEX `idx_lead_files_user_id` (`user_id`),
                CONSTRAINT `fk_lead_files_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_lead_files_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        
        // Create lead_statuses table
        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `lead_statuses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `status_name` VARCHAR(255) NOT NULL UNIQUE,
                `status_description` TEXT NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        
        // Insert default lead statuses
        Database::getInstance()->query("
            INSERT IGNORE INTO `lead_statuses` (`status_name`, `status_description`) VALUES
            ('New', 'Newly created lead'),
            ('Contacted', 'Lead has been contacted'),
            ('Qualified', 'Lead has been qualified'),
            ('Unqualified', 'Lead has been unqualified'),
            ('Converted', 'Lead has been converted to customer');
        ");

        
        // Create lead_sources table
        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `lead_sources` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `source_name` VARCHAR(255) NOT NULL UNIQUE,
                `source_description` TEXT NULL DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        
        // Insert default lead sources
        Database::getInstance()->query("
            INSERT IGNORE INTO `lead_sources` (`source_name`, `source_description`) VALUES
            ('Website', 'Lead originated from website form'),
            ('Referral', 'Lead came from a referral'),
            ('Advertisement', 'Lead from an advertisement campaign'),
            ('Cold Call', 'Lead generated from a cold call'),
            ('Event', 'Lead from an event or conference');
        ");

        
        // Create triggers for updated_at columns
        Database::getInstance()->query("DROP TRIGGER IF EXISTS `set_leads_updated_at`");
        Database::getInstance()->query("
            CREATE TRIGGER `set_leads_updated_at`
            BEFORE UPDATE ON `leads`
            FOR EACH ROW
            SET NEW.updated_at = NOW();
        ");

        Database::getInstance()->query("DROP TRIGGER IF EXISTS `set_lead_notes_updated_at`");
        Database::getInstance()->query("
            CREATE TRIGGER `set_lead_notes_updated_at`
            BEFORE UPDATE ON `lead_notes`
            FOR EACH ROW
            SET NEW.updated_at = NOW();
        ");
        
        
        Database::getInstance()->query("
            CREATE TABLE IF NOT EXISTS `lead_assignment_history` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `assigned_to` BIGINT UNSIGNED NULL DEFAULT NULL,
                `assigned_by` BIGINT UNSIGNED NULL DEFAULT NULL,
                `notes` TEXT NULL DEFAULT NULL,
                `assigned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_lead_assignment_history_lead_id` (`lead_id`),
                INDEX `idx_lead_assignment_history_assigned_to` (`assigned_to`),
                INDEX `idx_lead_assignment_history_assigned_by` (`assigned_by`),
                CONSTRAINT `fk_lead_assignment_history_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_lead_assignment_history_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk_lead_assignment_history_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        
        Database::getInstance()->query("\n            CREATE TABLE IF NOT EXISTS `lead_status_history` (\n                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n                `lead_id` BIGINT UNSIGNED NOT NULL,\n                `old_status` VARCHAR(255) NULL DEFAULT NULL,\n                `new_status` VARCHAR(255) NULL DEFAULT NULL,\n                `changed_by` BIGINT UNSIGNED NULL DEFAULT NULL,\n                `notes` TEXT NULL DEFAULT NULL,\n                `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n                PRIMARY KEY (`id`),\n                INDEX `idx_lead_status_history_lead_id` (`lead_id`),\n                INDEX `idx_lead_status_history_changed_by` (`changed_by`),\n                CONSTRAINT `fk_lead_status_history_lead_id` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,\n                CONSTRAINT `fk_lead_status_history_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n        ");
        
        // Create triggers for lead status history and assignment history
        Database::getInstance()->query("DROP TRIGGER IF EXISTS `after_lead_status_change`");
        Database::getInstance()->query("
            -- Trigger to log status changes
            CREATE TRIGGER after_lead_status_change
            AFTER UPDATE ON leads
            FOR EACH ROW
            BEGIN
                IF OLD.status != NEW.status THEN
                    INSERT INTO lead_status_history (
                        lead_id, 
                        old_status, 
                        new_status, 
                        changed_by,
                        notes
                    ) VALUES (
                        NEW.id,
                        OLD.status,
                        NEW.status,
                        NULL, -- This will be populated by the application
                        CONCAT('Status changed from ', OLD.status, ' to ', NEW.status)
                    );
                END IF;
                
                IF OLD.assigned_to != NEW.assigned_to THEN
                    INSERT INTO lead_assignment_history (
                        lead_id,
                        assigned_to,
                        assigned_by,
                        notes
                    ) VALUES (
                        NEW.id,
                        NEW.assigned_to,
                        NULL, -- This will be populated by the application
                        CONCAT(
                            'Lead assigned to ',
                            IFNULL((SELECT name FROM users WHERE id = NEW.assigned_to), 'Unassigned')
                        )
                    );
                END IF;
            END;
        ");
        
        // Re-enable foreign key checks
        Database::getInstance()->query('SET FOREIGN_KEY_CHECKS=1');
        
        return true;
    }
    
    /**
     * Reverse the migrations.
     */
    public function down() {
        Database::getInstance()->query('SET FOREIGN_KEY_CHECKS=0');
        // Drop tables in reverse order to avoid foreign key constraint issues
        $tables = [
            'lead_custom_field_values',
            'lead_custom_fields',
            'lead_status_history',
            'lead_assignment_history',
            'lead_tag_mapping',
            'lead_files',
            'lead_notes',
            'lead_activities',
            'lead_tags',
            'leads',
            'lead_statuses',
            'lead_sources'
        ];
        
        // Drop triggers first
        Database::getInstance()->query("DROP TRIGGER IF EXISTS after_lead_status_change");
        
        // Drop tables
        foreach ($tables as $table) {
            Database::getInstance()->query("DROP TABLE IF EXISTS `$table`");
        }
        Database::getInstance()->query('SET FOREIGN_KEY_CHECKS=1');
        
        return true;
    }
}

// Run the migration
$migration = new CreateLeadsTables();
$migration->up();

echo "Leads tables created successfully!\n";