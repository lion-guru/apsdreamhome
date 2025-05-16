<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/auth_check.php';
require_once '../../vendor/autoload.php'; // For TCPDF

use TCPDF;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid installment ID');
}

$installmentId = intval($_GET['id']);
$conn = getDbConnection();

// Get installment details with related information
$query = "SELECT ei.*, ep.*, c.name as customer_name, c.phone as customer_phone, 
                 c.email as customer_email, p.title as property_title, 
                 p.address as property_address, py.transaction_id,
                 py.payment_method, py.description as payment_description
          FROM emi_installments ei
          JOIN emi_plans ep ON ei.emi_plan_id = ep.id
          JOIN customers c ON ep.customer_id = c.id
          JOIN properties p ON ep.property_id = p.id
          LEFT JOIN payments py ON ei.payment_id = py.id
          WHERE ei.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $installmentId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data || $data['payment_status'] !== 'paid') {
    die('Invalid installment or payment not found');
}

require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';
    
// Define PDF constants if not already defined
if (!defined('PDF_PAGE_ORIENTATION')) define('PDF_PAGE_ORIENTATION', 'P');
if (!defined('PDF_UNIT')) define('PDF_UNIT', 'mm');
if (!defined('PDF_PAGE_FORMAT')) define('PDF_PAGE_FORMAT', 'A4');
    
/** @var TCPDF $pdf */
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('APS Dream Home');
$pdf->SetAuthor('APS Dream Home');
$pdf->SetTitle('EMI Payment Receipt');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Company Logo and Details
$pdf->Image('../../assets/img/logo.png', 15, 15, 50);
$pdf->Cell(0, 5, 'APS Dream Home', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Address: Your Company Address Here', 0, 1, 'R');
$pdf->Cell(0, 5, 'Phone: Your Company Phone', 0, 1, 'R');
$pdf->Cell(0, 5, 'Email: your@email.com', 0, 1, 'R');

// Receipt Title
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'EMI Payment Receipt', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Receipt No: ' . $data['transaction_id'], 0, 1, 'C');

// Customer Details
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Customer Details', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, 'Name:', 0);
$pdf->Cell(0, 5, $data['customer_name'], 0, 1);
$pdf->Cell(50, 5, 'Phone:', 0);
$pdf->Cell(0, 5, $data['customer_phone'], 0, 1);
$pdf->Cell(50, 5, 'Email:', 0);
$pdf->Cell(0, 5, $data['customer_email'], 0, 1);

// Property Details
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Property Details', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, 'Property:', 0);
$pdf->Cell(0, 5, $data['property_title'], 0, 1);
$pdf->Cell(50, 5, 'Address:', 0);
$pdf->Cell(0, 5, $data['property_address'], 0, 1);

// Payment Details
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Payment Details', 0, 1);
$pdf->SetFont('helvetica', '', 10);

// Create a table for payment details
$pdf->Cell(60, 7, 'Description', 1);
$pdf->Cell(30, 7, 'Due Date', 1);
$pdf->Cell(30, 7, 'Paid Date', 1);
$pdf->Cell(30, 7, 'Amount', 1);
$pdf->Cell(30, 7, 'Late Fee', 1, 1);

$pdf->Cell(60, 7, 'EMI Installment #' . $data['installment_number'], 1);
$pdf->Cell(30, 7, date('d/m/Y', strtotime($data['due_date'])), 1);
$pdf->Cell(30, 7, date('d/m/Y', strtotime($data['payment_date'])), 1);
$pdf->Cell(30, 7, '₹' . number_format($data['amount'], 2), 1);
$pdf->Cell(30, 7, '₹' . number_format($data['late_fee'], 2), 1, 1);

// Total
$totalAmount = $data['amount'] + $data['late_fee'];
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(150, 7, 'Total Amount Paid:', 1);
$pdf->Cell(30, 7, '₹' . number_format($totalAmount, 2), 1, 1);

// Payment Method
$pdf->Ln(10);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, 'Payment Method:', 0);
$pdf->Cell(0, 5, ucfirst($data['payment_method']), 0, 1);
$pdf->Cell(50, 5, 'Transaction ID:', 0);
$pdf->Cell(0, 5, $data['transaction_id'], 0, 1);

// EMI Plan Status
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'EMI Plan Status', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, 'Total Amount:', 0);
$pdf->Cell(0, 5, '₹' . number_format($data['total_amount'], 2), 0, 1);
$pdf->Cell(50, 5, 'EMI Amount:', 0);
$pdf->Cell(0, 5, '₹' . number_format($data['emi_amount'], 2), 0, 1);
$pdf->Cell(50, 5, 'Tenure:', 0);
$pdf->Cell(0, 5, $data['tenure_months'] . ' months', 0, 1);

// Get paid installments count
$query = "SELECT COUNT(*) as paid_count FROM emi_installments 
          WHERE emi_plan_id = ? AND payment_status = 'paid'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $data['emi_plan_id']);
$stmt->execute();
$paidCount = $stmt->get_result()->fetch_assoc()['paid_count'];

$pdf->Cell(50, 5, 'Installments Paid:', 0);
$pdf->Cell(0, 5, $paidCount . ' of ' . $data['tenure_months'], 0, 1);

// Footer text
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, 'This is a computer generated receipt and does not require a signature.', 0, 1, 'C');
$pdf->Cell(0, 5, 'For any queries, please contact our support team.', 0, 1, 'C');

// Output the PDF
$pdf->Output('EMI_Receipt_' . $data['transaction_id'] . '.pdf', 'I');
?>
