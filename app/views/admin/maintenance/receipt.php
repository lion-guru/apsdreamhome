<?php
/**
 * Maintenance Due Payment Receipt View
 */
$due = $due ?? [];
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $e($page_title ?? 'Maintenance Receipt') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .receipt-card { max-width: 650px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 35px; border-top: 6px solid #0d6efd; }
        .watermark { position: absolute; opacity: 0.05; transform: rotate(-30deg); font-size: 80px; font-weight: bold; top: 35%; left: 15%; pointer-events: none; }
        @media print {
            body { background: #fff; }
            .receipt-card { box-shadow: none; border: 1px solid #ddd; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="receipt-card position-relative">
    <div class="watermark">PAID</div>

    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
            <h4 class="fw-bold text-primary mb-1">APS DREAM HOME</h4>
            <div class="small text-muted">Society Maintenance &amp; Development Receipt</div>
        </div>
        <div class="text-end">
            <span class="badge bg-success-subtle text-success fs-6 border border-success-subtle px-3 py-1">PAID</span>
            <div class="small text-muted mt-1"><?= date('d M Y, h:i A', strtotime($due['paid_at'] ?? 'now')) ?></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6">
            <label class="text-muted small">Receipt Number</label>
            <div class="fw-bold font-monospace"><?= $e($due['receipt_number'] ?: ('MNT-#' . $due['id'])) ?></div>
        </div>
        <div class="col-6 text-end">
            <label class="text-muted small">Billing Month</label>
            <div class="fw-bold"><?= date('F Y', strtotime($due['bill_month'] . '-01')) ?></div>
        </div>
        <div class="col-6">
            <label class="text-muted small">Customer Name</label>
            <div class="fw-semibold"><?= $e($due['customer_name'] ?: 'Customer #' . $due['customer_id']) ?></div>
            <?php if (!empty($due['customer_phone'])): ?>
                <div class="small text-muted"><?= $e($due['customer_phone']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6 text-end">
            <label class="text-muted small">Colony &amp; Plot</label>
            <div class="fw-semibold"><?= $e($due['colony_name'] ?: 'Colony #' . $due['colony_id']) ?></div>
            <div class="small text-muted">Plot No: <strong><?= $e($due['plot_number'] ?: ('#' . $due['plot_id'])) ?></strong> (<?= number_format((float)$due['area_sqft']) ?> sqft)</div>
        </div>
    </div>

    <table class="table table-bordered mb-4">
        <thead class="table-light small">
            <tr>
                <th>Description</th>
                <th class="text-end">Calculation</th>
                <th class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Base Maintenance</td>
                <td class="text-end small"><?= number_format((float)$due['area_sqft']) ?> sqft @ ₹<?= number_format((float)$due['rate_per_sqft'], 2) ?></td>
                <td class="text-end"><?= number_format((float)$due['base_amount'], 2) ?></td>
            </tr>
            <?php if ((float)$due['water_charges'] > 0): ?>
            <tr>
                <td>Water Supply Charges</td>
                <td class="text-end small">Fixed</td>
                <td class="text-end"><?= number_format((float)$due['water_charges'], 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ((float)$due['security_charges'] > 0): ?>
            <tr>
                <td>Security &amp; Common Area Maintenance</td>
                <td class="text-end small">Fixed</td>
                <td class="text-end"><?= number_format((float)$due['security_charges'], 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="table-light fw-bold">
                <td colspan="2">Total Amount Due</td>
                <td class="text-end">₹<?= number_format((float)$due['amount_due'], 2) ?></td>
            </tr>
            <tr class="fw-bold text-success">
                <td colspan="2">Amount Paid</td>
                <td class="text-end">₹<?= number_format((float)$due['paid_amount'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row g-2 border-top pt-3 small text-muted mb-4">
        <div class="col-6">
            Payment Mode: <strong class="text-dark"><?= strtoupper($e($due['payment_mode'] ?? 'UPI')) ?></strong>
        </div>
        <div class="col-6 text-end">
            Transaction Ref: <strong class="text-dark"><?= $e($due['transaction_ref'] ?: 'N/A') ?></strong>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center no-print pt-3">
        <a href="javascript:window.close()" class="btn btn-outline-secondary btn-sm">Close</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i>Print Receipt</button>
    </div>
</div>

</body>
</html>
