<?php
/** @var array $me */
/** @var array $payouts */
$me = $me ?? []; $payouts = $payouts ?? [];
$fy = $fy ?? ''; $tan = $tan ?? '—';
$gross = $gross ?? 0; $tds = $tds ?? 0; $net = $net ?? 0; $rate = $rate ?? 5;
$base = defined('BASE_URL') ? BASE_URL : '';
$certNo = 'TDS16A-' . date('Ymd') . '-' . ($_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TDS Certificate FY <?= htmlspecialchars($fy) ?></title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        @page { size: A4; margin: 14mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11pt; color: #1a1a1a; }
        .cert-head { text-align: center; border-bottom: 3px solid #0d6efd; padding-bottom: 12px; margin-bottom: 18px; }
        .cert-title { font-size: 20px; font-weight: 700; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info-table th, .info-table td { border: 1px solid #dee2e6; padding: 7px 10px; font-size: 10.5pt; text-align: left; }
        .info-table th { background: #f1f5f9; width: 32%; }
        .sec-title { font-size: 13px; font-weight: 700; color: #0d6efd; margin: 16px 0 8px; text-transform: uppercase; letter-spacing: .4px; }
        .sig-line { border-top: 1px solid #333; width: 220px; margin-top: 46px; padding-top: 4px; font-size: 10pt; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="cert-head">
        <div class="cert-title">APS Dream Home</div>
        <div class="text-muted small">Real Estate &amp; Developers, Gorakhpur (UP) · GSTIN: 09AABCA1234A1Z5</div>
        <h5 class="mt-2 mb-0">TDS Deduction Certificate (Form 16A style) — FY <?= htmlspecialchars($fy) ?></h5>
        <small class="text-muted">Certificate No: <?= htmlspecialchars($certNo) ?> · Generated: <?= date('d M Y') ?></small>
    </div>

    <div class="sec-title">Deductor (Company)</div>
    <table class="info-table">
        <tr><th>Name</th><td>APS Dream Home</td></tr>
        <tr><th>TAN</th><td><?= htmlspecialchars($tan) ?></td></tr>
        <tr><th>Section</th><td>194H — Commission / Brokerage @ <?= htmlspecialchars((string)$rate) ?>%</td></tr>
    </table>

    <div class="sec-title">Deductee (Associate)</div>
    <table class="info-table">
        <tr><th>Name</th><td><?= htmlspecialchars($me['name'] ?? '') ?></td></tr>
        <tr><th>Associate Code</th><td><?= htmlspecialchars($me['referral_code'] ?? '') ?></td></tr>
        <tr><th>PAN</th><td><?= htmlspecialchars(!empty($me['pan_number']) ? strtoupper($me['pan_number']) : 'NOT FURNISHED (higher TDS u/s 206AA may apply)') ?></td></tr>
        <tr><th>Phone / Email</th><td><?= htmlspecialchars(trim(($me['phone'] ?? '') . ' / ' . ($me['email'] ?? ''), ' /')) ?></td></tr>
    </table>

    <div class="sec-title">Deduction Summary — FY <?= htmlspecialchars($fy) ?></div>
    <table class="info-table">
        <tr><th>Total Commission Paid (Gross)</th><td>₹<?= number_format($gross, 2) ?></td></tr>
        <tr><th>Total TDS Deducted &amp; Deposited</th><td>₹<?= number_format($tds, 2) ?></td></tr>
        <tr><th>Total Net Paid</th><td>₹<?= number_format($net, 2) ?></td></tr>
        <tr><th>No. of Payouts</th><td><?= count($payouts) ?></td></tr>
    </table>

    <div class="sec-title">Payout-wise Breakup</div>
    <table class="info-table">
        <tr><th>Date</th><th>Gross (₹)</th><th>TDS (₹)</th><th>Net (₹)</th><th>UTR / Ref</th></tr>
        <?php if (empty($payouts)): ?>
            <tr><td colspan="5" class="text-center text-muted">No payouts in this financial year.</td></tr>
        <?php else: ?>
            <?php foreach ($payouts as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['paid_date'] ?: substr($p['created_at'] ?? '', 0, 10)) ?></td>
                <td><?= number_format((float)$p['gross_amount'], 2) ?></td>
                <td><?= number_format((float)$p['tds_amount'], 2) ?></td>
                <td><?= number_format((float)$p['net_amount'], 2) ?></td>
                <td><?= htmlspecialchars($p['transaction_ref'] ?: $p['cheque_number'] ?: '—') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>

    <p class="small text-muted">Certified that the above tax has been deducted at source and deposited to the credit of the Central Government. This is a computer-generated certificate for ITR filing assistance.</p>

    <div class="d-flex justify-content-end">
        <div class="sig-line">Authorised Signatory<br>APS Dream Home</div>
    </div>

    <div class="text-center mt-4 no-print">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print / Save as PDF</button>
    </div>
</div>
</body>
</html>
