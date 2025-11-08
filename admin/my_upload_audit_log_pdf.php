<?php
session_start();
if (!isset($_SESSION['auser'])) { http_response_code(403); exit('Access denied.'); }
require_once __DIR__ . '/vendor/fpdf/fpdf.php';
include 'config.php';

$user = $_SESSION['auser'];
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$where = "WHERE uploader = '" . $user. "'";
if ($filter_date) {
    $where .= " AND DATE(created_at) = '" . $filter_date. "'";
}
$sql = "SELECT * FROM upload_audit_log $where ORDER BY created_at DESC LIMIT 100";
$logs = $conn->query($sql);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'My Upload Audit Report',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Generated: '.date('Y-m-d H:i:s'),0,1,'R');
$pdf->Ln(2);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(10,8,'ID',1);
$pdf->Cell(30,8,'Event',1);
$pdf->Cell(30,8,'Entity Table',1);
$pdf->Cell(20,8,'Entity ID',1);
$pdf->Cell(22,8,'Slack',1);
$pdf->Cell(22,8,'Telegram',1);
$pdf->Cell(35,8,'Created',1);
$pdf->Cell(20,8,'Status',1);
$pdf->Ln();
$pdf->SetFont('Arial','',10);
while($row = $logs->fetch_assoc()) {
    $pdf->Cell(10,8,$row['id'],1);
    $pdf->Cell(30,8,substr($row['event_type'],0,12),1);
    $pdf->Cell(30,8,substr($row['entity_table'],0,12),1);
    $pdf->Cell(20,8,substr($row['entity_id'],0,10),1);
    $pdf->Cell(22,8,substr($row['slack_status'],0,10),1);
    $pdf->Cell(22,8,substr($row['telegram_status'],0,10),1);
    $pdf->Cell(35,8,substr($row['created_at'],0,16),1);
    $pdf->Cell(20,8,substr($row['status'],0,10),1);
    $pdf->Ln();
}
$pdf->Ln(4);
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0,7,"This report shows your upload and notification activity. For details, see the Dream Home Admin system.");
$pdf->Output('D', 'my_upload_audit_log.pdf');
exit;
?>
