<?php
// Export drilldown results for a specific feedback type as CSV
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

$type = isset($_GET['type']) ? mysqli_real_escape_string($con, $_GET['type']) : null;
$where = $date_sql . ' ' . $role_sql;
if ($type) {
    $where .= " AND feedback='$type'";
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_feedbacktype_drilldown_export_'.date('Ymd_His').'.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['created_at','user_id','role','action','suggestion_text','feedback','notes','ip_address','user_agent']);
$res = mysqli_query($con, "SELECT created_at, user_id, role, action, suggestion_text, feedback, notes, ip_address, user_agent FROM ai_interactions WHERE $where ORDER BY created_at DESC LIMIT 100");
while($row = mysqli_fetch_assoc($res)) {
    fputcsv($out, $row);
}
fclose($out);
exit;
