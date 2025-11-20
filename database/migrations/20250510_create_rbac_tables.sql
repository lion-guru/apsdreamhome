-- Role-Based Access Control (RBAC) Tables Migration

-- Roles Table
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `permissions` JSON NOT NULL,
    `is_default` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Roles Mapping Table
CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `assigned_by` INT UNSIGNED,
    UNIQUE KEY `unique_user_role` (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Change History Table
CREATE TABLE IF NOT EXISTS `role_change_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `previous_role_id` INT UNSIGNED,
    `new_role_id` INT UNSIGNED NOT NULL,
    `changed_by` INT UNSIGNED,
    `change_reason` TEXT,
    `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`previous_role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`new_role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Role Seeding
INSERT INTO `roles` (`name`, `description`, `permissions`, `is_default`) VALUES
('super_admin', 'Full system access', 
 JSON_ARRAY(
    'dashboard_view', 
    'user_management', 
    'system_settings', 
    'database_access', 
    'full_admin_panel', 
    'security_logs', 
    'user_create', 
    'user_edit', 
    'user_delete'
 ), FALSE),
('admin', 'Advanced administrative access', 
 JSON_ARRAY(
    'dashboard_view', 
    'user_management', 
    'system_settings', 
    'user_create', 
    'user_edit'
 ), TRUE),
('manager', 'Limited administrative access', 
 JSON_ARRAY(
    'dashboard_view', 
    'limited_user_management', 
    'report_view'
 ), FALSE),
('editor', 'Content management access', 
 JSON_ARRAY(
    'dashboard_view', 
    'content_edit', 
    'content_publish'
 ), FALSE)
ON DUPLICATE KEY UPDATE 
    `description` = VALUES(`description`), 
    `permissions` = VALUES(`permissions`);

-- Indexes for performance
CREATE INDEX idx_roles_name ON `roles` (`name`);
CREATE INDEX idx_user_roles_user ON `user_roles` (`user_id`);
CREATE INDEX idx_role_change_history_user ON `role_change_history` (`user_id`);
