<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';
$con = getMysqliConnection();
if (!$con) { http_response_code(500); echo 'Database connection failed.'; exit; }
if (!isset($_SESSION['aid'])) { header('Location: login.php'); exit; }
$associate_id = $_SESSION['aid'];
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="plot_sales.csv"');
$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF");
fputcsv($out, ['Plot ID','Amount','Date','Status']);
$from = !empty($_GET['from']) ? $_GET['from'] : date('Y-m-01');
$to = !empty($_GET['to']) ? $_GET['to'] : date('Y-m-d');
$res = $con->query("SELECT id, amount, created_at, status FROM property WHERE associate_id = '$associate_id' AND created_at >= '$from' AND created_at <= '$to' ORDER BY created_at DESC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['id'], $row['amount'], $row['created_at'], ucfirst($row['status'])]);
    }
}
fclose($out);
exit;
