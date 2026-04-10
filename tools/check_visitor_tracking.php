<?php
// Check visitor tracking system
require_once __DIR__ . '/../app/Core/Database/Database.php';

try {
    $db = App\Core\Database\Database::getInstance();

    // Check if visitor_sessions table exists and has data
    $result = $db->fetch("SHOW TABLES LIKE 'visitor_sessions'");
    if ($result) {
        $count = $db->fetch("SELECT COUNT(*) as count FROM visitor_sessions");
        echo "Visitor sessions table exists. Records: " . $count['count'] . "\n";
    } else {
        echo "Visitor sessions table does not exist.\n";
    }

    // Check if visitor_page_views table exists and has data
    $result = $db->fetch("SHOW TABLES LIKE 'visitor_page_views'");
    if ($result) {
        $count = $db->fetch("SELECT COUNT(*) as count FROM visitor_page_views");
        echo "Page views table exists. Records: " . $count['count'] . "\n";
    } else {
        echo "Page views table does not exist.\n";
    }

    // Check if incomplete_registrations table exists and has data
    $result = $db->fetch("SHOW TABLES LIKE 'incomplete_registrations'");
    if ($result) {
        $count = $db->fetch("SELECT COUNT(*) as count FROM incomplete_registrations");
        echo "Incomplete registrations table exists. Records: " . $count['count'] . "\n";
    } else {
        echo "Incomplete registrations table does not exist.\n";
    }

    // Check if visitor_leads table exists and has data
    $result = $db->fetch("SHOW TABLES LIKE 'visitor_leads'");
    if ($result) {
        $count = $db->fetch("SELECT COUNT(*) as count FROM visitor_leads");
        echo "Visitor leads table exists. Records: " . $count['count'] . "\n";
    } else {
        echo "Visitor leads table does not exist.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
