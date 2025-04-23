<?php
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include necessary files
include("config.php");
include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/common-functions.php');
// Check if user is logged in
if (!isset($_SESSION['aid']) && !isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}
// Set page specific variables
$page_title = "Dashboard - APS Dream Homes";
$meta_description = "Manage your APS Dream Homes account, view properties, and track your activities.";
// Additional CSS for this page
$additional_css = '<style>
    .dashboard-container { padding: 50px 0; }
    .dashboard-card { background-color: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); margin-bottom: 30px; }
    .sidebar { background-color: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05); }
    .sidebar .nav-link { color: #333; padding: 12px 20px; border-bottom: 1px solid #f1f1f1; transition: all 0.3s ease; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: var(--primary-color); color: #fff; }
    .sidebar .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
    .stats-card { background-color: var(--primary-color); color: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; }
    .stats-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15); }
    .stats-card h3 { font-size: 1.5rem; margin-bottom: 10px; }
    .stats-card h2 { font-size: 2.5rem; margin-bottom: 0; }
    .recent-activity { margin-top: 30px; }
    .activity-item { padding: 15px 0; border-bottom: 1px solid #eee; }
    .activity-item:last-child { border-bottom: none; }
    .activity-icon { width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-color); color: #fff; display: flex; align-items: center; justify-content: center; margin-right: 15px; }
    .activity-details { flex: 1; }
    .activity-title { font-weight: 600; margin-bottom: 5px; }
    .activity-time { font-size: 0.85rem; color: #888; }
    .property-card { border: 1px solid #eee; border-radius: 10px; overflow: hidden; margin-bottom: 20px; transition: all 0.3s ease; }
    .property-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); }
    .property-image { height: 200px; overflow: hidden; }
    .property-image img { width: 100%; height: 100%; object-fit: cover; }
    .property-details { padding: 15px; }
</style>';
// ...rest of the original file content from backup_duplicates/updated-dash.php...