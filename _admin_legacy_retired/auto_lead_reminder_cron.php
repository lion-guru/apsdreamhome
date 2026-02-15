<?php
require_once __DIR__ . '/includes/config/config.php';
global $con;
// auto_lead_reminder_cron.php: Send automatic follow-up reminders for leads not contacted within 3 days
$conn = $con;

// Fetch leads that are 'New' or 'Qualified' and created more than 3 days ago
$sql = "SELECT * FROM leads WHERE (status = 'New' OR status = 'Qualified') AND created_at < (NOW() - INTERVAL 3 DAY)";
$result = $conn->query($sql);

while ($lead = $result && $result->fetch_assoc()) {
    // Send reminder (for demo, just log to reminders table)
    $stmt = $conn->prepare("INSERT INTO reminders (lead_id, reminder_type, message, created_at) VALUES (?, ?, ?, NOW())");
    $type = 'followup';
    $msg = 'Automatic follow-up reminder: Lead ' . $lead['name'] . ' needs attention.';
    $stmt->bind_param('iss', $lead['lead_id'], $type, $msg);
    $stmt->execute();
}
// Optional: Email/SMS logic can be added here
?>
