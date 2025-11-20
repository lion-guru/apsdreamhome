<?php
session_start();
require_once __DIR__ . '/includes/db_config.php';
$con = getMysqliConnection();
if (!$con) {
    http_response_code(500);
    echo 'Database connection failed.';
    exit;
}
if (!isset($_SESSION['aid'])) {
    header('Location: login.php');
    exit;
}
$associate_id = $_SESSION['aid'];
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="my_team.csv"');
$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM
fputcsv($out, ['Name','Post','Business Volume','Join Date','Phone','Level']);
function exportDownlineCSV($con, $parent_id, $level = 1) {
    $stmt = $con->prepare("SELECT id, name, post, business_volume, join_date, phone FROM associates WHERE parent_id=?");
    $stmt->bind_param('i', $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        fputcsv($GLOBALS['out'], [
            $row['name'],
            $row['post'],
            $row['business_volume'],
            $row['join_date'],
            $row['phone'],
            $level
        ]);
        exportDownlineCSV($con, $row['id'], $level+1);
    }
    $stmt->close();
}
exportDownlineCSV($con, $associate_id);
fclose($out);
exit;
