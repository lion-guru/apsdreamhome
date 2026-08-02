<?php

namespace App\Services\PDF;

use TCPDF;
use Exception;
use App\Core\Middleware\TenantContext;

class ServiceTenantTrait
{
    protected static function tenantId(): int
    {
        try {
            $tid = TenantContext::getId();
            return $tid > 0 ? $tid : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    protected static function tenantWhere(string &$sql, array &$params): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $sql .= ' AND tenant_id = ?';
            $params[] = $tid;
        }
    }

    protected static function tenantInsertData(array &$columns, array &$values): void
    {
        $tid = static::tenantId();
        if ($tid > 1) {
            $columns[] = 'tenant_id';
            $values[] = $tid;
        }
    }
}

class AgreementPDFService extends ServiceTenantTrait
{
    private $db;
    private $storageDir;

    private function getTenantId(): int
    {
        try {
            $tid = TenantContext::getId();
            return $tid > 0 ? $tid : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    private static $companyName = 'APS Dream Home Pvt. Ltd.';
    private static $cin = 'U70109UP2020PTC123456';
    private static $gstin = '09AABCA1234C1Z5';
    private static $address = 'Head Office: 123, Civil Lines, Gorakhpur, Uttar Pradesh - 273001, India';
    private static $phone = '+91 92771 21112';
    private static $email = 'info@apsdreamhome.com';
    private static $website = 'www.apsdreamhome.com';
    private static $bankName = 'HDFC Bank Ltd., Gorakhpur Branch';
    private static $bankAccount = '50100234567890';
    private static $bankIfsc = 'HDFC0001234';
    private static $bankUpi = 'apsdreamhome@hdfcbank';

    public function __construct($pdo = null)
    {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            try {
                $this->db = \App\Core\Database\Database::getInstance();
                if (method_exists($this->db, 'getPdo')) {
                    $this->db = $this->db->getPdo();
                }
            } catch (Exception $e) {
                $this->db = null;
            }
        }
        $this->storageDir = __DIR__ . '/../../../storage/pdfs';
        $this->ensureDirectories();
    }

    /* =========================================================
     *  PUBLIC API
     * ========================================================= */

    public function generateBookingAgreement(int $bookingId): array
    {
        try {
            $booking = $this->fetchBooking($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            $customer = $this->fetchCustomer($booking['user_id']);
            $plot = $this->fetchPlot($booking['plot_id']);
            $colony = $this->fetchColony($plot['colony_id'] ?? 0);
            $schedule = $this->fetchSchedule($bookingId);

            $pdf = $this->createPdfObject('Booking Agreement');
            $pdf->AddPage();

            $this->renderAgreementHeader($pdf);
            $this->renderAgreementParties($pdf, $booking, $customer);
            $this->renderAgreementProperty($pdf, $plot, $colony);
            $this->renderAgreementTerms($pdf, $booking);
            $this->renderPaymentSchedule($pdf, $schedule);
            $this->renderSignatures($pdf);
            $this->renderFooter($pdf, 'booking_agreement');

            $filename = 'agreement_' . $booking['booking_number'] . '_' . date('Ymd') . '.pdf';
            $path = $this->storageDir . '/agreements/' . $filename;
            $pdf->Output($path, 'F');

            $this->logPdfGeneration('agreement', $bookingId, $filename, filesize($path));

            return [
                'success' => true,
                'pdf_path' => $path,
                'filename' => $filename,
                'file_size' => filesize($path),
            ];
        } catch (Exception $e) {
            error_log('AgreementPDFService::generateBookingAgreement error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateDemandLetter(int $installmentId): array
    {
        try {
            $installment = $this->fetchInstallment($installmentId);
            if (!$installment) {
                return ['success' => false, 'error' => 'Installment not found'];
            }

            $booking = $this->fetchBooking($installment['booking_id']);
            $customer = $this->fetchCustomer($booking['user_id']);
            $plot = $this->fetchPlot($booking['plot_id']);
            $colony = $this->fetchColony($plot['colony_id'] ?? 0);

            $pdf = $this->createPdfObject('Demand Letter');
            $pdf->AddPage();

            $this->renderDemandHeader($pdf);
            $this->renderDemandDetails($pdf, $booking, $customer, $installment, $plot, $colony);
            $this->renderDemandBreakdown($pdf, $installment);
            $this->renderPaymentInstructions($pdf);
            $this->renderFooter($pdf, 'demand_letter');

            $filename = 'demand_letter_' . $booking['booking_number'] . '_inst' . $installment['installment_number'] . '_' . date('Ymd') . '.pdf';
            $path = $this->storageDir . '/demand_letters/' . $filename;
            $pdf->Output($path, 'F');

            $this->logPdfGeneration('demand_letter', $installmentId, $filename, filesize($path));

            return [
                'success' => true,
                'pdf_path' => $path,
                'filename' => $filename,
                'file_size' => filesize($path),
            ];
        } catch (Exception $e) {
            error_log('AgreementPDFService::generateDemandLetter error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateAllotmentLetter(int $bookingId): array
    {
        try {
            $booking = $this->fetchBooking($bookingId);
            if (!$booking) {
                return ['success' => false, 'error' => 'Booking not found'];
            }

            $customer = $this->fetchCustomer($booking['user_id']);
            $plot = $this->fetchPlot($booking['plot_id']);
            $colony = $this->fetchColony($plot['colony_id'] ?? 0);

            $pdf = $this->createPdfObject('Allotment Letter');
            $pdf->AddPage();

            $this->renderAllotmentHeader($pdf);
            $this->renderAllotmentDetails($pdf, $booking, $customer, $plot, $colony);
            $this->renderAllotmentTerms($pdf, $booking);
            $this->renderSignatures($pdf);
            $this->renderFooter($pdf, 'allotment_letter');

            $filename = 'allotment_' . $booking['booking_number'] . '_' . date('Ymd') . '.pdf';
            $path = $this->storageDir . '/allotment_letters/' . $filename;
            $pdf->Output($path, 'F');

            $this->logPdfGeneration('allotment_letter', $bookingId, $filename, filesize($path));

            return [
                'success' => true,
                'pdf_path' => $path,
                'filename' => $filename,
                'file_size' => filesize($path),
            ];
        } catch (Exception $e) {
            error_log('AgreementPDFService::generateAllotmentLetter error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateRefundVoucher(int $refundId): array
    {
        try {
            $refund = $this->fetchRefund($refundId);
            if (!$refund) {
                return ['success' => false, 'error' => 'Refund not found'];
            }

            $booking = $this->fetchBooking($refund['booking_id']);
            $customer = $this->fetchCustomer($booking['user_id']);

            $pdf = $this->createPdfObject('Refund Voucher');
            $pdf->AddPage();

            $this->renderRefundHeader($pdf);
            $this->renderRefundDetails($pdf, $refund, $booking, $customer);
            $this->renderRefundTerms($pdf, $refund);
            $this->renderSignatures($pdf);
            $this->renderFooter($pdf, 'refund_voucher');

            $filename = 'refund_' . $refund['refund_number'] . '_' . date('Ymd') . '.pdf';
            $path = $this->storageDir . '/refund_vouchers/' . $filename;
            $pdf->Output($path, 'F');

            $this->logPdfGeneration('refund_voucher', $refundId, $filename, filesize($path));

            return [
                'success' => true,
                'pdf_path' => $path,
                'filename' => $filename,
                'file_size' => filesize($path),
            ];
        } catch (Exception $e) {
            error_log('AgreementPDFService::generateRefundVoucher error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* =========================================================
     *  TCPDF BASE OBJECT
     * ========================================================= */

    private function createPdfObject(string $title): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(self::$companyName);
        $pdf->SetTitle($title);
        $pdf->SetHeaderData('', 0, self::$companyName, 'CIN: ' . self::$cin . ' | GSTIN: ' . self::$gstin);
        $pdf->setHeaderFont(['helvetica' => '', 8]);
        $pdf->setFooterFont(['helvetica' => '', 8]);
        $pdf->SetMargins(20, 35, 20);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(15);
        $pdf->SetAutoPageBreak(true, 25);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setLanguageArray([
            'l' => 'en', 'a' => 'UTF-8', 'u' => '',
            'ce' => 'UTF-8', 'co' => '-', 'do' => '-',
            'wa' => 'a', 'cc' => 'CP1250', 'enc' => 'UTF-8',
        ]);
        return $pdf;
    }

    private function addWatermark(TCPDF $pdf, string $text): void
    {
        $pdf->SetFont('helvetica' , 'B', 60);
        $pdf->SetTextColor(230, 230, 230);
        $pdf->SetAlpha(0.15);
        $pdf->StartTransform();
        $pdf->Rotate(-35);
        $pdf->Text(35, 180, $text);
        $pdf->StopTransform();
        $pdf->SetAlpha(1);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('helvetica', '', 10);
    }

    /* =========================================================
     *  SHARED RENDERERS
     * ========================================================= */

    private function renderCompanyHeader(TCPDF $pdf, string $docTitle): void
    {
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(79, 70, 229);
        $pdf->Cell(0, 10, self::$companyName, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'CIN: ' . self::$cin . '  |  GSTIN: ' . self::$gstin, 0, 1, 'C');
        $pdf->Cell(0, 5, self::$address, 0, 1, 'C');
        $pdf->Cell(0, 5, 'Ph: ' . self::$phone . '  |  Email: ' . self::$email, 0, 1, 'C');

        $pdf->Ln(3);
        $pdf->SetDrawColor(79, 70, 229);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->Cell(0, 10, $docTitle, 0, 1, 'C');
        $pdf->Ln(2);
    }

    private function renderInfoRow(TCPDF $pdf, string $label, string $value): void
    {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(50, 7, $label . ':', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, $value, 0, 1);
    }

    private function renderTableHeader(TCPDF $pdf, array $headers, array $widths): void
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(79, 70, 229);
        $pdf->SetTextColor(255, 255, 255);
        foreach ($headers as $i => $h) {
            $align = ($i === count($headers) - 1) ? 'R' : 'L';
            $pdf->Cell($widths[$i], 8, $h, 1, 0, $align, true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(30, 30, 30);
    }

    private function renderTableRow(TCPDF $pdf, array $cells, array $widths, bool $alt = false): void
    {
        $pdf->SetFont('helvetica', '', 9);
        if ($alt) {
            $pdf->SetFillColor(248, 250, 252);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        foreach ($cells as $i => $c) {
            $align = ($i === count($cells) - 1) ? 'R' : 'L';
            $pdf->Cell($widths[$i], 7, $c, 1, 0, $align, true);
        }
        $pdf->Ln();
    }

    private function renderSectionTitle(TCPDF $pdf, string $title): void
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(79, 70, 229);
        $pdf->Cell(0, 10, $title, 0, 1, 'L');
        $pdf->SetDrawColor(229, 231, 235);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(3);
        $pdf->SetTextColor(30, 30, 30);
    }

    private function renderBodyText(TCPDF $pdf, string $text): void
    {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 6, $text, 0, 'J');
        $pdf->Ln(3);
    }

    private function renderSignatureBlock(TCPDF $pdf): void
    {
        $pdf->Ln(15);
        $y = $pdf->GetY();
        $pdf->SetFont('helvetica', '', 10);

        $pdf->SetXY(25, $y);
        $pdf->Cell(60, 5, 'Customer Signature', 'T', 0, 'C');
        $pdf->SetXY(105, $y);
        $pdf->Cell(70, 5, 'Authorized Signatory', 'T', 0, 'C');

        $pdf->SetXY(105, $y + 5);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell(70, 5, self::$companyName, 0, 0, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY(20, $y + 12);
    }

    private function renderDocFooter(TCPDF $pdf, string $type): void
    {
        $pdf->Ln(5);
        $pdf->SetDrawColor(79, 70, 229);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 5, 'This is a computer-generated document. No physical signature is required.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'For queries, contact ' . self::$phone . ' or ' . self::$email, 0, 1, 'C');
        $pdf->Cell(0, 5, self::$website, 0, 1, 'C');
        $pdf->SetTextColor(30, 30, 30);
    }

    private function inr(float $amount): string
    {
        return "\xE2\x82\xB9" . number_format($amount, 2);
    }

    private function dateInr(string $date): string
    {
        if (empty($date)) return 'N/A';
        $ts = strtotime($date);
        if (!$ts) return $date;
        return date('d/m/Y', $ts);
    }

    /* =========================================================
     *  AGREEMENT SPECIFIC RENDERERS
     * ========================================================= */

    private function renderAgreementHeader(TCPDF $pdf): void
    {
        $this->addWatermark($pdf, 'AGREEMENT');
        $this->renderCompanyHeader($pdf, 'PROPERTY BOOKING AGREEMENT');
    }

    private function renderAgreementParties(TCPDF $pdf, array $booking, array $customer): void
    {
        $this->renderSectionTitle($pdf, '1. PARTIES TO THIS AGREEMENT');

        $this->renderInfoRow($pdf, 'Agreement Date', $this->dateInr($booking['booking_date']));
        $this->renderInfoRow($pdf, 'Booking Reference', $booking['booking_number'] ?? 'N/A');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'BU', 10);
        $pdf->Cell(0, 7, 'SELLER / PROMOTER:', 0, 1);
        $this->renderInfoRow($pdf, 'Name', self::$companyName);
        $this->renderInfoRow($pdf, 'CIN', self::$cin);
        $this->renderInfoRow($pdf, 'GSTIN', self::$gstin);
        $this->renderInfoRow($pdf, 'Address', self::$address);
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', 'BU', 10);
        $pdf->Cell(0, 7, 'BUYER / CUSTOMER:', 0, 1);
        $this->renderInfoRow($pdf, 'Name', $customer['name'] ?? 'N/A');
        $this->renderInfoRow($pdf, 'Phone', $customer['phone'] ?? 'N/A');
        $this->renderInfoRow($pdf, 'Email', $customer['email'] ?? 'N/A');
        $this->renderInfoRow($pdf, 'Address', $customer['address'] ?? 'N/A');
        $pdf->Ln(3);
    }

    private function renderAgreementProperty(TCPDF $pdf, array $plot, array $colony): void
    {
        $this->renderSectionTitle($pdf, '2. PROPERTY DESCRIPTION');

        $plotNo = $plot['plot_number'] ?? 'N/A';
        $block = $plot['block_name'] ?? '';
        $colonyName = $colony['name'] ?? 'N/A';
        $district = $colony['district_name'] ?? '';
        $area = number_format((float)($plot['area_sqft'] ?? 0));
        $dim = trim(($plot['width_ft'] ?? 0) . ' x ' . ($plot['length_ft'] ?? 0) . ' ft');
        $facing = $plot['facing'] ?? 'N/A';
        $price = $plot['total_price'] ?? 0;

        $this->renderInfoRow($pdf, 'Colony / Project', $colonyName);
        $this->renderInfoRow($pdf, 'Location', $district);
        $this->renderInfoRow($pdf, 'Plot No', $plotNo . ($block ? ' (Block: ' . $block . ')' : ''));
        $this->renderInfoRow($pdf, 'Area', $area . ' sq ft');
        $this->renderInfoRow($pdf, 'Dimensions', $dim);
        $this->renderInfoRow($pdf, 'Facing', $facing);
        $this->renderInfoRow($pdf, 'Agreed Value', $this->inr((float)$price));
        $pdf->Ln(3);
    }

    private function renderAgreementTerms(TCPDF $pdf, array $booking): void
    {
        $this->renderSectionTitle($pdf, '3. TERMS AND CONDITIONS');

        $terms = [
            '1. This booking is subject to verification of customer credentials and KYC documents.',
            '2. The buyer agrees to pay the balance amount as per the payment schedule attached hereto.',
            '3. Stamp duty, registration charges, and other statutory levies shall be borne by the buyer.',
            '4. Possession shall be handed over as per the timeline mentioned in the allotment letter.',
            '5. In case of cancellation, the refund shall be processed as per the cancellation policy.',
            '6. Any delay in payment beyond the due date shall attract a late fee of 1.5% per month.',
            '7. The seller reserves the right to alter specifications subject to statutory approvals.',
            '8. All disputes shall be subject to the jurisdiction of courts in Gorakhpur, Uttar Pradesh.',
            '9. Force Majeure: Neither party shall be liable for delays due to natural calamities, government actions, or other events beyond reasonable control.',
            '10. This agreement is governed by the Indian Contract Act, 1872, and the Real Estate (Regulation and Development) Act, 2016.',
        ];

        foreach ($terms as $t) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5.5, $t, 0, 'J');
            $pdf->Ln(1.5);
        }
        $pdf->Ln(3);
    }

    private function renderPaymentSchedule(TCPDF $pdf, array $schedule): void
    {
        $this->renderSectionTitle($pdf, '4. PAYMENT SCHEDULE');

        $headers = ['#', 'Due Date', 'Type', 'Amount (INR)', 'Paid (INR)', 'Status'];
        $widths = [12, 30, 30, 40, 40, 28];
        $this->renderTableHeader($pdf, $headers, $widths);

        $totalAmount = 0;
        $totalPaid = 0;
        foreach ($schedule as $i => $s) {
            $amt = (float)($s['amount_due'] ?? 0);
            $paid = (float)($s['amount_paid'] ?? 0);
            $totalAmount += $amt;
            $totalPaid += $paid;

            $this->renderTableRow($pdf, [
                (string)($s['installment_number'] ?? ($i + 1)),
                $this->dateInr($s['due_date'] ?? ''),
                ucfirst($s['installment_type'] ?? 'emi'),
                $this->inr($amt),
                $this->inr($paid),
                ucfirst($s['status'] ?? 'pending'),
            ], $widths, $i % 2 === 1);
        }

        $this->renderTableRow($pdf, [
            '', '', 'TOTAL',
            $this->inr($totalAmount),
            $this->inr($totalPaid),
            '',
        ], $widths, false);

        $pdf->Ln(3);
        $this->renderBodyText($pdf, 'Remaining Balance: ' . $this->inr($totalAmount - $totalPaid));
    }

    /* =========================================================
     *  DEMAND LETTER SPECIFIC RENDERERS
     * ========================================================= */

    private function renderDemandHeader(TCPDF $pdf): void
    {
        $this->addWatermark($pdf, 'DEMAND');
        $this->renderCompanyHeader($pdf, 'DEMAND LETTER');
    }

    private function renderDemandDetails(TCPDF $pdf, array $booking, array $customer, array $inst, array $plot, array $colony): void
    {
        $today = date('d/m/Y');
        $ref = 'DL-' . ($booking['booking_number'] ?? '0') . '-' . str_pad((string)($inst['installment_number'] ?? 0), 2, '0', STR_PAD_LEFT);
        $isOverdue = ($inst['status'] ?? '') === 'overdue';

        $this->renderInfoRow($pdf, 'Date', $today);
        $this->renderInfoRow($pdf, 'Reference No', $ref);
        $this->renderInfoRow($pdf, 'Status', $isOverdue ? 'OVERDUE' : 'DUE');
        $pdf->Ln(3);

        $this->renderBodyText($pdf, 'Dear ' . ($customer['name'] ?? 'Customer') . ',');

        $plotNo = $plot['plot_number'] ?? 'N/A';
        $block = $plot['block_name'] ?? '';
        $colonyName = $colony['name'] ?? 'N/A';
        $area = number_format((float)($plot['area_sqft'] ?? 0));

        $this->renderBodyText($pdf,
            'This is to inform you that Installment #' . ($inst['installment_number'] ?? 0) .
            ' under Booking Reference ' . ($booking['booking_number'] ?? 'N/A') .
            ' for Plot ' . $plotNo . ($block ? ' (Block: ' . $block . ')' : '') .
            ' at ' . $colonyName . ' (' . $area . ' sq ft) is ' .
            ($isOverdue ? 'OVERDUE' : 'due for payment') . '.'
        );

        $this->renderBodyText($pdf,
            'Please arrange payment of ' . $this->inr(
                (float)($inst['amount_due'] ?? 0) +
                (float)($inst['late_fee'] ?? 0) +
                (float)($inst['accrued_penalty'] ?? 0)
            ) . ' on or before ' . $this->dateInr($inst['due_date'] ?? '') .
            ' to avoid additional late fees and penalties.'
        );
    }

    private function renderDemandBreakdown(TCPDF $pdf, array $inst): void
    {
        $this->renderSectionTitle($pdf, 'AMOUNT BREAKDOWN');

        $headers = ['Description', 'Amount (INR)'];
        $widths = [110, 60];
        $this->renderTableHeader($pdf, $headers, $widths);

        $principal = (float)($inst['principal'] ?? ($inst['amount_due'] ?? 0));
        $interest = (float)($inst['interest'] ?? 0);
        $lateFee = (float)($inst['late_fee'] ?? 0);
        $penalty = (float)($inst['accrued_penalty'] ?? 0);
        $total = $principal + $interest + $lateFee + $penalty;

        $this->renderTableRow($pdf, ['Principal', $this->inr($principal)], $widths, false);
        if ($interest > 0) {
            $this->renderTableRow($pdf, ['Interest', $this->inr($interest)], $widths, true);
        }
        if ($lateFee > 0) {
            $this->renderTableRow($pdf, ['Late Fee', $this->inr($lateFee)], $widths, false);
        }
        if ($penalty > 0) {
            $this->renderTableRow($pdf, ['Accrued Penalty', $this->inr($penalty)], $widths, true);
        }
        $this->renderTableRow($pdf, ['TOTAL AMOUNT DUE', $this->inr($total)], $widths, false);

        $pdf->Ln(3);
        $this->renderBodyText($pdf,
            'Please note that as per the terms of your Agreement, payments not received by the due date are subject to a late fee of 1.5% per month on the outstanding amount, plus applicable penalties as outlined in the booking agreement.'
        );
    }

    /* =========================================================
     *  ALLOTMENT LETTER RENDERERS
     * ========================================================= */

    private function renderAllotmentHeader(TCPDF $pdf): void
    {
        $this->addWatermark($pdf, 'ALLOTMENT');
        $this->renderCompanyHeader($pdf, 'PLOT ALLOTMENT LETTER');
    }

    private function renderAllotmentDetails(TCPDF $pdf, array $booking, array $customer, array $plot, array $colony): void
    {
        $this->renderInfoRow($pdf, 'Allotment Date', $this->dateInr(date('Y-m-d')));
        $this->renderInfoRow($pdf, 'Booking Reference', $booking['booking_number'] ?? 'N/A');
        $pdf->Ln(3);

        $this->renderSectionTitle($pdf, 'ALLOTMENT DETAILS');

        $plotNo = $plot['plot_number'] ?? 'N/A';
        $block = $plot['block_name'] ?? '';
        $colonyName = $colony['name'] ?? 'N/A';
        $district = $colony['district_name'] ?? '';
        $area = number_format((float)($plot['area_sqft'] ?? 0));
        $dim = trim(($plot['width_ft'] ?? 0) . ' x ' . ($plot['length_ft'] ?? 0) . ' ft');
        $facing = $plot['facing'] ?? 'N/A';
        $price = $plot['total_price'] ?? 0;

        $this->renderInfoRow($pdf, 'Customer Name', $customer['name'] ?? 'N/A');
        $this->renderInfoRow($pdf, 'Colony', $colonyName);
        $this->renderInfoRow($pdf, 'District', $district);
        $this->renderInfoRow($pdf, 'Plot No', $plotNo . ($block ? ' (Block: ' . $block . ')' : ''));
        $this->renderInfoRow($pdf, 'Area', $area . ' sq ft');
        $this->renderInfoRow($pdf, 'Dimensions', $dim);
        $this->renderInfoRow($pdf, 'Facing', $facing);
        $this->renderInfoRow($pdf, 'Plot Value', $this->inr((float)$price));
        $this->renderInfoRow($pdf, 'Booking Amount Paid', $this->inr((float)($booking['booking_amount'] ?? 0)));
        $this->renderInfoRow($pdf, 'Balance Amount', $this->inr((float)($booking['total_plot_value'] ?? 0) - (float)($booking['booking_amount'] ?? 0)));
        $pdf->Ln(3);
    }

    private function renderAllotmentTerms(TCPDF $pdf, array $booking): void
    {
        $this->renderSectionTitle($pdf, 'TERMS OF ALLOTMENT');

        $terms = [
            '1. This allotment is subject to the buyer fulfilling all payment obligations as per the booking agreement.',
            '2. The plot shall be handed over after all dues are cleared and registration is completed.',
            '3. The buyer is responsible for stamp duty, registration charges, and other statutory fees.',
            '4. Any construction on the plot must comply with local building bylaws and obtain necessary approvals.',
            '5. This allotment letter is valid for 90 days from the date of issuance.',
            '6. Transfer of allotment to a third party requires prior written consent of the Company.',
            '7. Maintenance charges for common areas shall be billed separately as per RERA guidelines.',
        ];

        foreach ($terms as $t) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5.5, $t, 0, 'J');
            $pdf->Ln(1.5);
        }
    }

    /* =========================================================
     *  REFUND VOUCHER RENDERERS
     * ========================================================= */

    private function renderRefundHeader(TCPDF $pdf): void
    {
        $this->addWatermark($pdf, 'REFUND');
        $this->renderCompanyHeader($pdf, 'REFUND VOUCHER');
    }

    private function renderRefundDetails(TCPDF $pdf, array $refund, array $booking, array $customer): void
    {
        $this->renderInfoRow($pdf, 'Voucher No', $refund['refund_number'] ?? 'N/A');
        $this->renderInfoRow($pdf, 'Date', $this->dateInr($refund['processed_at'] ?? date('Y-m-d')));
        $this->renderInfoRow($pdf, 'Booking Ref', $booking['booking_number'] ?? 'N/A');
        $pdf->Ln(3);

        $this->renderSectionTitle($pdf, 'REFUND DETAILS');

        $this->renderInfoRow($pdf, 'Customer Name', $customer['name'] ?? 'N/A');
        $this->renderInfoRow($pdf, 'Original Amount', $this->inr((float)($booking['total_plot_value'] ?? 0)));
        $this->renderInfoRow($pdf, 'Amount Paid', $this->inr((float)($booking['booking_amount'] ?? 0)));
        $this->renderInfoRow($pdf, 'Cancellation Charge', $this->inr((float)($refund['cancellation_charge'] ?? 0)));
        $this->renderInfoRow($pdf, 'Refund Amount', $this->inr((float)($refund['refund_amount'] ?? 0)));
        $pdf->Ln(3);

        $this->renderBodyText($pdf, 'Mode of Refund: ' . ucfirst($refund['refund_mode'] ?? 'bank_transfer'));
    }

    private function renderRefundTerms(TCPDF $pdf, array $refund): void
    {
        $this->renderSectionTitle($pdf, 'TERMS');

        $terms = [
            '1. Refund will be processed within 15 business days from the date of approval.',
            '2. The refund amount is net of all applicable cancellation charges and deductions.',
            '3. Refund will be made via bank transfer to the account on record.',
            '4. This voucher is valid for 90 days from the date of issuance.',
            '5. For any queries regarding the refund, please contact our finance team.',
        ];

        foreach ($terms as $t) {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5.5, $t, 0, 'J');
            $pdf->Ln(1.5);
        }
    }

    /* =========================================================
     *  PAYMENT INSTRUCTIONS
     * ========================================================= */

    private function renderPaymentInstructions(TCPDF $pdf): void
    {
        $this->renderSectionTitle($pdf, 'PAYMENT INSTRUCTIONS');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, 'Bank Transfer / NEFT / RTGS / UPI', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $this->renderInfoRow($pdf, 'Account Name', self::$companyName);
        $this->renderInfoRow($pdf, 'Account No', self::$bankAccount);
        $this->renderInfoRow($pdf, 'Bank', self::$bankName);
        $this->renderInfoRow($pdf, 'IFSC', self::$bankIfsc);
        $this->renderInfoRow($pdf, 'UPI', self::$bankUpi);
        $pdf->Ln(3);

        $this->renderBodyText($pdf, 'Please share the transaction reference number via email or WhatsApp after payment.');
    }

    /* =========================================================
     *  RENDER SIGNATURES + FOOTER
     * ========================================================= */

    private function renderSignatures(TCPDF $pdf): void
    {
        $this->renderSignatureBlock($pdf);
    }

    private function renderFooter(TCPDF $pdf, string $type): void
    {
        $this->renderDocFooter($pdf, $type);
    }

    /* =========================================================
     *  DB FETCHERS
     * ========================================================= */

    private function fetchBooking(int $id): ?array
    {
        if (!$this->db) return null;
        try {
            $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone, u.email AS customer_email,
                            p.plot_number, p.area_sqft, p.width_ft, p.length_ft, p.facing, p.total_price AS plot_price,
                            p.colony_id, p.block_name
                     FROM plot_bookings b
                     LEFT JOIN users u ON b.user_id = u.id
                     LEFT JOIN inventory_plots p ON b.plot_id = p.id
                     WHERE b.id = ?";
            $params = [(int)$id];
            $this->tenantWhere($sql, $params);
            $sql .= ' LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function fetchCustomer(int $userId): ?array
    {
        if (!$this->db || !$userId) return null;
        try {
            $tid = $this->getTenantId();
            if ($tid > 1) {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ? LIMIT 1");
                $stmt->execute([$userId, $tid]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$userId]);
            }
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function fetchPlot(int $plotId): ?array
    {
        if (!$this->db || !$plotId) return null;
        try {
            $sql = "SELECT * FROM inventory_plots WHERE id = ?";
            $params = [(int)$plotId];
            $this->tenantWhere($sql, $params);
            $sql .= ' LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function fetchColony(int $colonyId): ?array
    {
        if (!$this->db || !$colonyId) return null;
        try {
            $sql = "SELECT c.*, d.name AS district_name FROM colonies c
                     LEFT JOIN districts d ON c.district_id = d.id
                     WHERE c.id = ?";
            $params = [(int)$colonyId];
            $this->tenantWhere($sql, $params);
            $sql .= ' LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function fetchSchedule(int $bookingId): array
    {
        if (!$this->db) return [];
        try {
            $sql = "SELECT * FROM booking_payment_schedules WHERE booking_id = ?";
            $params = [(int)$bookingId];
            $this->tenantWhere($sql, $params);
            $sql .= ' ORDER BY installment_number ASC';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    private function fetchInstallment(int $id): ?array
    {
        if (!$this->db) return null;
        try {
            $sql = "SELECT * FROM booking_payment_schedules WHERE id = ?";
            $params = [(int)$id];
            $this->tenantWhere($sql, $params);
            $sql .= ' LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function fetchRefund(int $id): ?array
    {
        if (!$this->db) return null;
        try {
            $sql = "SELECT * FROM booking_refunds WHERE id = ?";
            $params = [(int)$id];
            $this->tenantWhere($sql, $params);
            $sql .= ' LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
} catch (Exception $e) {
            return null;
        }
    }

    /* =========================================================
     *  UTILITIES
     * ========================================================= */

    private function ensureDirectories(): void
    {
        $dirs = [
            $this->storageDir,
            $this->storageDir . '/agreements',
            $this->storageDir . '/demand_letters',
            $this->storageDir . '/allotment_letters',
            $this->storageDir . '/refund_vouchers',
        ];
        foreach ($dirs as $d) {
            if (!is_dir($d)) {
                mkdir($d, 0755, true);
            }
        }
    }

    private function logPdfGeneration(string $type, int $entityId, string $filename, int $size): void
    {
        if (!$this->db) return;
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO gateway_logs (gateway, action, recipient, status, cost, error_message, request_body, response_body, duration_ms, created_at)
                 VALUES ('pdf_generator', ?, ?, 'success', 0, NULL, ?, ?, 0, NOW())"
            );
            $stmt->execute([
                $type,
                'entity_' . $entityId,
                json_encode(['entity_id' => $entityId, 'type' => $type]),
                json_encode(['filename' => $filename, 'size' => $size]),
            ]);
        } catch (Exception $e) {
            error_log('AgreementPDFService::logPdfGeneration error: ' . $e->getMessage());
        }
    }
}
