<?php
// Export all IPs and User Agents as CSV for further analysis
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit();
}
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$role_filter = isset($_GET['role']) && $_GET['role'] ? $_GET['role'] : null;
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";
$role_sql = $role_filter ? "AND role='".mysqli_real_escape_string($con, $role_filter)."'" : '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_ips_agents_export_'.date('Ymd_His').'.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['ip_address','user_agent','feedback_count']);
$res = mysqli_query($con, "SELECT ip_address, user_agent, COUNT(*) as feedback_count FROM ai_interactions WHERE $date_sql $role_sql GROUP BY ip_address, user_agent ORDER BY feedback_count DESC");
while($row = mysqli_fetch_assoc($res)) {
    fputcsv($out, $row);
}
fclose($out);
exit;
