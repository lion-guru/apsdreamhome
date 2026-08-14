<?php

namespace App\Services;

class PDFService
{
    private static $companyName = 'APS Dream Home';
    private static $companyAddress = 'Head Office: Gorakhpur, Uttar Pradesh, India';
    private static $companyPhone = '+91 92771 21112';
    private static $companyEmail = 'info@apsdreamhome.com';
    private static $companyWebsite = 'www.apsdreamhome.com';

    private static function cssBase(): string
    {
        return '
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #1e293b; line-height: 1.6; background: #fff; }
            .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-size: 80px; font-weight: 900; color: rgba(0,0,0,0.04); letter-spacing: 12px; text-transform: uppercase; pointer-events: none; z-index: 0; white-space: nowrap; }
            .page { max-width: 800px; margin: 0 auto; padding: 40px 50px; position: relative; z-index: 1; }
            .header { text-align: center; border-bottom: 3px solid #0d9488; padding-bottom: 20px; margin-bottom: 30px; }
            .header h1 { color: #0d9488; font-size: 28px; margin-bottom: 4px; }
            .header .tagline { color: #6b7280; font-size: 13px; letter-spacing: 2px; text-transform: uppercase; }
            .header .contact { font-size: 12px; color: #6b7280; margin-top: 8px; }
            .header .contact span { margin: 0 8px; }
            .section-title { font-size: 18px; font-weight: 700; color: #0d9488; margin: 24px 0 12px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 24px; margin-bottom: 20px; }
            .info-grid .row { display: flex; }
            .info-grid .label { font-weight: 600; color: #6b7280; min-width: 140px; }
            .info-grid .value { color: #1e293b; font-weight: 500; }
            table { width: 100%; border-collapse: collapse; margin: 16px 0; }
            table th { background: #0d9488; color: #fff; padding: 10px 12px; text-align: left; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
            table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
            table tr:nth-child(even) td { background: #f8fafc; }
            .amount { text-align: right; font-family: "Courier New", monospace; }
            .total-row td { font-weight: 700; background: #ede9fe !important; border-top: 2px solid #0d9488; }
            .body-text { margin: 16px 0; line-height: 1.8; }
            .body-text p { margin-bottom: 12px; }
            .highlight { background: #fef3c7; padding: 2px 6px; border-radius: 3px; font-weight: 600; }
            .footer { margin-top: 40px; border-top: 2px solid #0d9488; padding-top: 16px; font-size: 12px; color: #6b7280; }
            .footer .payment-info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
            .footer .payment-info h4 { color: #166534; font-size: 14px; margin-bottom: 8px; }
            .signature { margin-top: 50px; display: flex; justify-content: space-between; }
            .signature-block { text-align: center; width: 200px; }
            .signature-block .line { border-top: 1px solid #1e293b; margin-top: 60px; padding-top: 6px; font-weight: 600; font-size: 13px; }
            .noc-header { text-align: center; margin-bottom: 24px; }
            .noc-number { font-size: 16px; color: #0d9488; font-weight: 700; }
            .stamp-box { border: 2px solid #dc2626; border-radius: 50%; width: 100px; height: 100px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; transform: rotate(-12deg); }
            .stamp-box span { color: #dc2626; font-weight: 900; font-size: 11px; text-transform: uppercase; text-align: center; line-height: 1.2; }
            @media print {
                body { background: #fff; }
                .page { padding: 30px 40px; }
                .no-print { display: none !important; }
            }
        ';
    }

    private static function cssPrint(): string
    {
        return '
            @media print {
                body { background: #fff; margin: 0; }
                .page { padding: 20px 30px; max-width: 100%; }
                .no-print { display: none !important; }
            }
        ';
    }

    private static function companyHeader(): string
    {
        return '
            <div class="header">
                <h1>' . self::$companyName . '</h1>
                <div class="tagline">Building Dreams, Delivering Trust</div>
                <div class="contact">
                    <span><i class="fas fa-map-marker-alt"></i> ' . self::$companyAddress . '</span>
                    <span><i class="fas fa-phone"></i> ' . self::$companyPhone . '</span>
                    <span><i class="fas fa-envelope"></i> ' . self::$companyEmail . '</span>
                </div>
            </div>
        ';
    }

    public static function generateDemandLetter(array $booking, array $installment, array $colony, array $plot): string
    {
        $customerName = htmlspecialchars($booking['customer_name'] ?? 'Customer');
        $plotNo = htmlspecialchars($plot['plot_number'] ?? 'N/A');
        $block = htmlspecialchars($plot['block'] ?? '');
        $colonyName = htmlspecialchars($colony['name'] ?? 'N/A');
        $instNo = (int)($installment['installment_no'] ?? 0);
        $dueDate = date('d M Y', strtotime($installment['due_date'] ?? 'now'));
        $amount = number_format((float)($installment['amount'] ?? 0), 2);
        $principal = number_format((float)($installment['principal'] ?? 0), 2);
        $interest = number_format((float)($installment['interest'] ?? 0), 2);
        $lateFee = number_format((float)($installment['late_fee'] ?? 0), 2);
        $penalty = number_format((float)($installment['accrued_penalty'] ?? 0), 2);
        $totalDue = number_format((float)($installment['amount'] ?? 0) + (float)($installment['late_fee'] ?? 0) + (float)($installment['accrued_penalty'] ?? 0), 2);
        $bookingNo = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $today = date('d M Y');
        $area = number_format((float)($plot['area_sqft'] ?? 0)) . ' sq ft';
        $dimLabel = $plot['dimension_label'] ?? (($plot['width_ft'] ?? 0) . ' x ' . ($plot['length_ft'] ?? 0) . ' ft');

        $isOverdue = ($installment['status'] ?? '') === 'overdue';

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demand Letter - ' . $bookingNo . '</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>' . self::cssBase() . self::cssPrint() . '</style>
</head>
<body>
    <div class="watermark">DEMAND LETTER</div>
    <div class="page">
        ' . self::companyHeader() . '
        <div class="style-1051">
            <div>
                <strong>Date:</strong> ' . $today . '<br>
                <strong>Ref:</strong> DL-' . $bookingNo . '-' . str_pad($instNo, 2, '0', STR_PAD_LEFT) . '
            </div>
            ' . ($isOverdue ? '<span class="style-8565">OVERDUE</span>' : '<span class="style-44758">DUE</span>') . '
        </div>

        <div class="body-text">
            <p>Dear <strong>' . $customerName . '</strong>,</p>
            <p>This is to inform you that Installment <strong>#' . $instNo . '</strong> under your Booking Reference 
            <strong>' . $bookingNo . '</strong> for Plot <strong>' . $plotNo . ($block ? ' (Block ' . $block . ')' : '') . '</strong> 
            at <strong>' . $colonyName . '</strong> (' . $area . ', ' . $dimLabel . ') is ' . ($isOverdue ? '<span class="highlight">overdue</span>' : 'due for payment') . '.</p>
            <p>Please arrange payment of <strong>â‚¹' . $totalDue . '</strong> on or before <strong>' . $dueDate . '</strong> to avoid additional late fees and penalties.</p>
        </div>

        <h3 class="section-title">Installment Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Amount (â‚¹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Principal</td>
                    <td class="amount">' . $principal . '</td>
                </tr>
                <tr>
                    <td>Interest</td>
                    <td class="amount">' . $interest . '</td>
                </tr>' . '
                ' . ((float)$lateFee > 0 ? '<tr>
                    <td>Late Fee</td>
                    <td class="amount" class="style-78245">' . $lateFee . '</td>
                </tr>' : '') . '
                ' . ((float)$penalty > 0 ? '<tr>
                    <td>Accrued Penalty</td>
                    <td class="amount" class="style-78245">' . $penalty . '</td>
                </tr>' : '') . '
                <tr class="total-row">
                    <td>Total Amount Due</td>
                    <td class="amount">â‚¹' . $totalDue . '</td>
                </tr>
            </tbody>
        </table>

        <div class="body-text">
            <p>Please note that as per the terms of your Agreement, payments not received by the due date are subject to a late fee of 1.5% per month on the outstanding amount, plus applicable penalties as outlined in the booking agreement.</p>
        </div>

        <div class="footer">
            <div class="payment-info">
                <h4><i class="fas fa-university"></i> Payment Instructions</h4>
                <p><strong>Bank Transfer / NEFT / RTGS:</strong></p>
                <p>
                    Account Name: APS Dream Home Pvt. Ltd.<br>
                    Account No: 50100234567890<br>
                    Bank: HDFC Bank, Gorakhpur Branch<br>
                    IFSC: HDFC0001234<br>
                    UPI: apsdreamhome@hdfcbank
                </p>
                <p class="style-29694"><strong>Note:</strong> Please share the transaction reference number via email or WhatsApp after payment.</p>
            </div>
            <p class="style-31100">
                For queries, contact us at <strong>' . self::$companyPhone . '</strong> or <strong>' . self::$companyEmail . '</strong><br>
                This is a system-generated document. No signature required.
            </p>
        </div>

        <div class="no-print" class="style-18221">
            <button onclick="window.print()" class="style-37441">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</body>
</html>';
    }

    public static function generateBookingReceipt(array $booking, array $colony, array $plot): string
    {
        $customerName = htmlspecialchars($booking['customer_name'] ?? 'Customer');
        $customerPhone = htmlspecialchars($booking['customer_phone'] ?? '');
        $customerEmail = htmlspecialchars($booking['customer_email'] ?? '');
        $plotNo = htmlspecialchars($plot['plot_number'] ?? 'N/A');
        $block = htmlspecialchars($plot['block'] ?? '');
        $colonyName = htmlspecialchars($colony['name'] ?? 'N/A');
        $district = htmlspecialchars($colony['district_name'] ?? '');
        $bookingNo = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $bookingDate = date('d M Y', strtotime($booking['booking_date'] ?? 'now'));
        $totalValue = number_format((float)($booking['total_plot_value'] ?? 0), 2);
        $bookingAmt = number_format((float)($booking['booking_amount'] ?? 0), 2);
        $area = number_format((float)($plot['area_sqft'] ?? 0)) . ' sq ft';
        $dimLabel = $plot['dimension_label'] ?? (($plot['width_ft'] ?? 0) . ' x ' . ($plot['length_ft'] ?? 0) . ' ft');
        $today = date('d M Y');
        $channel = ucfirst(str_replace('_', ' ', $booking['channel'] ?? 'direct'));
        $status = ucfirst(str_replace('_', ' ', $booking['status'] ?? 'token_paid'));

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - ' . $bookingNo . '</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>' . self::cssBase() . self::cssPrint() . '
        .receipt-badge { display: inline-block; background: #059669; color: #fff; padding: 6px 20px; border-radius: 20px; font-weight: 700; font-size: 13px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="watermark">BOOKING RECEIPT</div>
    <div class="page">
        ' . self::companyHeader() . '
        <div class="style-9828">
            <span class="receipt-badge"><i class="fas fa-check-circle"></i> BOOKING CONFIRMATION RECEIPT</span>
        </div>

        <div class="info-grid">
            <div class="row"><span class="label">Booking No:</span> <span class="value">' . $bookingNo . '</span></div>
            <div class="row"><span class="label">Date:</span> <span class="value">' . $bookingDate . '</span></div>
            <div class="row"><span class="label">Status:</span> <span class="value">' . $status . '</span></div>
            <div class="row"><span class="label">Channel:</span> <span class="value">' . $channel . '</span></div>
        </div>

        <h3 class="section-title">Customer Details</h3>
        <div class="info-grid">
            <div class="row"><span class="label">Name:</span> <span class="value">' . $customerName . '</span></div>
            <div class="row"><span class="label">Phone:</span> <span class="value">' . $customerPhone . '</span></div>
            <div class="row"><span class="label">Email:</span> <span class="value">' . $customerEmail . '</span></div>
        </div>

        <h3 class="section-title">Plot Details</h3>
        <div class="info-grid">
            <div class="row"><span class="label">Colony:</span> <span class="value">' . $colonyName . '</span></div>
            <div class="row"><span class="label">District:</span> <span class="value">' . $district . '</span></div>
            <div class="row"><span class="label">Plot No:</span> <span class="value">' . $plotNo . ($block ? ' (Block ' . $block . ')' : '') . '</span></div>
            <div class="row"><span class="label">Area:</span> <span class="value">' . $area . '</span></div>
            <div class="row"><span class="label">Dimensions:</span> <span class="value">' . $dimLabel . '</span></div>
            <div class="row"><span class="label">Facing:</span> <span class="value">' . htmlspecialchars($plot['facing'] ?? 'N/A') . '</span></div>
        </div>

        <h3 class="section-title">Payment Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="amount">Amount (â‚¹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Plot Value</td>
                    <td class="amount">' . $totalValue . '</td>
                </tr>
                <tr>
                    <td>Token / Booking Amount Paid</td>
                    <td class="amount" class="style-7250">' . $bookingAmt . '</td>
                </tr>
                <tr class="total-row">
                    <td>Balance Amount</td>
                    <td class="amount">â‚¹' . number_format((float)($booking['total_plot_value'] ?? 0) - (float)($booking['booking_amount'] ?? 0), 2) . '</td>
                </tr>
            </tbody>
        </table>

        <div class="body-text" class="style-30245">
            <p><strong>Terms & Conditions:</strong></p>
            <ol class="style-97297">
                <li class="style-15049">This booking is subject to verification of customer credentials and KYC documents.</li>
                <li class="style-15049">The balance amount is payable as per the agreed EMI schedule.</li>
                <li class="style-15049">In case of cancellation, the booking amount is refundable as per the cancellation policy.</li>
                <li class="style-15049">Stamp duty, registration charges, and other statutory fees are additional and payable by the customer.</li>
                <li class="style-15049">Possession will be handed over as per the agreed timeline mentioned in the allotment letter.</li>
            </ol>
        </div>

        <div class="signature">
            <div class="signature-block">
                <div class="line">Customer Signature</div>
            </div>
            <div class="signature-block">
                <div class="line">Authorized Signatory<br><small>' . self::$companyName . '</small></div>
            </div>
        </div>

        <div class="footer">
            <p class="style-31100">
                This is a system-generated receipt. For queries, call <strong>' . self::$companyPhone . '</strong> or email <strong>' . self::$companyEmail . '</strong>
            </p>
        </div>

        <div class="no-print" class="style-18221">
            <button onclick="window.print()" class="style-37441">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</body>
</html>';
    }

    public static function generateNOC(array $booking, array $colony, array $plot, string $nocNumber): string
    {
        $customerName = htmlspecialchars($booking['customer_name'] ?? 'Customer');
        $customerPhone = htmlspecialchars($booking['customer_phone'] ?? '');
        $customerAddress = htmlspecialchars($booking['customer_address'] ?? '');
        $plotNo = htmlspecialchars($plot['plot_number'] ?? 'N/A');
        $block = htmlspecialchars($plot['block'] ?? '');
        $colonyName = htmlspecialchars($colony['name'] ?? 'N/A');
        $district = htmlspecialchars($colony['district_name'] ?? '');
        $bookingNo = htmlspecialchars($booking['booking_number'] ?? 'N/A');
        $totalValue = number_format((float)($booking['total_plot_value'] ?? 0), 2);
        $area = number_format((float)($plot['area_sqft'] ?? 0)) . ' sq ft';
        $today = date('d M Y');

        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOC - ' . $nocNumber . '</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>' . self::cssBase() . self::cssPrint() . '
        .noc-title { font-size: 22px; font-weight: 800; color: #1e293b; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; }
        .noc-subtitle { font-size: 13px; color: #6b7280; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="watermark">NO OBJECTION CERTIFICATE</div>
    <div class="page">
        ' . self::companyHeader() . '
        <div class="noc-header">
            <div class="stamp-box"><span>No Objection<br>Certificate</span></div>
            <div class="noc-title">No Objection Certificate</div>
            <div class="noc-subtitle">For Transfer / Registration of Plot</div>
            <div class="noc-number">NOC No: ' . $nocNumber . '</div>
        </div>

        <div class="style-38798">
            <strong>Date:</strong> ' . $today . '
        </div>

        <div class="body-text">
            <p>To Whom It May Concern,</p>
            <p>This No Objection Certificate is issued to <strong>' . $customerName . '</strong> 
            (Phone: ' . $customerPhone . ') in respect of the following property:</p>
        </div>

        <h3 class="section-title">Property Details</h3>
        <div class="info-grid">
            <div class="row"><span class="label">Colony:</span> <span class="value">' . $colonyName . '</span></div>
            <div class="row"><span class="label">District:</span> <span class="value">' . $district . '</span></div>
            <div class="row"><span class="label">Plot No:</span> <span class="value">' . $plotNo . ($block ? ' (Block ' . $block . ')' : '') . '</span></div>
            <div class="row"><span class="label">Area:</span> <span class="value">' . $area . '</span></div>
            <div class="row"><span class="label">Booking Ref:</span> <span class="value">' . $bookingNo . '</span></div>
            <div class="row"><span class="label">Total Value:</span> <span class="value">â‚¹' . $totalValue . '</span></div>
        </div>

        <div class="body-text">
            <p>' . self::$companyName . ' hereby certifies that it has no objection to the registration and/or transfer of the above-mentioned plot in favor of the said customer, subject to:</p>
            <ol class="style-97297">
                <li class="style-51016">All outstanding dues against the said plot have been cleared.</li>
                <li class="style-51016">All terms and conditions of the original booking agreement have been fulfilled.</li>
                <li class="style-51016">The customer has provided all required KYC and legal documents.</li>
                <li class="style-51016">Stamp duty and registration charges have been deposited as applicable.</li>
                <li class="style-51016">This NOC is valid for a period of 30 days from the date of issuance.</li>
            </ol>
        </div>

        <div class="signature">
            <div class="signature-block">
                <div class="line">Customer Signature</div>
            </div>
            <div class="signature-block">
                <div class="line">Managing Director<br><small>' . self::$companyName . '</small></div>
            </div>
        </div>

        <div class="footer">
            <p class="style-31100">
                ' . self::$companyName . ' | ' . self::$companyAddress . '<br>
                Ph: ' . self::$companyPhone . ' | Email: ' . self::$companyEmail . '
            </p>
        </div>

        <div class="no-print" class="style-18221">
            <button onclick="window.print()" class="style-37441">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</body>
</html>';
    }
}
