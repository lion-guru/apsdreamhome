<?php
// Exports all AI interaction logs as CSV for APS model training or review
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="ai_interactions_export_'.date('Ymd_His').'.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['id','user_id','role','action','suggestion_text','feedback','notes','created_at']);
$res = mysqli_query($con, "SELECT id,user_id,role,action,suggestion_text,feedback,notes,created_at FROM ai_interactions ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($res)) {
    fputcsv($out, $row);
}
fclose($out);
exit;
