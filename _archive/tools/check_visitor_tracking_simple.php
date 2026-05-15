<?php
// Check visitor tracking system - simple version
$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check visitor_sessions
    $stmt = $db->query("SHOW TABLES LIKE 'visitor_sessions'");
    if ($stmt->fetch()) {
        $count = $db->query("SELECT COUNT(*) FROM visitor_sessions")->fetchColumn();
        echo "Visitor sessions: $count records\n";
    } else {
        echo "Visitor sessions table does not exist\n";
    }

    // Check visitor_page_views
    $stmt = $db->query("SHOW TABLES LIKE 'visitor_page_views'");
    if ($stmt->fetch()) {
        $count = $db->query("SELECT COUNT(*) FROM visitor_page_views")->fetchColumn();
        echo "Page views: $count records\n";
    } else {
        echo "Page views table does not exist\n";
    }

    // Check incomplete_registrations
    $stmt = $db->query("SHOW TABLES LIKE 'incomplete_registrations'");
    if ($stmt->fetch()) {
        $count = $db->query("SELECT COUNT(*) FROM incomplete_registrations")->fetchColumn();
        echo "Incomplete registrations: $count records\n";
    } else {
        echo "Incomplete registrations table does not exist\n";
    }

    // Check visitor_leads
    $stmt = $db->query("SHOW TABLES LIKE 'visitor_leads'");
    if ($stmt->fetch()) {
        $count = $db->query("SELECT COUNT(*) FROM visitor_leads")->fetchColumn();
        echo "Visitor leads: $count records\n";
    } else {
        echo "Visitor leads table does not exist\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
