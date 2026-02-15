<?php
/**
 * Super Admin Configuration File
 * Defines settings and permissions for the super admin panel
 */

// Super Admin Panel Settings
define('SUPER_ADMIN_ENABLED', true);
define('VISUAL_EDITOR_ENABLED', true);
define('MAX_FILE_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx');

// Content Management Settings
define('CONTENT_BACKUP_ENABLED', true);
define('MAX_CONTENT_VERSIONS', 10);
define('AUTO_BACKUP_INTERVAL', 24 * 60 * 60); // 24 hours in seconds

// User Role Permissions
$SUPER_ADMIN_PERMISSIONS = [
    'manage_content' => true,
    'manage_layout' => true,
    'manage_users' => true,
    'manage_settings' => true,
    'manage_media' => true,
    'manage_backups' => true,
    'manage_roles' => true,
    'view_analytics' => true
];

$ADMIN_PERMISSIONS = [
    'manage_content' => true,
    'manage_layout' => false,
    'manage_users' => false,
    'manage_settings' => false,
    'manage_media' => true,
    'manage_backups' => false,
    'manage_roles' => false,
    'view_analytics' => true
];

$EDITOR_PERMISSIONS = [
    'manage_content' => true,
    'manage_layout' => false,
    'manage_users' => false,
    'manage_settings' => false,
    'manage_media' => true,
    'manage_backups' => false,
    'manage_roles' => false,
    'view_analytics' => false
];

// Visual Editor Components
$VISUAL_EDITOR_COMPONENTS = [
    'text_editor' => [
        'enabled' => true,
        'toolbar' => 'full' // basic, standard, full
    ],
    'media_uploader' => [
        'enabled' => true,
        'max_file_size' => MAX_FILE_UPLOAD_SIZE,
        'allowed_types' => ALLOWED_FILE_TYPES
    ],
    'layout_builder' => [
        'enabled' => true,
        'templates' => ['default', 'homepage', 'contact', 'about']
    ],
    'component_library' => [
        'enabled' => true,
        'components' => ['header', 'footer', 'sidebar', 'slider', 'gallery']
    ]
];

// Backup Settings
$BACKUP_SETTINGS = [
    'auto_backup' => true,
    'backup_interval' => AUTO_BACKUP_INTERVAL,
    'max_backups' => 5,
    'include_media' => true,
    'backup_path' => dirname(__DIR__) . '/backups'
];

// Analytics Integration
$ANALYTICS_SETTINGS = [
    'enabled' => true,
    'tracking_id' => '',
    'track_admin_users' => false
];