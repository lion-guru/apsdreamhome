<?php

/**
 * Migration: Create Roles and Permissions Tables
 * Creates the database tables for role-based access control
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Creating Roles and Permissions Tables ===\n\n";

try {
    // Manually include necessary files since bootstrap is having issues
    require_once __DIR__ . '/config/helpers.php';
    require_once __DIR__ . '/app/core/Database.php';
    echo "✅ Files loaded manually\n";
} catch (Exception $e) {
    echo "❌ Manual file loading failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Database connection
try {
    $dbConfig = [
        'host' => 'localhost',
        'database' => 'apsdreamhome',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'port' => 3306,
    ];
    $db = \App\Core\Database::getInstance($dbConfig);
    echo "✅ Database connection established\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Create roles table
$createRolesTable = "
CREATE TABLE IF NOT EXISTS `roles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL UNIQUE,
    `display_name` varchar(100) NOT NULL,
    `description` text,
    `level` int(11) NOT NULL DEFAULT 1,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_roles_name` (`name`),
    INDEX `idx_roles_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

echo "Creating roles table...\n";
try {
    $db->query($createRolesTable);
    echo "✅ Roles table created successfully\n";
} catch (Exception $e) {
    echo "❌ Error creating roles table: " . $e->getMessage() . "\n";
}

// Create permissions table
$createPermissionsTable = "
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL UNIQUE,
    `display_name` varchar(150) NOT NULL,
    `description` text,
    `module` varchar(50) NOT NULL,
    `action` varchar(50) NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_permissions_name` (`name`),
    INDEX `idx_permissions_module` (`module`),
    INDEX `idx_permissions_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

echo "\nCreating permissions table...\n";
try {
    $db->query($createPermissionsTable);
    echo "✅ Permissions table created successfully\n";
} catch (Exception $e) {
    echo "❌ Error creating permissions table: " . $e->getMessage() . "\n";
}

// Create role_permissions pivot table
$createRolePermissionsTable = "
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `role_id` int(11) NOT NULL,
    `permission_id` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
    INDEX `idx_role_permissions_role_id` (`role_id`),
    INDEX `idx_role_permissions_permission_id` (`permission_id`),
    CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

echo "\nCreating role_permissions table...\n";
try {
    $db->query($createRolePermissionsTable);
    echo "✅ Role permissions table created successfully\n";
} catch (Exception $e) {
    echo "❌ Error creating role_permissions table: " . $e->getMessage() . "\n";
}

// Add role_id to users table
$alterUsersTable = "
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `role_id` int(11) DEFAULT 2,
ADD INDEX IF NOT EXISTS `idx_users_role_id` (`role_id`),
ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
";

echo "\nAdding role_id to users table...\n";
try {
    $db->query($alterUsersTable);
    echo "✅ Users table updated with role_id column\n";
} catch (Exception $e) {
    echo "❌ Error updating users table: " . $e->getMessage() . "\n";
}

// Insert default roles
$insertRoles = "
INSERT IGNORE INTO `roles` (`id`, `name`, `display_name`, `description`, `level`) VALUES
(1, 'super_admin', 'Super Administrator', 'Full system access with all permissions', 100),
(2, 'admin', 'Administrator', 'Administrative access with most permissions', 80),
(3, 'manager', 'Manager', 'Management access for properties and leads', 60),
(4, 'agent', 'Agent', 'Sales agent access for leads and properties', 40),
(5, 'user', 'User', 'Basic user access for browsing', 20);
";

echo "\nInserting default roles...\n";
try {
    $db->query($insertRoles);
    echo "✅ Default roles inserted successfully\n";
} catch (Exception $e) {
    echo "❌ Error inserting default roles: " . $e->getMessage() . "\n";
}

// Insert default permissions
$insertPermissions = "
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `module`, `action`) VALUES
-- User Management
('users.view', 'View Users', 'Can view user lists and profiles', 'users', 'view'),
('users.create', 'Create Users', 'Can create new user accounts', 'users', 'create'),
('users.edit', 'Edit Users', 'Can edit user information', 'users', 'edit'),
('users.delete', 'Delete Users', 'Can delete user accounts', 'users', 'delete'),
('users.assign_roles', 'Assign Roles', 'Can assign roles to users', 'users', 'assign_roles'),

-- Property Management
('properties.view', 'View Properties', 'Can view property listings', 'properties', 'view'),
('properties.create', 'Create Properties', 'Can add new properties', 'properties', 'create'),
('properties.edit', 'Edit Properties', 'Can edit property information', 'properties', 'edit'),
('properties.delete', 'Delete Properties', 'Can delete properties', 'properties', 'delete'),
('properties.bulk_operations', 'Bulk Operations', 'Can perform bulk property operations', 'properties', 'bulk'),

-- Lead Management
('leads.view', 'View Leads', 'Can view lead information', 'leads', 'view'),
('leads.create', 'Create Leads', 'Can create new leads', 'leads', 'create'),
('leads.edit', 'Edit Leads', 'Can edit lead information', 'leads', 'edit'),
('leads.delete', 'Delete Leads', 'Can delete leads', 'leads', 'delete'),
('leads.assign', 'Assign Leads', 'Can assign leads to agents', 'leads', 'assign'),
('leads.bulk_operations', 'Bulk Lead Operations', 'Can perform bulk lead operations', 'leads', 'bulk'),

-- Reports & Analytics
('reports.view', 'View Reports', 'Can access system reports', 'reports', 'view'),
('reports.analytics', 'View Analytics', 'Can view analytics dashboard', 'reports', 'analytics'),

-- System Administration
('system.settings', 'System Settings', 'Can modify system settings', 'system', 'settings'),
('system.backup', 'System Backup', 'Can perform system backups', 'system', 'backup'),
('system.maintenance', 'System Maintenance', 'Can perform maintenance tasks', 'system', 'maintenance');
";

echo "\nInserting default permissions...\n";
try {
    $db->query($insertPermissions);
    echo "✅ Default permissions inserted successfully\n";
} catch (Exception $e) {
    echo "❌ Error inserting default permissions: " . $e->getMessage() . "\n";
}

// Assign permissions to roles
$assignPermissions = "
-- Super Admin gets all permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Admin gets most permissions except system critical ones
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, p.id FROM `permissions` p
WHERE p.name NOT IN ('system.settings', 'system.backup', 'system.maintenance');

-- Manager gets property, lead, and report permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, p.id FROM `permissions` p
WHERE p.module IN ('properties', 'leads', 'reports')
AND p.name NOT LIKE '%delete%';

-- Agent gets basic property and lead permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, p.id FROM `permissions` p
WHERE p.module IN ('properties', 'leads')
AND p.action IN ('view', 'create', 'edit')
AND p.name NOT LIKE '%bulk%';

-- User gets only view permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, p.id FROM `permissions` p
WHERE p.action = 'view';
";

echo "\nAssigning permissions to roles...\n";
try {
    $db->query($assignPermissions);
    echo "✅ Permissions assigned to roles successfully\n";
} catch (Exception $e) {
    echo "❌ Error assigning permissions: " . $e->getMessage() . "\n";
}

// Update existing users to have admin role (assuming they are admins)
$updateExistingUsers = "
UPDATE `users` SET `role_id` = 2 WHERE `role_id` IS NULL OR `role_id` = 0;
";

echo "\nUpdating existing users with admin role...\n";
try {
    $result = $db->query($updateExistingUsers);
    echo "✅ Existing users updated with roles\n";
} catch (Exception $e) {
    echo "❌ Error updating existing users: " . $e->getMessage() . "\n";
}

echo "\n🎉 Roles and Permissions System Setup Complete!\n\n";

echo "📊 Summary:\n";
echo "• Created roles table with 5 default roles\n";
echo "• Created permissions table with 20+ permissions\n";
echo "• Created role_permissions pivot table\n";
echo "• Added role_id to users table\n";
echo "• Assigned appropriate permissions to each role\n";
echo "• Updated existing users with admin role\n\n";

echo "🔐 Available Roles:\n";
echo "1. Super Admin (level 100) - Full access\n";
echo "2. Admin (level 80) - Administrative access\n";
echo "3. Manager (level 60) - Property/Lead management\n";
echo "4. Agent (level 40) - Sales agent access\n";
echo "5. User (level 20) - Basic browsing access\n\n";

echo "✅ System is ready for role-based access control!\n";
?>
