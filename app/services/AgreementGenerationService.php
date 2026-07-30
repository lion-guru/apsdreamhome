<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;
use Exception;

class AgreementGenerationService
{
    use ServiceTenantTrait;

    private $db;
    private $company;
    private $assetsPath;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->company = $this->loadCompanySettings();
        $this->assetsPath = defined('APS_ROOT') ? APS_ROOT . '/assets' : (defined('APP_ROOT') ? APP_ROOT . '/assets' : __DIR__ . '/../../assets');
    }

    private function loadCompanySettings()
    {
        try {
            $company = $this->db->fetch("SELECT * FROM company_settings LIMIT 1");
            if (!$company) {
                $company = [
                    'company_name' => 'APS Dream Home',
                    'phone' => '+91 92771 21112',
                    'email' => 'info@apsdreamhome.com',
                    'address' => 'Gorakhpur, Uttar Pradesh, India',
                    'description' => ''
                ];
            }
            $settings = $this->db->fetchAll("SELECT `key`, `value` FROM settings");
            foreach ($settings as $s) {
                $company[$s['key']] = $s['value'];
            }
            return $company;
        } catch (Exception $e) {
            return [
                'company_name' => 'APS Dream Home',
                'phone' => '+91 92771 21112',
                'email' => 'info@apsdreamhome.com',
                'address' => 'Gorakhpur, Uttar Pradesh, India'
            ];
        }
    }

    public function getBookingData($bookingId)
    {
        $tid = $this->tenantId();
        $sql = "
            SELECT b.*, 
                   u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
                   p.plot_number, p.block, p.sector, p.area_sqft, p.total_price as plot_price, 
                   p.price_per_sqft, p.width_ft, p.length_ft, p.dimension_label, p.facing,
                   p.status as plot_status, p.booking_amount, p.total_paid, p.payment_status as plot_payment_status,
                   c.name as colony_name, c.description as colony_description
            FROM bookings b
            LEFT JOIN users u ON b.customer_id = u.id
            LEFT JOIN plots p ON b.plot_id = p.id
            LEFT JOIN colonies c ON b.colony_id = c.id
            WHERE b.id = ?" . ($tid > 1 ? " AND b.tenant_id = ?" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->execute($tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            throw new Exception("Booking not found with ID: $bookingId");
        }

        $payments = $this->db->prepare("SELECT * FROM booking_payments WHERE booking_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $payments->execute($tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        $data['payments'] = $payments->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    public function generateDocumentCode($type, $colonyName)
    {
        $tid = $this->tenantId();
        $prefix = 'APS';
        $colonyCode = '';
        $words = preg_split('/[\s-]+/', $colonyName);
        foreach ($words as $w) {
            if (!empty($w)) $colonyCode .= strtoupper($w[0]);
        }
        if (strlen($colonyCode) > 5) $colonyCode = substr($colonyCode, 0, 5);

        $year = date('Y');
        $typePrefix = '';
        switch ($type) {
            case 'allotment': $typePrefix = 'AL'; break;
            case 'sale_agreement': $typePrefix = 'SA'; break;
            case 'payment_plan': $typePrefix = 'PP'; break;
            default: $typePrefix = 'AG';
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM generated_documents WHERE document_code LIKE ? AND YEAR(generated_at) = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""));
        $stmt->execute($tid > 1 ? ["$prefix/$colonyCode/$typePrefix/$year/%", $year, $tid] : ["$prefix/$colonyCode/$typePrefix/$year/%", $year]);
        $count = intval($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0) + 1;

        return "$prefix/$colonyCode/$typePrefix/$year/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function generateAllotmentLetter($bookingId)
    {
        $data = $this->getBookingData($bookingId);
        $docCode = $this->generateDocumentCode('allotment', $data['colony_name'] ?? 'COLONY');
        $title = 'Allotment Letter - ' . ($data['plot_number'] ?? 'N/A') . ' - ' . ($data['customer_name'] ?? 'Buyer');
        $filePath = $this->generatePdf($data, 'allotment', $docCode);
        return $this->saveDocumentRecord($data, 'allotment', $docCode, $title, $filePath);
    }

    public function generateSaleAgreement($bookingId)
    {
        $data = $this->getBookingData($bookingId);
        $docCode = $this->generateDocumentCode('sale_agreement', $data['colony_name'] ?? 'COLONY');
        $title = 'Sale Agreement - ' . ($data['plot_number'] ?? 'N/A') . ' - ' . ($data['customer_name'] ?? 'Buyer');
        $filePath = $this->generatePdf($data, 'sale_agreement', $docCode);
        return $this->saveDocumentRecord($data, 'sale_agreement', $docCode, $title, $filePath);
    }

    public function generatePaymentPlan($bookingId)
    {
        $data = $this->getBookingData($bookingId);
        $docCode = $this->generateDocumentCode('payment_plan', $data['colony_name'] ?? 'COLONY');
        $title = 'Payment Plan - ' . ($data['plot_number'] ?? 'N/A') . ' - ' . ($data['customer_name'] ?? 'Buyer');
        $filePath = $this->generatePdf($data, 'payment_plan', $docCode);
        return $this->saveDocumentRecord($data, 'payment_plan', $docCode, $title, $filePath);
    }

    private function generatePdf($data, $type, $docCode)
    {
        if (!class_exists('TCPDF')) {
            require_once $this->assetsPath . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('APS Dream Home');
        $pdf->SetAuthor($this->company['company_name'] ?? 'APS Dream Home');
        $pdf->SetTitle(ucwords(str_replace('_', ' ', $type)) . ' - ' . ($data['plot_number'] ?? 'N/A'));
        $pdf->SetSubject('Property Agreement');
        $pdf->SetKeywords('APS, Dream Home, Agreement, Plot, Sale');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 25);

        $pdf->AddPage();

        $logoPath = $this->assetsPath . '/images/logo/apslogo-wide.svg';
        if (file_exists($logoPath)) {
            $pdf->ImageSVG($logoPath, 15, 10, 60, 15);
        } else {
            $logoPath = $this->assetsPath . '/images/logo/apslogonew.jpg';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 15, 10, 50, 15);
            }
        }

        $companyName = $this->company['company_name'] ?? 'APS Dream Home';
        $companyAddress = $this->company['address'] ?? '';
        $companyPhone = $this->company['phone'] ?? '';
        $companyEmail = $this->company['email'] ?? '';

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY(15, 28);
        $pdf->Cell(0, 4, $companyName, 0, 1, 'L');
        $pdf->SetX(15);
        $pdf->Cell(0, 4, $companyAddress, 0, 1, 'L');
        $pdf->SetX(15);
        $pdf->Cell(0, 4, 'Phone: ' . $companyPhone . ' | Email: ' . $companyEmail, 0, 1, 'L');

        $pdf->SetDrawColor(200, 160, 30);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, 34, 195, 34);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, 35, 195, 35);

        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(180, 130, 20);
        $title = '';
        switch ($type) {
            case 'allotment': $title = 'ALLOTMENT LETTER'; break;
            case 'sale_agreement': $title = 'SALE AGREEMENT'; break;
            case 'payment_plan': $title = 'PAYMENT PLAN AGREEMENT'; break;
        }
        $pdf->Cell(0, 10, $title, 0, 1, 'C');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 6, 'Document No: ' . $docCode, 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 6, 'Date: ' . date('d/m/Y'), 0, 1, 'R');
        $pdf->Ln(3);

        $this->drawSectionHeader($pdf, 'PARTY DETAILS');
        $pdf->Ln(1);

        $customerName = $data['customer_name'] ?? 'N/A';
        $customerAddress = $data['customer_address'] ?? 'N/A';
        $customerPhone = $data['customer_phone'] ?? 'N/A';
        $customerEmail = $data['customer_email'] ?? 'N/A';

        $pdf->SetFont('helvetica', '', 9);
        $col1X = 15;
        $col2X = 105;
        $rowH = 5;

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Buyer Name:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $customerName);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col2X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Company:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $companyName);

        $pdf->Ln($rowH);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Address:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $customerAddress);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col2X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Address:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $companyAddress);

        $pdf->Ln($rowH);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Phone:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $customerPhone);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col2X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Phone:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $companyPhone);

        $pdf->Ln($rowH);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Email:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $customerEmail);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col2X, $pdf->GetY());
        $pdf->Cell(25, $rowH, 'Email:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(60, $rowH, $companyEmail);

        $pdf->Ln(4);
        $this->drawSectionHeader($pdf, 'PROPERTY DETAILS');
        $pdf->Ln(1);

        $plotNumber = $data['plot_number'] ?? 'N/A';
        $block = $data['block'] ?? 'N/A';
        $sector = $data['sector'] ?? 'N/A';
        $colonyName = $data['colony_name'] ?? 'N/A';
        $areaSqft = $data['area_sqft'] ?? 0;
        $dimension = $data['dimension_label'] ?? '';
        if (empty($dimension) && ($data['width_ft'] ?? 0) > 0 && ($data['length_ft'] ?? 0) > 0) {
            $dimension = $data['width_ft'] . 'x' . $data['length_ft'];
        }
        $facing = $data['facing'] ?? 'N/A';
        $totalPrice = $data['plot_price'] ?? ($data['total_amount'] ?? 0);
        $negotiatedPrice = $data['negotiated_price'] ?? null;

        $pdf->SetFont('helvetica', '', 9);
        $props = [
            ['Plot No:', $plotNumber, 'Block:', $block],
            ['Sector:', $sector, 'Colony:', $colonyName],
            ['Area (sq.ft.):', number_format(floatval($areaSqft), 2), 'Dimension:', $dimension ?: 'N/A'],
            ['Facing:', $facing, '', ''],
        ];

        foreach ($props as $p) {
            $y = $pdf->GetY();
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetXY($col1X, $y);
            $pdf->Cell(28, $rowH, $p[0]);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(57, $rowH, $p[1]);
            if (!empty($p[2])) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetXY($col2X, $y);
                $pdf->Cell(28, $rowH, $p[2]);
                $pdf->SetFont('helvetica', '', 9);
                $pdf->Cell(57, $rowH, $p[3]);
            }
            $pdf->Ln($rowH);
        }

        $pdf->Ln(2);
        $this->drawSectionHeader($pdf, 'PAYMENT SUMMARY');
        $pdf->Ln(1);

        $bookingAmount = $data['booking_amount'] ?? 0;
        $totalPaid = $data['total_paid'] ?? 0;

        $pdf->SetFont('helvetica', '', 9);
        $y = $pdf->GetY();
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $y);
        $pdf->Cell(40, $rowH, 'Total Plot Price:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(50, $rowH, 'Rs. ' . number_format(floatval($totalPrice), 2));

        if ($negotiatedPrice && $negotiatedPrice > 0) {
            $pdf->Ln($rowH);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetXY($col1X, $pdf->GetY());
            $pdf->Cell(40, $rowH, 'Negotiated Price:');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(50, $rowH, 'Rs. ' . number_format(floatval($negotiatedPrice), 2));
        }

        $pdf->Ln($rowH);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $pdf->GetY());
        $pdf->Cell(40, $rowH, 'Booking Amount:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(50, $rowH, 'Rs. ' . number_format(floatval($bookingAmount), 2));

        $pdf->Ln($rowH);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetXY($col1X, $pdf->GetY());
        $pdf->Cell(40, $rowH, 'Total Paid:');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(50, $rowH, 'Rs. ' . number_format(floatval($totalPaid), 2));

        if (!empty($data['payments'])) {
            $pdf->Ln(4);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Payment History', 0, 1, 'L');

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(15, 5, '#', 1, 0, 'C', true);
            $pdf->Cell(40, 5, 'Date', 1, 0, 'C', true);
            $pdf->Cell(35, 5, 'Method', 1, 0, 'C', true);
            $pdf->Cell(50, 5, 'Transaction ID', 1, 0, 'C', true);
            $pdf->Cell(35, 5, 'Amount', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 8);
            $i = 1;
            foreach ($data['payments'] as $pmt) {
                $pdf->Cell(15, 5, $i++, 1, 0, 'C');
                $pdf->Cell(40, 5, date('d/m/Y', strtotime($pmt['payment_date'])), 1, 0, 'C');
                $pdf->Cell(35, 5, ucwords(str_replace('_', ' ', $pmt['payment_method'] ?? 'N/A')), 1, 0, 'C');
                $pdf->Cell(50, 5, $pmt['transaction_id'] ?? 'N/A', 1, 0, 'C');
                $pdf->Cell(35, 5, 'Rs. ' . number_format(floatval($pmt['payment_amount'] ?? 0), 2), 1, 1, 'R');
            }
        }

        if ($type === 'payment_plan') {
            $this->generatePaymentPlanTable($pdf, $totalPrice, $bookingAmount);
        }

        $pdf->Ln(4);
        $this->drawSectionHeader($pdf, 'TERMS AND CONDITIONS');
        $pdf->Ln(1);

        $terms = $this->getTermsConditions($type);
        $pdf->SetFont('helvetica', '', 8);
        foreach ($terms as $i => $term) {
            $pdf->SetX(18);
            $pdf->MultiCell(175, 4, ($i + 1) . '. ' . $term, 0, 'L');
            $pdf->Ln(1);
        }

        $pdf->Ln(4);

        if ($pdf->GetY() > 230) {
            $pdf->AddPage();
        }

        $this->drawSectionHeader($pdf, 'SIGNATURES');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 9);

        $sigY = $pdf->GetY();
        $pdf->SetXY(15, $sigY);
        $pdf->Cell(80, 5, '____________________________', 0, 1, 'C');
        $pdf->SetX(15);
        $pdf->Cell(80, 5, $customerName, 0, 1, 'C');
        $pdf->SetX(15);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(80, 5, 'BUYER', 0, 1, 'C');

        $pdf->SetXY(100, $sigY);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(80, 5, '____________________________', 0, 1, 'C');
        $pdf->SetX(100);
        $pdf->Cell(80, 5, 'For ' . $companyName, 0, 1, 'C');
        $pdf->SetX(100);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(80, 5, 'SELLER (Authorized Signatory)', 0, 1, 'C');

        $witnessY = $pdf->GetY() + 12;
        $pdf->SetXY(15, $witnessY);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(80, 5, '____________________________', 0, 1, 'C');
        $pdf->SetX(15);
        $pdf->Cell(80, 5, 'Witness 1', 0, 1, 'C');

        $pdf->SetXY(100, $witnessY);
        $pdf->Cell(80, 5, '____________________________', 0, 1, 'C');
        $pdf->SetX(100);
        $pdf->Cell(80, 5, 'Witness 2', 0, 1, 'C');

        $pdf->Ln(8);
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 4, 'This is a computer-generated document and does not require a physical signature.', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Document No: ' . $docCode . ' | Generated on: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $pdf->Cell(0, 4, $companyName . ' | ' . $companyAddress, 0, 1, 'C');

        $docDir = defined('APS_ROOT') ? APS_ROOT . '/assets/documents' : (defined('APP_ROOT') ? APP_ROOT . '/assets/documents' : __DIR__ . '/../../assets/documents');
        if (!is_dir($docDir)) {
            mkdir($docDir, 0755, true);
        }

        $filename = str_replace('/', '_', $docCode) . '.pdf';
        $filePath = $docDir . '/' . $filename;
        $pdf->Output($filePath, 'F');

        return '/assets/documents/' . $filename;
    }

    private function generatePaymentPlanTable($pdf, $totalPrice, $bookingAmount)
    {
        $pdf->Ln(3);
        $this->drawSectionHeader($pdf, 'INSTALLMENT SCHEDULE');
        $pdf->Ln(2);

        $remainingAmount = floatval($totalPrice) - floatval($bookingAmount);
        $numInstallments = max(1, ceil($remainingAmount / (floatval($totalPrice) * 0.2)));
        $installmentAmount = $remainingAmount / max(1, $numInstallments);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(15, 6, '#', 1, 0, 'C', true);
        $pdf->Cell(50, 6, 'Installment Type', 1, 0, 'C', true);
        $pdf->Cell(35, 6, 'Due Date', 1, 0, 'C', true);
        $pdf->Cell(35, 6, 'Amount', 1, 0, 'C', true);
        $pdf->Cell(45, 6, 'Remarks', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(15, 5, '0', 1, 0, 'C');
        $pdf->Cell(50, 5, 'Booking Amount', 1, 0, 'L');
        $pdf->Cell(35, 5, date('d/m/Y'), 1, 0, 'C');
        $pdf->Cell(35, 5, 'Rs. ' . number_format(floatval($bookingAmount), 2), 1, 0, 'R');
        $pdf->Cell(45, 5, 'At time of booking', 1, 1, 'L');

        for ($i = 1; $i <= $numInstallments; $i++) {
            $dueDate = date('d/m/Y', strtotime("+$i months"));
            $pdf->Cell(15, 5, (string)$i, 1, 0, 'C');
            $pdf->Cell(50, 5, ($i < $numInstallments) ? "Installment $i" : 'Final Payment', 1, 0, 'L');
            $pdf->Cell(35, 5, $dueDate, 1, 0, 'C');
            $pdf->Cell(35, 5, 'Rs. ' . number_format($installmentAmount, 2), 1, 0, 'R');
            $pdf->Cell(45, 5, ($i < $numInstallments) ? 'Monthly' : 'On possession', 1, 1, 'L');
        }

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(15, 5, '', 1, 0, 'C');
        $pdf->Cell(50, 5, 'Total', 1, 0, 'C');
        $pdf->Cell(35, 5, '', 1, 0, 'C');
        $pdf->Cell(35, 5, 'Rs. ' . number_format(floatval($totalPrice), 2), 1, 0, 'R');
        $pdf->Cell(45, 5, '', 1, 1, 'L');
    }

    private function drawSectionHeader($pdf, $title)
    {
        $pdf->SetFillColor(200, 160, 30);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, '  ' . $title, 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
    }

    private function getTermsConditions($type)
    {
        $commonTerms = [
            'The property is sold on an "as is where is" basis and the buyer has verified all details.',
            'The buyer agrees to pay the total consideration as per the payment schedule.',
            'Possession will be handed over after full payment and execution of the Sale Deed.',
            'The company reserves the right to cancel the allotment if payments are not made on time.',
            'All disputes shall be subject to the jurisdiction of Gorakhpur, Uttar Pradesh courts.',
            'The buyer shall bear all applicable taxes, registration charges, and stamp duty.',
        ];

        if ($type === 'allotment') {
            return array_merge([
                'This allotment is provisional and subject to receipt of full payment.',
                'The allottee must execute the Sale Agreement within 30 days of this allotment letter.',
                'Construction/development will commence as per the project timeline.',
            ], $commonTerms);
        }

        if ($type === 'sale_agreement') {
            return array_merge([
                'This Agreement is executed on the terms and conditions mentioned herein.',
                'The Seller agrees to sell and the Buyer agrees to purchase the said property.',
                'The Buyer has agreed to pay the total sale consideration as per the payment plan.',
                'The Seller shall provide clear title and possession upon full payment.',
                'Any delay in payment shall attract interest at 18% per annum.',
            ], $commonTerms);
        }

        if ($type === 'payment_plan') {
            return [
                'The payment schedule is tentative and subject to change with mutual consent.',
                'All payments must be made via cheque, bank transfer, or online payment.',
                'Delayed payments will attract interest at 12% per annum on the outstanding amount.',
                'The buyer can prepay any installment without penalty.',
                'Taxes and registration charges are payable in addition to the plot price.',
                'The company may revise the payment plan with 30 days notice.',
                'Upon full payment, the company shall execute the Sale Deed within 60 days.',
            ];
        }

        return $commonTerms;
    }

    private function saveDocumentRecord($data, $type, $docCode, $title, $filePath)
    {
        $db = Database::getInstance();
        $tid = $this->tenantId();

        $fullPath = defined('APS_ROOT') ? APS_ROOT . $filePath : (defined('APP_ROOT') ? APP_ROOT . $filePath : __DIR__ . '/../..' . $filePath);
        $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;

        $variablesData = json_encode([
            'booking_id' => $data['id'] ?? null,
            'customer_name' => $data['customer_name'] ?? '',
            'customer_id' => $data['customer_id'] ?? null,
            'plot_number' => $data['plot_number'] ?? '',
            'plot_id' => $data['plot_id'] ?? null,
            'colony_name' => $data['colony_name'] ?? '',
            'total_amount' => $data['total_amount'] ?? 0,
            'agreement_type' => $type,
            'generated_by' => $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
        ]);

        $extraCol = $tid > 1 ? ', tenant_id' : '';
        $extraVal = $tid > 1 ? ', ?' : '';
        $stmt = $db->prepare("
            INSERT INTO generated_documents 
            (document_code, document_type, entity_type, entity_id, title, variables_data, file_path, file_size, status, generated_by, generated_at, created_at, updated_at{$extraCol})
            VALUES (?, ?, 'booking', ?, ?, ?, ?, ?, 'draft', ?, NOW(), NOW(), NOW(){$extraVal})
        ");
        $params = [
            $docCode,
            'agreement',
            intval($data['id']),
            $title,
            $variablesData,
            $filePath,
            intval($fileSize),
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1
        ];
        if ($tid > 1) $params[] = $tid;
        $stmt->execute($params);

        return $db->lastInsertId();
    }

    public function getHtmlPreview($bookingId, $type)
    {
        $tid = $this->tenantId();
        $data = $this->getBookingData($bookingId);
        $companyName = $this->company['company_name'] ?? 'APS Dream Home';
        $companyAddress = $this->company['address'] ?? '';
        $companyPhone = $this->company['phone'] ?? '';
        $companyEmail = $this->company['email'] ?? '';

        $title = '';
        switch ($type) {
            case 'allotment': $title = 'ALLOTMENT LETTER'; break;
            case 'sale_agreement': $title = 'SALE AGREEMENT'; break;
            case 'payment_plan': $title = 'PAYMENT PLAN AGREEMENT'; break;
        }

        ob_start();
        include __DIR__ . '/../views/admin/agreements/preview.php';
        return ob_get_clean();
    }

    public function getDocumentById($documentId)
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM generated_documents WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->db->prepare($sql);
        $stmt->execute($tid > 1 ? [$documentId, $tid] : [$documentId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getDocumentsByBooking($bookingId)
    {
        $tid = $this->tenantId();
        $sql = "SELECT * FROM generated_documents WHERE entity_type = 'booking' AND entity_id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "") . " ORDER BY generated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($tid > 1 ? [$bookingId, $tid] : [$bookingId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function markAsSent($documentId)
    {
        $tid = $this->tenantId();
        $sql = "UPDATE generated_documents SET sent_at = NOW(), status = 'signed' WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($tid > 1 ? [$documentId, $tid] : [$documentId]);
    }
}
