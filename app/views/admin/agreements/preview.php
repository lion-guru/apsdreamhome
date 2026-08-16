<?php
/**
 * HTML Preview Template for Agreement Generation
 * This file is included by AgreementGenerationService::getHtmlPreview()
 * Variables available: $data, $companyName, $companyAddress, $companyPhone, $companyEmail, $title, $type
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? '') ?></title>
    <style>
        body { font-family: 'Georgia', 'Times New Roman', serif; font-size: 12pt; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 3px solid #c8a01e; padding-bottom: 10px; }
        .header .company-name { font-size: 18pt; font-weight: bold; color: #0d9488; }
        .header .company-details { font-size: 9pt; color: #666; margin-top: 3px; }
        .header .doc-title { font-size: 16pt; font-weight: bold; color: #c8a01e; margin-top: 8px; letter-spacing: 2px; }
        .header .doc-meta { font-size: 9pt; color: #666; margin-top: 5px; }
        .section-title { background: #c8a01e; color: #fff; padding: 4px 10px; font-size: 11pt; font-weight: bold; margin: 15px 0 8px 0; }
        table.details { width: 100%; font-size: 10pt; border-collapse: collapse; }
        table.details td { padding: 3px 5px; vertical-align: top; }
        table.details .label { font-weight: bold; width: 120px; color: #555; }
        table.payments, table.installments { width: 100%; font-size: 9pt; border-collapse: collapse; margin: 8px 0; }
        table.payments th, table.installments th { background: #e0e0e0; padding: 4px 6px; text-align: center; border: 1px solid #ccc; font-size: 9pt; }
        table.payments td, table.installments td { padding: 4px 6px; border: 1px solid #ddd; text-align: center; }
        table.payments tr:nth-child(even), table.installments tr:nth-child(even) { background: #f9f9f9; }
        .terms { font-size: 9pt; margin: 10px 0; }
        .terms p { margin: 2px 0; padding-left: 15px; text-indent: -15px; }
        .signatures { margin-top: 15px; }
        .signatures table { width: 100%; font-size: 10pt; }
        .signatures td { padding: 5px; text-align: center; width: 50%; }
        .signatures .line { border-top: 1px solid #333; width: 80%; margin: 20px auto 3px auto; }
        .signatures .label { font-weight: bold; font-size: 9pt; color: #555; }
        .footer { font-size: 8pt; color: #999; text-align: center; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 5px; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 8pt; border-radius: 3px; }
        .badge-success { background: #4caf50; color: #fff; }
        .badge-secondary { background: #9e9e9e; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name"><?= htmlspecialchars($companyName ?? '') ?></div>
        <div class="company-details">
            <?= htmlspecialchars($companyAddress ?? '') ?><br>
            Phone: <?= htmlspecialchars($companyPhone ?? '') ?> | Email: <?= htmlspecialchars($companyEmail ?? '') ?>
        </div>
        <div class="doc-title"><?= htmlspecialchars($title ?? '') ?></div>
        <div class="doc-meta">
            Date: <?= date('d/m/Y') ?>
        </div>
    </div>

    <div class="section-title">PARTY DETAILS</div>
    <div class="table-responsive"><table class="details">
        <tr>
            <td class="label">Buyer Name:</td>
            <td><?= htmlspecialchars($data['customer_name'] ?? 'N/A') ?></td>
            <td class="label">Company:</td>
            <td><?= htmlspecialchars($companyName ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td><?= htmlspecialchars($data['customer_address'] ?? 'N/A') ?></td>
            <td class="label">Address:</td>
            <td><?= htmlspecialchars($companyAddress ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Phone:</td>
            <td><?= htmlspecialchars($data['customer_phone'] ?? 'N/A') ?></td>
            <td class="label">Phone:</td>
            <td><?= htmlspecialchars($companyPhone ?? '') ?></td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td><?= htmlspecialchars($data['customer_email'] ?? 'N/A') ?></td>
            <td class="label">Email:</td>
            <td><?= htmlspecialchars($companyEmail ?? '') ?></td>
        </tr>
    </table></div>

    <div class="section-title">PROPERTY DETAILS</div>
    <div class="table-responsive"><table class="details">
        <tr>
            <td class="label">Plot No:</td>
            <td><?= htmlspecialchars($data['plot_number'] ?? 'N/A') ?></td>
            <td class="label">Block:</td>
            <td><?= htmlspecialchars($data['block'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td class="label">Sector:</td>
            <td><?= htmlspecialchars($data['sector'] ?? 'N/A') ?></td>
            <td class="label">Colony:</td>
            <td><?= htmlspecialchars($data['colony_name'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td class="label">Area:</td>
            <td><?= number_format(floatval($data['area_sqft'] ?? 0), 2) ?> sq.ft.</td>
            <td class="label">Dimension:</td>
            <td>
                <?php 
                $dim = $data['dimension_label'] ?? '';
                if (empty($dim) && ($data['width_ft'] ?? 0) > 0 && ($data['length_ft'] ?? 0) > 0) {
                    $dim = $data['width_ft'] . 'x' . $data['length_ft'];
                }
                echo htmlspecialchars($dim ?: 'N/A');
                ?>
            </td>
        </tr>
        <tr>
            <td class="label">Facing:</td>
            <td><?= htmlspecialchars($data['facing'] ?? 'N/A') ?></td>
            <td class="label"></td>
            <td></td>
        </tr>
    </table></div>

    <div class="section-title">PAYMENT SUMMARY</div>
    <div class="table-responsive"><table class="details">
        <tr>
            <td class="label">Total Price:</td>
            <td>Rs. <?= number_format(floatval($data['total_amount'] ?? $data['total_price'] ?? 0), 2) ?></td>
        </tr>
        <?php if (!empty($data['negotiated_price'])): ?>
        <tr>
            <td class="label">Negotiated Price:</td>
            <td>Rs. <?= number_format(floatval($data['negotiated_price']), 2) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">Booking Amount:</td>
            <td>Rs. <?= number_format(floatval($data['booking_amount'] ?? 0), 2) ?></td>
        </tr>
        <tr>
            <td class="label">Total Paid:</td>
            <td>Rs. <?= number_format(floatval($data['total_paid'] ?? 0), 2) ?></td>
        </tr>
    </table></div>

    <?php if (!empty($data['payments'])): ?>
    <div class="section-title">PAYMENT HISTORY</div>
    <div class="table-responsive"><table class="payments">
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Method</th>
            <th>Transaction ID</th>
            <th>Amount</th>
        </tr>
        <?php $pi = 1; ?>
        <?php foreach ($data['payments'] as $pmt): ?>
        <tr>
            <td><?= $pi++ ?></td>
            <td><?= date('d/m/Y', strtotime($pmt['payment_date'])) ?></td>
            <td><?= ucwords(str_replace('_', ' ', $pmt['payment_method'] ?? 'N/A')) ?></td>
            <td><?= htmlspecialchars($pmt['transaction_id'] ?? 'N/A') ?></td>
            <td>Rs. <?= number_format(floatval($pmt['payment_amount'] ?? 0), 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table></div>
    <?php endif; ?>

    <?php if ($type === 'payment_plan'): ?>
    <div class="section-title">INSTALLMENT SCHEDULE</div>
    <?php
    $totalPriceVal = floatval($data['total_amount'] ?? $data['total_price'] ?? 0);
    $bookingAmountVal = floatval($data['booking_amount'] ?? 0);
    $remainingAmount = $totalPriceVal - $bookingAmountVal;
    $numInstallments = max(1, ceil($remainingAmount / max(1, $totalPriceVal * 0.2)));
    $installmentAmount = $remainingAmount / max(1, $numInstallments);
    ?>
    <div class="table-responsive"><table class="installments">
        <tr>
            <th>#</th>
            <th>Installment Type</th>
            <th>Due Date</th>
            <th>Amount</th>
            <th>Remarks</th>
        </tr>
        <tr>
            <td>0</td>
            <td>Booking Amount</td>
            <td><?= date('d/m/Y') ?></td>
            <td>Rs. <?= number_format($bookingAmountVal, 2) ?></td>
            <td>At time of booking</td>
        </tr>
        <?php for ($ii = 1; $ii <= $numInstallments; $ii++): ?>
        <tr>
            <td><?= $ii ?></td>
            <td><?= ($ii < $numInstallments) ? "Installment $ii" : 'Final Payment' ?></td>
            <td><?= date('d/m/Y', strtotime("+$ii months")) ?></td>
            <td>Rs. <?= number_format($installmentAmount, 2) ?></td>
            <td><?= ($ii < $numInstallments) ? 'Monthly' : 'On possession' ?></td>
        </tr>
        <?php endfor; ?>
        <tr class="style-19077">
            <td></td>
            <td>Total</td>
            <td></td>
            <td>Rs. <?= number_format($totalPriceVal, 2) ?></td>
            <td></td>
        </tr>
    </table></div>
    <?php endif; ?>

    <div class="section-title">TERMS AND CONDITIONS</div>
    <div class="terms">
        <?php
        $terms = [
            'The property is sold on an "as is where is" basis and the buyer has verified all details.',
            'The buyer agrees to pay the total consideration as per the payment schedule.',
            'Possession will be handed over after full payment and execution of the Sale Deed.',
            'The company reserves the right to cancel the allotment if payments are not made on time.',
            'All disputes shall be subject to the jurisdiction of Gorakhpur, Uttar Pradesh courts.',
            'The buyer shall bear all applicable taxes, registration charges, and stamp duty.',
        ];
        if ($type === 'allotment') {
            $terms = array_merge(['This allotment is provisional and subject to receipt of full payment.', 'The allottee must execute the Sale Agreement within 30 days of this allotment letter.'], $terms);
        }
        if ($type === 'sale_agreement') {
            $terms = array_merge(['This Agreement is executed on the terms and conditions mentioned herein.', 'The Seller agrees to sell and the Buyer agrees to purchase the said property.'], $terms);
        }
        if ($type === 'payment_plan') {
            $terms = ['The payment schedule is tentative and subject to change with mutual consent.', 'All payments must be made via cheque, bank transfer, or online payment.', 'Delayed payments will attract interest at 12% per annum on the outstanding amount.', 'The buyer can prepay any installment without penalty.', 'Taxes and registration charges are payable in addition to the plot price.'];
        }
        foreach ($terms as $ti => $term): ?>
            <p><?= ($ti + 1) ?>. <?= htmlspecialchars($term ?? '') ?></p>
        <?php endforeach; ?>
    </div>

    <div class="section-title">SIGNATURES</div>
    <div class="signatures">
        <div class="table-responsive"><table>
            <tr>
                <td>
                    <div class="style-8484">
                        <div class="line"></div>
                        <div class="label">BUYER</div>
                        <div><?= htmlspecialchars($data['customer_name'] ?? '___________________') ?></div>
                    </div>
                </td>
                <td>
                    <div class="style-8484">
                        <div class="line"></div>
                        <div class="label">SELLER (Authorized Signatory)</div>
                        <div>For <?= htmlspecialchars($companyName ?? '') ?></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="style-8484">
                        <div class="line"></div>
                        <div class="label">WITNESS 1</div>
                    </div>
                </td>
                <td>
                    <div class="style-8484">
                        <div class="line"></div>
                        <div class="label">WITNESS 2</div>
                    </div>
                </td>
            </tr>
        </table></div>
    </div>

    <div class="footer">
        This is a computer-generated document and does not require a physical signature.<br>
        <?= htmlspecialchars($companyName ?? '') ?> | <?= htmlspecialchars($companyAddress ?? '') ?><br>
        Generated on: <?= date('d/m/Y H:i:s') ?>
    </div>
</body>
</html>
