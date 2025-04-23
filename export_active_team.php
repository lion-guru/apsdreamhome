<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';
$con = getDbConnection();
if (!$con) { http_response_code(500); echo 'Database connection failed.'; exit; }
if (!isset($_SESSION['aid'])) { header('Location: login.php'); exit; }
$associate_id = $_SESSION['aid'];
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="active_team_percent.csv"');
$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF");
fputcsv($out, ['Total Directs','Active Directs','Active Percentage']);
$total = 0; $active = 0;
$res = $con->query("SELECT COUNT(*) as cnt FROM associates WHERE parent_id = '$associate_id'");
if ($res) $total = (int)$res->fetch_assoc()['cnt'];
$res = $con->query("SELECT COUNT(*) as cnt FROM associates WHERE parent_id = '$associate_id' AND status = 'active'");
if ($res) $active = (int)$res->fetch_assoc()['cnt'];
$active_pct = ($total > 0) ? round(($active / $total) * 100, 1) : 0;
fputcsv($out, [$total, $active, $active_pct.'%']);
fclose($out);
exit;
