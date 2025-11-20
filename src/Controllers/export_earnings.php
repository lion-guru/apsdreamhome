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
header('Content-Disposition: attachment; filename="earnings_history.csv"');
$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // UTF-8 BOM
fputcsv($out, ['Date','Type','Amount','Status']);
// Export payouts
$stmt = $con->prepare("SELECT created_at, payout_amount as amount, status FROM payouts WHERE associate_id=? ORDER BY created_at DESC");
$stmt->bind_param('i', $associate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['created_at'],
        'Payout',
        $row['amount'],
        ucfirst($row['status'])
    ]);
}
$stmt->close();
// Export plot sales
$stmt = $con->prepare("SELECT created_at, amount, status FROM property WHERE associate_id=? ORDER BY created_at DESC");
$stmt->bind_param('i', $associate_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($out, [
        $row['created_at'],
        'Plot Sale',
        $row['amount'],
        ucfirst($row['status'])
    ]);
}
$stmt->close();
fclose($out);
exit;
