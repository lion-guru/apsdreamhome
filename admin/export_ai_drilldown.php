<?php
// Export drilldown analytics as CSV
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php?msg='.urlencode('Access denied.')); exit();
}
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$role_filter = isset($_GET['role']) && $_GET['role'] ? $_GET['role'] : null;
$suggestion_filter = isset($_GET['suggestion']) && trim($_GET['suggestion']) ? trim($_GET['suggestion']) : '';
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";
$role_sql = $role_filter ? "AND role='".mysqli_real_escape_string($con, $role_filter)."'" : '';
$suggestion_sql = $suggestion_filter ? "AND suggestion_text LIKE '%".mysqli_real_escape_string($con, $suggestion_filter)."%'" : '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_drilldown_export_'.date('Ymd_His').'.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['id','user_id','role','action','suggestion_text','feedback','notes','created_at']);
$res = mysqli_query($con, "SELECT id,user_id,role,action,suggestion_text,feedback,notes,created_at FROM ai_interactions WHERE $date_sql $role_sql $suggestion_sql ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($res)) {
    fputcsv($out, $row);
}
fclose($out);
exit;
