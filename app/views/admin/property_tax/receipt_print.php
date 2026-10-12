<?php
$base = rtrim(BASE_URL, '/');
$payment = $payment ?? [];
$master = $master ?? [];
$customer = $customer ?? null;
$plot = $plot ?? null;
$today = date('d F Y');
$todayShort = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Tax Receipt - <?= htmlspecialchars($payment['receipt_number'] ?? '') ?></title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <style>
        @page { size: A4; margin: 20mm; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .receipt-container { box-shadow: none; border: 1px solid #000; }
        }
        body { font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .receipt-container { max-width: 800px; margin: 0 auto; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border: 1px solid #ddd; }
        .header { background: linear-gradient(135deg, #6B4EE6 0%, #8B5CF6 100%); color: white; padding: 30px; text-align: center; position: relative; }
        .header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></svg>') center/cover; opacity: 0.3; }
        .header-content { position: relative; z-index: 1; }
        .company-name { font-size: 28px; font-weight: 700; margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .company-tagline { font-size: 14px; opacity: 0.9; margin-bottom: 15px; }
        .receipt-title { font-size: 22px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; }
        .receipt-number { font-size: 18px; font-weight: 500; opacity: 0.95; margin-top: 5px; }
        .body { padding: 30px; }
        .section { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .section:last-of-type { border-bottom: none; }
        .section-title { font-size: 14px; font-weight: 600; color: #6B4EE6; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .section-title i { font-size: 16px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-size: 12px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .info-value { font-size: 15px; font-weight: 500; color: #212529; }
        .amount-breakdown { background: #f8f9fa; border-radius: 8px; padding: 20px; margin-top: 15px; }
        .amount-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6; }
        .amount-row:last-child { border-bottom: none; }
        .amount-row.total { font-weight: 700; font-size: 18px; color: #6B4EE6; border-top: 2px solid #6B4EE6; margin-top: 10px; padding-top: 15px; }
        .amount-label { color: #495057; }
        .amount-value { font-weight: 600; }
        .amount-value.positive { color: #28a745; }
        .amount-value.negative { color: #dc3545; }
        .footer { background: #f8f9fa; padding: 20px 30px; border-top: 1px solid #eee; text-align: center; color: #6c757d; font-size: 13px; }
        .watermark { position: fixed; bottom: 50px; right: 50px; font-size: 120px; color: rgba(107, 78, 230, 0.03); font-weight: 700; transform: rotate(-15deg); pointer-events: none; z-index: -1; }
        .qr-section { text-align: center; margin-top: 20px; }
        .qr-section img { max-width: 120px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
        .sig-block { text-align: center; }
        .sig-line { border-top: 1px solid #333; width: 100%; margin-top: 50px; padding-top: 8px; font-size: 13px; color: #666; }
        .verified-badge { display: inline-flex; align-items: center; gap: 6px; background: #d4edda; color: #155724; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 13px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="watermark">APS</div>

<div class="receipt-container">
    <div class="header">
        <div class="header-content">
            <div class="company-name">APS DREAM HOME</div>
            <div class="company-tagline">Real Estate & Property Management</div>
            <div class="receipt-title"><i class="fas fa-receipt me-2"></i>Property Tax Payment Receipt</div>
            <div class="receipt-number">Receipt No: <?= htmlspecialchars($payment['receipt_number'] ?? 'N/A') ?></div>
        </div>
    </div>

    <div class="body">
        <div class="section">
            <div class="section-title"><i class="fas fa-building me-1"></i>Tax Assessment Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Tax Authority</span>
                    <span class="info-value"><?= htmlspecialchars($master['tax_authority'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Assessment Year</span>
                    <span class="info-value"><?= htmlspecialchars($master['assessment_year'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tax Type</span>
                    <span class="info-value"><?= ucwords(str_replace('_', ' ', $master['tax_type'] ?? '')) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ward / Zone</span>
                    <span class="info-value"><?= htmlspecialchars($master['ward_zone'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Property Category</span>
                    <span class="info-value"><?= ucfirst($master['property_category'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Demand Notice #</span>
                    <span class="info-value"><?= htmlspecialchars($master['demand_notice_number'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Due Date</span>
                    <span class="info-value"><?= $master['due_date'] ? date('d F Y', strtotime($master['due_date'])) : 'N/A' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value"><?= ucfirst(str_replace('_', ' ', $master['status'] ?? '')) ?></span>
                </div>
            </div>
        </div>

        <?php if ($plot): ?>
        <div class="section">
            <div class="section-title"><i class="fas fa-map-marker-alt me-1"></i>Property Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Plot Number</span>
                    <span class="info-value"><?= htmlspecialchars($plot['plot_number'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Colony</span>
                    <span class="info-value"><?= htmlspecialchars($plot['colony_name'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Area</span>
                    <span class="info-value"><?= $plot['area_sqft'] ? number_format($plot['area_sqft']) . ' sq ft' : 'N/A' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Facing</span>
                    <span class="info-value"><?= htmlspecialchars($plot['facing'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($customer): ?>
        <div class="section">
            <div class="section-title"><i class="fas fa-user me-1"></i>Payer Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value"><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($customer['email'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Address</span>
                    <span class="info-value"><?= htmlspecialchars($customer['address'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-title"><i class="fas fa-money-bill-wave me-1"></i>Payment Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Payment Date</span>
                    <span class="info-value"><?= $payment['payment_date'] ? date('d F Y', strtotime($payment['payment_date'])) : 'N/A' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Mode</span>
                    <span class="info-value"><?= ucfirst($payment['payment_mode'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Reference / Cheque No</span>
                    <span class="info-value"><?= htmlspecialchars($payment['cheque_dd_number'] ?? $payment['transaction_ref'] ?? $payment['upi_ref'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Bank</span>
                    <span class="info-value"><?= htmlspecialchars($payment['bank_name'] ?? 'N/A') ?><?= $payment['bank_branch'] ? ' - ' . htmlspecialchars($payment['bank_branch']) : '' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Receipt Date</span>
                    <span class="info-value"><?= $payment['receipt_date'] ? date('d F Y', strtotime($payment['receipt_date'])) : $today ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="badge bg-<?= $payment['status'] === 'verified' ? 'success' : 'warning' ?>">
                            <?= ucfirst($payment['status'] ?? '') ?>
                        </span>
                        <?php if ($payment['status'] === 'verified'): ?>
                            <span class="verified-badge ms-2"><i class="fas fa-check-circle"></i> Verified</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="amount-breakdown">
                <div class="amount-row">
                    <span class="amount-label">Base Tax Amount</span>
                    <span class="amount-value">₹<?= number_format($payment['base_tax_paid'] ?? 0, 2) ?></span>
                </div>
                <div class="amount-row">
                    <span class="amount-label">Penalty / Interest</span>
                    <span class="amount-value positive">+ ₹<?= number_format(($payment['penalty_paid'] ?? 0) + ($payment['interest_paid'] ?? 0), 2) ?></span>
                </div>
                <div class="amount-row">
                    <span class="amount-label">Rebate Availed</span>
                    <span class="amount-value negative">- ₹<?= number_format($payment['rebate_availed'] ?? 0, 2) ?></span>
                </div>
                <div class="amount-row total">
                    <span class="amount-label">Total Amount Paid</span>
                    <span class="amount-value">₹<?= number_format($payment['total_paid'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line">Authorized Signatory<br><small>APS Dream Home</small></div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Payer Signature<br><small><?= htmlspecialchars($customer['name'] ?? '____________________') ?></small></div>
            </div>
        </div>

        <div class="qr-section">
            <div class="small text-muted">Scan to verify receipt authenticity</div>
            <svg width="120" height="120" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                <rect width="120" height="120" fill="white"/>
                <rect x="10" y="10" width="100" height="100" fill="none" stroke="#000" stroke-width="2"/>
                <rect x="15" y="15" width="20" height="20" fill="#000"/>
                <rect x="85" y="15" width="20" height="20" fill="#000"/>
                <rect x="15" y="85" width="20" height="20" fill="#000"/>
                <rect x="35" y="35" width="50" height="50" fill="none" stroke="#000" stroke-width="1"/>
                <text x="60" y="115" text-anchor="middle" font-size="10" fill="#666">QR CODE</text>
            </svg>
            <div class="small text-muted mt-2">Verify at: <?= $base ?>/verify/receipt/<?= htmlspecialchars($payment['receipt_number'] ?? '') ?></div>
        </div>
    </div>

    <div class="footer">
        <div class="d-flex justify-content-center gap-4 flex-wrap mb-2">
            <span><i class="fas fa-phone me-1"></i> +91-XXXXXXXXXX</span>
            <span><i class="fas fa-envelope me-1"></i> info@apsdreamhome.com</span>
            <span><i class="fas fa-globe me-1"></i> www.apsdreamhome.com</span>
            <span><i class="fas fa-map-marker-alt me-1"></i> Gorakhpur, Uttar Pradesh</span>
        </div>
        <div class="small">GSTIN: 09XXXXXXXXXXXXX | CIN: U70100UP2020PTCXXXXXX</div>
        <div class="small mt-1">Generated on <?= $today ?> | This is a computer-generated receipt and does not require physical signature if verified online.</div>
    </div>
</div>

<div class="text-center mt-4 no-print">
    <button class="btn btn-primary btn-lg" onclick="window.print()"><i class="fas fa-print me-2"></i>Print Receipt</button>
    <a href="<?= $base ?>/admin/property-tax/payments/<?= $master['id'] ?? '' ?>" class="btn btn-outline-secondary btn-lg ms-2"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
window.addEventListener('load', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print') === '1') window.print();
});
</script>
</body>
</html>