<?php
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();
require_once __DIR__ . '/vendor/fpdf/fpdf.php';

$user = $_SESSION['auser'];
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

$params = [$user];
$query = "SELECT * FROM upload_audit_log WHERE uploader = ?";

if ($filter_date) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $filter_date;
}

$query .= " ORDER BY created_at DESC LIMIT 100";
$logs = $db->fetchAll($query, $params);

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

foreach ($logs as $row) {
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
