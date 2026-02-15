<?php
session_start();
if (!isset($_SESSION['auser']) || $_SESSION['auser'] !== 'superadmin') { http_response_code(403); exit('Access denied.'); }
require_once __DIR__ . '/vendor/fpdf/fpdf.php';
require_once __DIR__ . '/config.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE conditions using prepared statements
$where_conditions = [];
$params = [];
$types = "";

if ($filter && in_array($filter, ['admin_user','action','ip_address']) && !empty($search)) {
    $where_conditions[] = "$filter LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

$where_clause = empty($where_conditions) ? "" : "WHERE " . implode(" AND ", $where_conditions);

// Fetch logs using prepared statement
$query = "SELECT * FROM audit_access_log $where_clause ORDER BY accessed_at DESC LIMIT 200";
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $logs = $stmt->get_result();
} else {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $logs = $stmt->get_result();
}
$stmt->close();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Audit Access Compliance Report',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Generated: '.date('Y-m-d H:i:s'),0,1,'R');
$pdf->Ln(2);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(10,8,'ID',1);
$pdf->Cell(35,8,'Admin User',1);
$pdf->Cell(25,8,'Action',1);
$pdf->Cell(60,8,'IP Address',1);
$pdf->Cell(60,8,'Date',1);
$pdf->Ln();
$pdf->SetFont('Arial','',10);
while($row = $logs->fetch_assoc()) {
    $pdf->Cell(10,8,$row['id'],1);
    $pdf->Cell(35,8,substr($row['admin_user'],0,16),1);
    $pdf->Cell(25,8,substr($row['action'],0,12),1);
    $pdf->Cell(60,8,substr($row['ip_address'],0,25),1);
    $pdf->Cell(60,8,substr($row['accessed_at'],0,19),1);
    $pdf->Ln();
}
$pdf->Ln(4);
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0,7,"This report is generated for compliance review. All access to the audit log is tracked and monitored. For details, see the Dream Home Admin system.");
$pdf->Output('D', 'audit_access_compliance_report.pdf');
exit;
?>
