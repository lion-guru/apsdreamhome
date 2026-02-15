<?php
require_once '../core/init.php';

// RBAC Protection - Only Super Admin, Admin, and Manager can generate receipts
$currentRole = $_SESSION['admin_role'] ?? '';
$allowedRoles = ['superadmin', 'admin', 'manager'];
if (!in_array($currentRole, $allowedRoles)) {
    header("Location: /admin/dashboard?error=unauthorized");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die(h($mlSupport->translate('Invalid installment ID')));
}

$installmentId = intval($_GET['id']);

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
$data = \App\Core\App::database()->fetchOne($query, [$installmentId]);

if (!$data || $data['payment_status'] !== 'paid') {
    die(h($mlSupport->translate('Invalid installment or payment not found')));
}

require_once '../../vendor/autoload.php'; // For TCPDF
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

use TCPDF;

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
$pdf->Cell(0, 5, h($mlSupport->translate('Address: Your Company Address Here')), 0, 1, 'R');
$pdf->Cell(0, 5, h($mlSupport->translate('Phone: Your Company Phone')), 0, 1, 'R');
$pdf->Cell(0, 5, h($mlSupport->translate('Email: your@email.com')), 0, 1, 'R');

// Receipt Title
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, h($mlSupport->translate('EMI Payment Receipt')), 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, h($mlSupport->translate('Receipt No')) . ': ' . h($data['transaction_id']), 0, 1, 'C');

// Customer Details
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, h($mlSupport->translate('Customer Details')), 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, h($mlSupport->translate('Name')) . ':', 0);
$pdf->Cell(0, 5, h($data['customer_name']), 0, 1);
$pdf->Cell(50, 5, h($mlSupport->translate('Phone')) . ':', 0);
$pdf->Cell(0, 5, h($data['customer_phone']), 0, 1);
$pdf->Cell(50, 5, h($mlSupport->translate('Email')) . ':', 0);
$pdf->Cell(0, 5, h($data['customer_email']), 0, 1);

// Property Details
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, h($mlSupport->translate('Property Details')), 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, h($mlSupport->translate('Property')) . ':', 0);
$pdf->Cell(0, 5, h($data['property_title']), 0, 1);
$pdf->Cell(50, 5, h($mlSupport->translate('Address')) . ':', 0);
$pdf->Cell(0, 5, h($data['property_address']), 0, 1);

// Payment Details
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, h($mlSupport->translate('Payment Details')), 0, 1);
$pdf->SetFont('helvetica', '', 10);

// Create a table for payment details
$pdf->Cell(60, 7, h($mlSupport->translate('Description')), 1);
$pdf->Cell(30, 7, h($mlSupport->translate('Due Date')), 1);
$pdf->Cell(30, 7, h($mlSupport->translate('Paid Date')), 1);
$pdf->Cell(30, 7, h($mlSupport->translate('Amount')), 1);
$pdf->Cell(30, 7, h($mlSupport->translate('Late Fee')), 1, 1);

$pdf->Cell(60, 7, h($mlSupport->translate('EMI Installment #')) . h($data['installment_number']), 1);
$pdf->Cell(30, 7, date('d/m/Y', strtotime($data['due_date'])), 1);
$pdf->Cell(30, 7, date('d/m/Y', strtotime($data['payment_date'])), 1);
$pdf->Cell(30, 7, '₹' . h(number_format($data['amount'], 2)), 1);
$pdf->Cell(30, 7, '₹' . h(number_format($data['late_fee'], 2)), 1, 1);

// Total
$totalAmount = $data['amount'] + $data['late_fee'];
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(150, 7, h($mlSupport->translate('Total Amount Paid')) . ':', 1);
$pdf->Cell(30, 7, '₹' . h(number_format($totalAmount, 2)), 1, 1);

// Payment Method
$pdf->Ln(10);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, h($mlSupport->translate('Payment Method')) . ':', 0);
$pdf->Cell(0, 5, h($mlSupport->translate(ucfirst($data['payment_method']))), 0, 1);
$pdf->Cell(50, 5, h($mlSupport->translate('Transaction ID')) . ':', 0);
$pdf->Cell(0, 5, h($data['transaction_id']), 0, 1);

// EMI Plan Status
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, h($mlSupport->translate('EMI Plan Status')), 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 5, h($mlSupport->translate('Total Amount')) . ':', 0);
$pdf->Cell(0, 5, '₹' . h(number_format($data['total_amount'], 2)), 0, 1);
$pdf->Cell(50, 5, h($mlSupport->translate('EMI Amount')) . ':', 0);
$pdf->Cell(0, 5, '₹' . h(number_format($data['emi_amount'], 2)), 0, 1);
$pdf->Cell(50, 5, h($mlSupport->translate('Tenure')) . ':', 0);
$pdf->Cell(0, 5, h($data['tenure_months']) . ' ' . h($mlSupport->translate('months')), 0, 1);

// Get paid installments count
$query = "SELECT COUNT(*) as paid_count FROM emi_installments
          WHERE emi_plan_id = ? AND payment_status = 'paid'";
$paidRow = \App\Core\App::database()->fetchOne($query, [$data['emi_plan_id']]);
$paidCount = $paidRow['paid_count'] ?? 0;

$pdf->Cell(50, 5, h($mlSupport->translate('Installments Paid')) . ':', 0);
$pdf->Cell(0, 5, h($paidCount) . ' ' . h($mlSupport->translate('of')) . ' ' . h($data['tenure_months']), 0, 1);

// Footer text
$pdf->Ln(20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 5, h($mlSupport->translate('This is a computer generated receipt and does not require a signature.')), 0, 1, 'C');
$pdf->Cell(0, 5, h($mlSupport->translate('For any queries, please contact our support team.')), 0, 1, 'C');

// Output the PDF
$pdf->Output('EMI_Receipt_' . $data['transaction_id'] . '.pdf', 'I');
?>
