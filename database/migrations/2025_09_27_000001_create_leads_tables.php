<?php
/**
 * Migration for creating leads management system tables
 */

class CreateLeadsTables {
    /**
     * Run the migrations.
     */
    public function up() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Enable foreign key checks
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        
        // Create leads table
        $conn->query("
            CREATE TABLE IF NOT EXISTS `leads` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `first_name` VARCHAR(100) NOT NULL,
                `last_name` VARCHAR(100) DEFAULT NULL,
                `email` VARCHAR(255) DEFAULT NULL,
                `phone` VARCHAR(50) DEFAULT NULL,
                `mobile` VARCHAR(50) DEFAULT NULL,
                `company` VARCHAR(255) DEFAULT NULL,
                `job_title` VARCHAR(255) DEFAULT NULL,
                `website` VARCHAR(255) DEFAULT NULL,
                `address` TEXT DEFAULT NULL,
                `city` VARCHAR(100) DEFAULT NULL,
                `state` VARCHAR(100) DEFAULT NULL,
                `postal_code` VARCHAR(20) DEFAULT NULL,
                `country` VARCHAR(100) DEFAULT NULL,
                `source` VARCHAR(50) NOT NULL DEFAULT 'website',
                `status` VARCHAR(50) NOT NULL DEFAULT 'new',
                `rating` TINYINT UNSIGNED DEFAULT NULL,
                `estimated_value` DECIMAL(15, 2) DEFAULT 0.00,
                `description` TEXT DEFAULT NULL,
                `last_contact_date` DATETIME DEFAULT NULL,
                `next_followup_date` DATETIME DEFAULT NULL,
                `assigned_to` BIGINT UNSIGNED DEFAULT NULL,
                `created_by` BIGINT UNSIGNED NOT NULL,
                `updated_by` BIGINT UNSIGNED DEFAULT NULL,
                `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `date_modified` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
                `custom_fields` JSON DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_status` (`status`),
                KEY `idx_assigned_to` (`assigned_to`),
                KEY `idx_created_by` (`created_by`),
                KEY `idx_date_created` (`date_created`),
                KEY `idx_source` (`source`),
                KEY `idx_email` (`email`),
                KEY `idx_phone` (`phone`),
                CONSTRAINT `fk_leads_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
                CONSTRAINT `fk_leads_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
            -- Lead activities table
            CREATE TABLE IF NOT EXISTS `lead_activities` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `activity_type` VARCHAR(50) NOT NULL,
                `activity_details` TEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `metadata` JSON DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_activity_type` (`activity_type`),
                KEY `idx_created_at` (`created_at`),
                CONSTRAINT `fk_lead_activities_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead notes table
            CREATE TABLE IF NOT EXISTS `lead_notes` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `content` TEXT NOT NULL,
                `is_private` TINYINT(1) NOT NULL DEFAULT 0,
                `created_by` BIGINT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_created_by` (`created_by`),
                CONSTRAINT `fk_lead_notes_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_notes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead files table
            CREATE TABLE IF NOT EXISTS `lead_files` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `original_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(512) NOT NULL,
                `file_type` VARCHAR(100) NOT NULL,
                `file_size` BIGINT UNSIGNED NOT NULL,
                `description` TEXT DEFAULT NULL,
                `uploaded_by` BIGINT UNSIGNED NOT NULL,
                `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `is_private` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_uploaded_by` (`uploaded_by`),
                CONSTRAINT `fk_lead_files_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_files_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead tags table
            CREATE TABLE IF NOT EXISTS `lead_tags` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                `color` VARCHAR(20) DEFAULT '#3498db',
                `created_by` BIGINT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `is_system` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_name` (`name`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead to tags mapping
            CREATE TABLE IF NOT EXISTS `lead_tag_mapping` (
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `tag_id` BIGINT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `created_by` BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (`lead_id`, `tag_id`),
                KEY `idx_tag_id` (`tag_id`),
                KEY `idx_created_by` (`created_by`),
                CONSTRAINT `fk_lead_tag_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_tag_tag` FOREIGN KEY (`tag_id`) REFERENCES `lead_tags` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_tag_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead status history
            CREATE TABLE IF NOT EXISTS `lead_status_history` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `old_status` VARCHAR(50) NOT NULL,
                `new_status` VARCHAR(50) NOT NULL,
                `changed_by` BIGINT UNSIGNED NOT NULL,
                `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `notes` TEXT DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_changed_at` (`changed_at`),
                KEY `idx_new_status` (`new_status`),
                CONSTRAINT `fk_lead_status_history_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_status_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead custom fields
            CREATE TABLE IF NOT EXISTS `lead_custom_fields` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `field_name` VARCHAR(100) NOT NULL,
                `field_label` VARCHAR(255) NOT NULL,
                `field_type` ENUM('text', 'textarea', 'select', 'checkbox', 'radio', 'date', 'datetime', 'number', 'email', 'url', 'tel') NOT NULL,
                `field_options` JSON DEFAULT NULL,
                `is_required` TINYINT(1) NOT NULL DEFAULT 0,
                `default_value` TEXT DEFAULT NULL,
                `validation_rules` JSON DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                `created_by` BIGINT UNSIGNED NOT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `field_group` VARCHAR(100) DEFAULT 'General',
                `display_order` INT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_field_name` (`field_name`),
                KEY `idx_field_group` (`field_group`),
                KEY `idx_is_active` (`is_active`),
                KEY `idx_display_order` (`display_order`),
                CONSTRAINT `fk_lead_custom_field_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead custom field values
            CREATE TABLE IF NOT EXISTS `lead_custom_field_values` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `field_id` BIGINT UNSIGNED NOT NULL,
                `field_value` TEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                `updated_by` BIGINT UNSIGNED DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_lead_field` (`lead_id`, `field_id`),
                KEY `idx_field_id` (`field_id`),
                CONSTRAINT `fk_lead_custom_value_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_custom_value_field` FOREIGN KEY (`field_id`) REFERENCES `lead_custom_fields` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_custom_value_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            
            -- Lead assignment history
            CREATE TABLE IF NOT EXISTS `lead_assignment_history` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `lead_id` BIGINT UNSIGNED NOT NULL,
                `assigned_to` BIGINT UNSIGNED DEFAULT NULL,
                `assigned_by` BIGINT UNSIGNED NOT NULL,
                `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `notes` TEXT DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_assigned_to` (`assigned_to`),
                KEY `idx_assigned_at` (`assigned_at`),
                CONSTRAINT `fk_lead_assignment_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_lead_assignment_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
                CONSTRAINT `fk_lead_assignment_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Create triggers for lead status history and assignment history
        $conn->query("
            -- Trigger to log status changes
            DELIMITER //
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
                        COALESCE(NEW.updated_by, NEW.created_by),
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
                        COALESCE(NEW.updated_by, NEW.created_by),
                        CONCAT(
                            'Lead assigned to ', 
                            IFNULL((SELECT name FROM users WHERE id = NEW.assigned_to), 'Unassigned'),
                            ' by ',
                            (SELECT name FROM users WHERE id = COALESCE(NEW.updated_by, NEW.created_by))
                        )
                    );
                END IF;
            END//
            DELIMITER ;
        
            -- Insert default lead statuses
            INSERT IGNORE INTO `lead_statuses` (`name`, `color`, `is_default`, `display_order`) VALUES
            ('New', '#3498db', 1, 1),
            ('Contacted', '#2ecc71', 0, 2),
            ('Qualified', '#9b59b6', 0, 3),
            ('Proposal Sent', '#f1c40f', 0, 4),
            ('Negotiation', '#e67e22', 0, 5),
            ('Closed Won', '#27ae60', 0, 6),
            ('Closed Lost', '#e74c3c', 0, 7),
            ('On Hold', '#95a5a6', 0, 8);
            
            -- Insert default lead sources
            INSERT IGNORE INTO `lead_sources` (`name`, `description`, `is_active`) VALUES
            ('Website', 'Lead came from website form', 1),
            ('Referral', 'Referred by existing customer', 1),
            ('Social Media', 'From social media platforms', 1),
            ('Email Campaign', 'From email marketing campaign', 1),
            ('Cold Call', 'From outbound calling', 1),
            ('Trade Show', 'Met at a trade show or event', 1),
            ('Advertisement', 'From online/offline ads', 1),
            ('Other', 'Other source not listed', 1);
        ");
        
        // Re-enable foreign key checks
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        
        return true;
    }
    
    /**
     * Reverse the migrations.
     */
    public function down() {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
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
            'leads'
        ];
        
        // Drop triggers first
        $conn->query("DROP TRIGGER IF EXISTS after_lead_status_change");
        
        // Drop tables
        foreach ($tables as $table) {
            $conn->query("DROP TABLE IF EXISTS `$table`");
        }
        
        return true;
    }
}

// Run the migration
$migration = new CreateLeadsTables();
$migration->up();

echo "Leads tables created successfully!\n";
