<?php
// auto_cleanup_cron.php: Archive or delete leads older than 1 year to keep the database clean
require_once __DIR__ . '/../includes/db_config.php';
$conn = getDbConnection();

// Archive leads older than 1 year
$conn->query("UPDATE leads SET status = 'Archived' WHERE status != 'Archived' AND created_at < (NOW() - INTERVAL 1 YEAR)");

// Optional: Delete archived leads older than 2 years
$conn->query("DELETE FROM leads WHERE status = 'Archived' AND created_at < (NOW() - INTERVAL 2 YEAR)");
?>
