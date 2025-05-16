<?php
// auto_revenue_commission_cron.php: Calculate daily revenue and agent commissions from converted leads
require_once __DIR__ . '/../includes/db_config.php';
$conn = getDbConnection();

// Example: Assume each lead has amount and agent_id fields
$sql = "SELECT agent_id, SUM(amount) as revenue, COUNT(*) as deals FROM leads WHERE status = 'Converted' AND DATE(updated_at) = CURDATE() GROUP BY agent_id";
$result = $conn->query($sql);
while ($row = $result && $result->fetch_assoc()) {
    $commission = $row['revenue'] * 0.02; // 2% commission example
    $stmt = $conn->prepare("INSERT INTO revenue_commission_daily (stat_date, agent_id, revenue, deals, commission) VALUES (CURDATE(), ?, ?, ?, ?)");
    $stmt->bind_param('iidd', $row['agent_id'], $row['revenue'], $row['deals'], $commission);
    $stmt->execute();
}
?>
