<?php
// auto_lead_conversion_cron.php: Automatically convert qualified leads to 'Converted' after 10 days if not already converted
require_once __DIR__ . '/../includes/db_config.php';
$conn = getDbConnection();

// Find qualified leads older than 10 days, not yet converted
$sql = "SELECT * FROM leads WHERE status = 'Qualified' AND created_at < (NOW() - INTERVAL 10 DAY)";
$result = $conn->query($sql);

while ($lead = $result && $result->fetch_assoc()) {
    $stmt = $conn->prepare("UPDATE leads SET status = 'Converted', notes = CONCAT(IFNULL(notes, ''), '\n[Auto] Converted after 10 days') WHERE lead_id = ?");
    $stmt->bind_param('i', $lead['lead_id']);
    $stmt->execute();
}
?>
