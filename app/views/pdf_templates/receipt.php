<?php
// Receipt PDF Template
$companyName = $data['company_name'] ?? 'APS Dream Home';
$receiptNumber = $data['receipt_number'] ?? 'N/A';
$date = $data['date'] ?? date('d M Y');
$customerName = $data['customer_name'] ?? 'N/A';
$amount = $data['amount'] ?? 0;
$amountWords = $data['amount_words'] ?? '';
$paymentMode = $data['payment_mode'] ?? 'Cash';
$transactionRef = $data['transaction_ref'] ?? 'N/A';
$plotNumber = $data['plot_number'] ?? 'N/A';
$colonyName = $data['colony_name'] ?? 'N/A';
$purpose = $data['purpose'] ?? 'Payment';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - <?= htmlspecialchars($receiptNumber) ?></title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .receipt-container { max-width: 600px; margin: 0 auto; border: 2px solid #0B194C; border-radius: 10px; padding: 30px; }
        .header { text-align: center; border-bottom: 3px solid #D4AF37; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { width: 80px; height: 80px; background: linear-gradient(135deg, #0B194C, #1E3A8A); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: #D4AF37; font-size: 24px; font-weight: bold; }
        .company-name { font-size: 24px; font-weight: 800; color: #0B194C; margin-bottom: 5px; }
        .company-tagline { color: #666; font-size: 12px; }
        .receipt-title { font-size: 18px; font-weight: 700; color: #D4AF37; margin: 20px 0; text-transform: uppercase; letter-spacing: 2px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .info-item { background: #FAF7F0; padding: 15px; border-radius: 8px; border: 1px solid #E8DFD0; }
        .info-label { font-size: 11px; color: #888; text-transform: uppercase; font-weight: 600; margin-bottom: 5px; }
        .info-value { font-size: 14px; font-weight: 600; color: #0B194C; }
        .amount-section { background: linear-gradient(135deg, #0B194C, #1E3A8A); color: white; padding: 25px; border-radius: 10px; text-align: center; margin: 25px 0; }
        .amount-label { font-size: 13px; color: #D4AF37; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .amount-value { font-size: 36px; font-weight: 800; color: #D4AF37; }
        .amount-words { font-size: 13px; color: #E8DFD0; font-style: italic; margin-top: 10px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #E8DFD0; color: #888; font-size: 11px; }
        .seal { display: inline-block; margin-top: 20px; }
        .seal img { width: 80px; height: 80px; }
        @media print { body { padding: 0; } .receipt-container { border: none; box-shadow: none; } }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="logo">APS</div>
            <div class="company-name"><?= htmlspecialchars($companyName) ?></div>
            <div class="company-tagline">Premium Plots & Townships • Gorakhpur</div>
        </div>
        
        <div class="receipt-title">Official Payment Receipt</div>
        
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Receipt Number</div>
                <div class="info-value"><?= htmlspecialchars($receiptNumber) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Date</div>
                <div class="info-value"><?= htmlspecialchars($date) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Customer Name</div>
                <div class="info-value"><?= htmlspecialchars($customerName) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Payment Mode</div>
                <div class="info-value"><?= htmlspecialchars($paymentMode) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Transaction Ref</div>
                <div class="info-value"><?= htmlspecialchars($transactionRef) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Plot / Colony</div>
                <div class="info-value"><?= htmlspecialchars($plotNumber) ?> / <?= htmlspecialchars($colonyName) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Purpose</div>
                <div class="info-value"><?= htmlspecialchars($purpose) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value" style="color: #10B981;">✓ COMPLETED</div>
            </div>
        </div>
        
        <div class="amount-section">
            <div class="amount-label">Amount Paid</div>
            <div class="amount-value">₹<?= number_format($amount, 2) ?></div>
            <?php if ($amountWords): ?>
                <div class="amount-words">(<?= htmlspecialchars($amountWords) ?> Only)</div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p><strong><?= htmlspecialchars($companyName) ?></strong> - Premium Plots & Townships in Gorakhpur</p>
            <p>RERA Registered | ISO 9001:2015 Certified</p>
            <p>Website: www.apsdreamhomes.co.in | Support: +91 7007444842</p>
            <div class="seal">
                <svg width="80" height="80" viewBox="0 0 80 80" style="border: 2px solid #D4AF37; border-radius: 50%; padding: 5px;">
                    <circle cx="40" cy="40" r="35" stroke="#D4AF37" stroke-width="2" fill="none"/>
                    <text x="40" y="45" text-anchor="middle" font-family="serif" font-size="18" font-weight="bold" fill="#D4AF37">APS</text>
                    <text x="40" y="62" text-anchor="middle" font-family="sans-serif" font-size="7" fill="#666">AUTHORIZED</text>
                </svg>
            </div>
            <p style="margin-top: 15px; font-size: 10px; color: #999;">This is a computer-generated receipt. No signature required.</p>
        </div>
    </div>
</body>
</html>