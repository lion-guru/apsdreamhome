<?php
/**
 * Admin Configuration File
 * This file provides database connection and other configurations for the admin panel
 */

// Include main configuration files
// Include centralized database connection
require_once(__DIR__ . '/../includes/db_connection.php');

// Get database connection
try {
    $con = getDbConnection();
} catch (Exception $e) {
    handleDatabaseError($e);
}

// Set admin-specific constants
define('ADMIN_BASE_URL', '/apsdreamhome/admin');
define('ADMIN_ASSETS_URL', ADMIN_BASE_URL . '/assets');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add configurable SIEM/log forwarding endpoint variable
$SIEM_ENDPOINT = getenv('SIEM_ENDPOINT') ?: '';

// Add configurable incident response webhook variable
$INCIDENT_WEBHOOK_URL = getenv('INCIDENT_WEBHOOK_URL') ?: '';

// Add log retention config
$LOG_RETENTION_DAYS = getenv('LOG_RETENTION_DAYS') ? intval(getenv('LOG_RETENTION_DAYS')) : 180;
$LOG_ARCHIVE_PASSWORD = getenv('LOG_ARCHIVE_PASSWORD') ?: 'DreamHomeSecure!';
$LOG_ARCHIVE_DIR = __DIR__ . '/../log_archives';

// Add S3/cloud storage configuration variables
$S3_BUCKET = getenv('S3_BUCKET') ?: '';
$S3_KEY = getenv('S3_KEY') ?: '';
$S3_SECRET = getenv('S3_SECRET') ?: '';
$S3_REGION = getenv('S3_REGION') ?: 'us-east-1';

// Admin authentication check
function checkAdminAuth() {
    if (!isset($_SESSION['auser'])) {
        header('location:index.php');
        exit();
    }
}
