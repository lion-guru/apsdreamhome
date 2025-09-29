<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['aid'])) { header('Location: login.php'); exit; }
$associate_id = $_SESSION['aid'];
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="my_payouts.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Date','Amount','Percent','Period','Status']);
$stmt = $con->prepare("SELECT payout_amount, payout_percent, period, status, generated_on FROM payouts WHERE associate_id=? ORDER BY generated_on DESC");
$stmt->bind_param('i', $associate_id);
$stmt->execute();
$stmt->bind_result($amt, $percent, $period, $status, $date);
while($stmt->fetch()) {
    fputcsv($out, [$date, $amt, $percent, $period, $status]);
}
$stmt->close();
fclose($out);
exit;
