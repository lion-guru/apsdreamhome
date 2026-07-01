<?php
$page_title = 'Possession Letter';
$active_page = 'possession';

$booking = $booking ?? [];
$customerName = htmlspecialchars($booking['customer_name'] ?? '');
$customerAddress = htmlspecialchars($booking['customer_email'] ?? '') . ' / ' . htmlspecialchars($booking['customer_phone'] ?? '');
$propertyTitle = htmlspecialchars($booking['property_title'] ?? '');
$propertyLocation = htmlspecialchars($booking['property_location'] ?? '');
$plotNumber = htmlspecialchars($booking['plot_number'] ?? 'N/A');
$colonyName = htmlspecialchars($booking['colony_name'] ?? '');
$areaSqft = htmlspecialchars(number_format($booking['area_sqft'] ?? 0));
$width = htmlspecialchars($booking['width'] ?? '--');
$length = htmlspecialchars($booking['length'] ?? '--');
$bookingNumber = htmlspecialchars($booking['booking_number'] ?? '');
$letterNumber = htmlspecialchars($booking['possession_letter_number'] ?? 'POSS-' . date('Y') . '-' . str_pad($booking['id'] ?? 0, 5, '0', STR_PAD_LEFT));
$possessionDate = !empty($booking['possession_date']) ? htmlspecialchars(date('d F Y', strtotime($booking['possession_date']))) : date('d F Y');
$handoverByName = htmlspecialchars($booking['handover_by_name'] ?? 'Authorized Signatory');
$defectPeriod = intval($booking['defect_liability_period'] ?? 365);
$defectEndDate = !empty($booking['defect_liability_end_date']) ? htmlspecialchars(date('d F Y', strtotime($booking['defect_liability_end_date']))) : date('d F Y', strtotime('+' . $defectPeriod . ' days'));
$today = date('d F Y');
$price = htmlspecialchars(number_format(floatval($booking['property_price'] ?? 0), 2));
?>
<style>
    body { font-family: 'Georgia', 'Times New Roman', serif; background: #fff; }
    .letter-wrapper { max-width: 800px; margin: 0 auto; border: 2px solid #1a5276; padding: 40px; }
    .letter-header { text-align: center; border-bottom: 2px solid #1a5276; padding-bottom: 15px; margin-bottom: 20px; }
    .letter-header h1 { color: #1a5276; font-size: 22pt; margin-bottom: 0; }
    .letter-header h2 { color: #2e86c1; font-size: 13pt; margin-top: 5px; font-weight: normal; }
    .letter-header .letter-no { font-size: 10pt; color: #666; margin-top: 5px; }
    .date-line { text-align: right; font-size: 11pt; margin-bottom: 20px; }
    .letter-content { margin: 20px 0; }
    .letter-content p { font-size: 11pt; line-height: 1.8; text-align: justify; }
    .letter-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .letter-table th, .letter-table td { border: 1px solid #999; padding: 8px 12px; text-align: left; font-size: 10pt; }
    .letter-table th { background-color: #eaf2f8; width: 40%; }
    .terms-box { margin: 20px 0; padding: 15px; background: #fdf2e9; border-left: 4px solid #e67e22; }
    .terms-box h4 { color: #e67e22; margin-top: 0; }
    .terms-box ul { margin-bottom: 0; font-size: 10pt; line-height: 1.8; }
    .signatures { margin-top: 40px; }
    .signatures table { width: 100%; }
    .signatures td { width: 50%; text-align: center; padding-top: 40px; font-size: 10pt; }
    .signature-line { border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px; }
    .letter-footer { margin-top: 30px; text-align: center; font-size: 9pt; color: #888; border-top: 1px solid #ccc; padding-top: 10px; }
    @media print { .no-print { display: none; } }
</style>

<div class="no-print mb-3">
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print / Save PDF</button>
    <a href="<?= BASE_URL ?>/admin/possession/show/<?= $booking['id'] ?? 0 ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="letter-wrapper">
    <div class="letter-header">
        <h1>POSSESSION HANDOVER LETTER</h1>
        <h2>APS Dream Home - Property Possession Certificate</h2>
        <div class="letter-no">Letter No: <strong><?= $letterNumber ?></strong></div>
    </div>

    <div class="date-line">Date: <?= $today ?></div>

    <div class="letter-content">
        <p>To,</p>
        <p><strong><?= $customerName ?></strong><br><?= $customerAddress ?></p>

        <p><strong>Subject: Possession Handover of Property at <?= $colonyName ?></strong></p>

        <p>Dear <?= $customerName ?>,</p>

        <p>Congratulations! We are pleased to inform you that the possession of the property booked by you under <strong>Booking #<?= $bookingNumber ?></strong> is hereby handed over to you with effect from <strong><?= $possessionDate ?></strong>.</p>

        <p>The property has been inspected and is found to be in complete accordance with the agreed specifications and approved layout plans. All amenities and utilities as per the agreement have been provided.</p>

        <div class="table-responsive"><table class="letter-table">
            <tr><th>Booking Number</th><td><?= $bookingNumber ?></td></tr>
            <tr><th>Property Title</th><td><?= $propertyTitle ?></td></tr>
            <tr><th>Location</th><td><?= $propertyLocation ?></td></tr>
            <tr><th>Colony</th><td><?= $colonyName ?></td></tr>
            <tr><th>Plot Number</th><td><?= $plotNumber ?></td></tr>
            <tr><th>Plot Dimensions</th><td><?= $width ?> ft x <?= $length ?> ft</td></tr>
            <tr><th>Area</th><td><?= $areaSqft ?> sq. ft.</td></tr>
            <tr><th>Total Consideration</th><td>&#8377; <?= $price ?></td></tr>
            <tr><th>Possession Date</th><td><?= $possessionDate ?></td></tr>
            <tr><th>Handover By</th><td><?= $handoverByName ?></td></tr>
            <tr><th>Defect Liability Period</th><td><?= $defectPeriod ?> days (until <?= $defectEndDate ?>)</td></tr>
        </table></div>

        <div class="terms-box">
            <h4>Terms &amp; Conditions</h4>
            <ul>
                <li>The allottee has inspected the property and is satisfied with its condition and specifications.</li>
                <li>Any structural or material defects reported within <?= $defectPeriod ?> days (<?= $defectEndDate ?>) from the date of possession will be rectified by the company.</li>
                <li>Defects caused by normal wear and tear, misuse, or unauthorized alterations are not covered.</li>
                <li>The allottee shall maintain the property in good condition and comply with all colony/housing society rules.</li>
                <li>Property tax and all other applicable charges from the date of possession are the responsibility of the allottee.</li>
                <li>This possession does not constitute transfer of ownership; the same shall be effected through a registered Sale Deed.</li>
            </ul>
        </div>

        <p>We thank you for choosing APS Dream Home as your trusted real estate partner and wish you a wonderful experience in your new property.</p>
    </div>

    <div class="signatures">
        <div class="table-responsive"><table>
            <tr>
                <td>
                    <div class="signature-line"><?= $handoverByName ?></div>
                    <div style="font-size:9pt; color:#666;">Authorized Signatory<br>APS Dream Home</div>
                </td>
                <td>
                    <div class="signature-line"><?= $customerName ?></div>
                    <div style="font-size:9pt; color:#666;">Allottee / Buyer</div>
                </td>
            </tr>
        </table></div>
    </div>

    <div class="letter-footer">
        This letter is electronically generated and does not require a physical signature.<br>
        APS Dream Home &bull; Generated on: <?= $today ?> &bull; Letter #<?= $letterNumber ?>
    </div>
</div>
