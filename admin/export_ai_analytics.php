<?php
// Export filtered AI analytics data as CSV
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit();
}
require_once(__DIR__ . '/../includes/classes/Database.php');
$db = new Database();
$con = $db->getConnection();

// Get filters from GET params
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$role_filter = isset($_GET['role']) && $_GET['role'] ? $_GET['role'] : null;
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";
$role_sql = $role_filter ? "AND role='".mysqli_real_escape_string($con, $role_filter)."'" : '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_analytics_export_'.date('Ymd_His').'.csv"');
$out = fopen('php://output', 'w');
// Header
fputcsv($out, ['id','user_id','role','action','suggestion_text','feedback','notes','created_at']);
// Data
$res = mysqli_query($con, "SELECT id,user_id,role,action,suggestion_text,feedback,notes,created_at FROM ai_interactions WHERE $date_sql $role_sql ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($res)) {
    fputcsv($out, $row);
}
fclose($out);
exit;
