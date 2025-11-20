-- Simple Upgrade Script for APS Dream Homes Lead Management
-- Adds essential tables and updates for enhanced lead tracking

-- 1. Update leads table with new columns
ALTER TABLE `leads` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `contact`,
ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `company` VARCHAR(100) NULL AFTER `name`,
ADD COLUMN IF NOT EXISTS `priority` ENUM('Low','Medium','High','Urgent') NULL DEFAULT 'Medium' AFTER `status`,
ADD COLUMN IF NOT EXISTS `next_followup` DATETIME NULL AFTER `converted_at`,
ADD COLUMN IF NOT EXISTS `last_contact` DATETIME NULL AFTER `next_followup`,
ADD COLUMN IF NOT EXISTS `lead_score` INT(3) DEFAULT 0 AFTER `priority`,
ADD COLUMN IF NOT EXISTS `tags` VARCHAR(255) NULL AFTER `lead_score`,
ADD COLUMN IF NOT EXISTS `client_id` INT(11) NULL AFTER `assigned_to`,
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 2. Create lead_activities table
CREATE TABLE IF NOT EXISTS `lead_activities` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `lead_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `activity_type` ENUM('Call','Email','Meeting','Note','Task') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `due_date` DATETIME NULL,
    `status` ENUM('Pending','Completed','Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead_activity` (`lead_id`),
    KEY `idx_activity_user` (`user_id`),
    KEY `idx_activity_type` (`activity_type`),
    KEY `idx_activity_status` (`status`),
    CONSTRAINT `fk_activity_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create lead_tasks table
CREATE TABLE IF NOT EXISTS `lead_tasks` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `lead_id` INT(11) NOT NULL,
    `assigned_to` INT(11) NOT NULL,
    `assigned_by` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `due_date` DATETIME NULL,
    `priority` ENUM('Low','Medium','High','Urgent') DEFAULT 'Medium',
    `status` ENUM('Not Started','In Progress','Completed','On Hold','Cancelled') DEFAULT 'Not Started',
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_task_lead` (`lead_id`),
    KEY `idx_task_assigned` (`assigned_to`),
    KEY `idx_task_priority` (`priority`),
    KEY `idx_task_status` (`status`),
    KEY `idx_task_due` (`due_date`),
    CONSTRAINT `fk_task_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_task_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_task_creator` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create lead_notes table
CREATE TABLE IF NOT EXISTS `lead_notes` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `lead_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `note` TEXT NOT NULL,
    `is_important` TINYINT(1) DEFAULT 0,
    `is_pinned` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_note_lead` (`lead_id`),
    KEY `idx_note_user` (`user_id`),
    KEY `idx_note_important` (`is_important`),
    KEY `idx_note_pinned` (`is_pinned`),
    CONSTRAINT `fk_note_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_note_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create lead_documents table
CREATE TABLE IF NOT EXISTS `lead_documents` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `lead_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(512) NOT NULL,
    `file_type` VARCHAR(100) NULL,
    `file_size` INT(11) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_document_lead` (`lead_id`),
    KEY `idx_document_user` (`user_id`),
    CONSTRAINT `fk_document_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Create lead_events table for audit trail
CREATE TABLE IF NOT EXISTS `lead_events` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `lead_id` INT(11) NOT NULL,
    `user_id` INT(11) NULL,
    `event_type` VARCHAR(100) NOT NULL,
    `event_data` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_event_lead` (`lead_id`),
    KEY `idx_event_user` (`user_id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_event_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Add foreign key for client relationship
ALTER TABLE `leads`
ADD CONSTRAINT `fk_lead_client` 
FOREIGN KEY (`client_id`) 
REFERENCES `clients` (`id`) 
ON DELETE SET NULL;

-- 8. Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_lead_status` ON `leads` (`status`);
CREATE INDEX IF NOT EXISTS `idx_lead_assigned` ON `leads` (`assigned_to`);
CREATE INDEX IF NOT EXISTS `idx_lead_created` ON `leads` (`created_at`);
CREATE INDEX IF NOT EXISTS `idx_lead_priority` ON `leads` (`priority`);

-- 9. Create trigger for lead status changes
DELIMITER //
CREATE TRIGGER IF NOT EXISTS after_lead_status_change 
AFTER UPDATE ON leads
FOR EACH ROW 
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO lead_events (lead_id, event_type, event_data, created_at)
        VALUES (NEW.id, 'status_change', 
                JSON_OBJECT(
                    'field', 'status',
                    'old_value', OLD.status,
                    'new_value', NEW.status
                ), 
                NOW());
    END IF;
    
    IF NEW.assigned_to IS NOT NULL AND (OLD.assigned_to IS NULL OR OLD.assigned_to != NEW.assigned_to) THEN
        INSERT INTO lead_events (lead_id, event_type, event_data, created_at)
        VALUES (NEW.id, 'assigned', 
                JSON_OBJECT(
                    'assigned_to', NEW.assigned_to,
                    'previous_assignee', OLD.assigned_to
                ), 
                NOW());
    END IF;
END //
DELIMITER ;

-- 10. Create a simple view for lead activities
CREATE OR REPLACE VIEW vw_lead_activities AS
SELECT 
    la.id,
    la.lead_id,
    l.name as lead_name,
    la.user_id,
    u.name as user_name,
    la.activity_type,
    la.title,
    la.description,
    la.due_date,
    la.status,
    la.created_at,
    la.updated_at
FROM 
    lead_activities la
JOIN 
    leads l ON la.lead_id = l.id
JOIN 
    users u ON la.user_id = u.id
WHERE 
    l.deleted_at IS NULL
ORDER BY 
    la.created_at DESC;
