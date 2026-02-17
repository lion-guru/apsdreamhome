<?php
// Enhanced Features for APS Dream Homes - Inspired by Local Competitors - Updated with Session Management
require_once __DIR__ . "/core/init.php";
// require_once __DIR__ . '/config/unified_config.php'; // Removed non-existent config

// Authentication and role check
if (!isAuthenticated()) {
    header('Location: login.php');
    exit();
}

// Set page title and metadata
$page_title = 'Enhanced Features - APS Dream Homes';
$page_description = 'Advanced features and innovations inspired by Gorakhpur real estate market leaders';
$page_keywords = 'APS Dream Homes, enhanced features, real estate innovations, Gorakhpur property';
