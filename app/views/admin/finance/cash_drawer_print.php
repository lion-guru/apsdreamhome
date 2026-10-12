<?php
/**
 * Day-End Shift Register Print View
 */
$sheet = $sheet ?? [];
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$denom = json_decode($sheet['denominations_json'] ?? '{}', true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $e($page_title ?? 'Cash Drawer Closing Sheet') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sheet-card { max-width: 750px; margin: 30px auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        @media print {
            body { background: #fff; }
            .sheet-card { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="sheet-card">
    <div class="text-center border-bottom pb-3 mb-4">
        <h3 class="fw-bold mb-0">APS DREAM HOME</h3>
        <div class="text-muted small">Day-End Cash Drawer &amp; Safe Reconciliation Register</div>
    </div>

    <div class="row g-2 mb-4 small">
        <div class="col-6">
            <strong>Closing Date:</strong> <?= date('d M Y (l)', strtotime($sheet['closing_date'])) ?><br>
            <strong>Branch:</strong> <?= $e($sheet['branch_name'] ?? 'Head Office') ?>
        </div>
        <div class="col-6 text-end">
            <strong>Cashier:</strong> <?= $e($sheet['cashier_name'] ?: 'Cashier #' . $sheet['cashier_id']) ?><br>
            <strong>Closed At:</strong> <?= date('d M Y, h:i A', strtotime($sheet['closed_at'])) ?>
        </div>
    </div>

    <h6 class="fw-bold mb-2">1. Cash Reconciliation Summary</h6>
    <table class="table table-bordered mb-4">
        <thead class="table-light small">
            <tr>
                <th>Particulars</th>
                <th class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Opening Cash in Safe (B/F)</td>
                <td class="text-end">₹<?= number_format((float)$sheet['opening_cash'], 2) ?></td>
            </tr>
            <tr class="text-success">
                <td>(+) Daily Cash Counter Collections</td>
                <td class="text-end">+₹<?= number_format((float)$sheet['cash_receipts'], 2) ?></td>
            </tr>
            <tr class="text-danger">
                <td>(-) Petty Cash Expenses Vouchers</td>
                <td class="text-end">-₹<?= number_format((float)$sheet['cash_expenses'], 2) ?></td>
            </tr>
            <tr class="text-primary">
                <td>(-) Cash Remitted / Deposited into Bank</td>
                <td class="text-end">-₹<?= number_format((float)$sheet['bank_deposit'], 2) ?></td>
            </tr>
            <tr class="table-light fw-bold">
                <td>System Book Closing Balance</td>
                <td class="text-end">₹<?= number_format((float)$sheet['system_closing'], 2) ?></td>
            </tr>
            <tr class="fw-bold">
                <td>Actual Physical Cash Counted in Safe</td>
                <td class="text-end">₹<?= number_format((float)$sheet['physical_closing'], 2) ?></td>
            </tr>
            <tr class="table-secondary fw-bold">
                <td>Discrepancy (Physical - System)</td>
                <?php $d = (float)$sheet['discrepancy']; ?>
                <td class="text-end <?= $d == 0 ? 'text-success' : ($d > 0 ? 'text-primary' : 'text-danger') ?>">
                    <?= $d >= 0 ? '+' : '' ?>₹<?= number_format($d, 2) ?>
                    (<?= $d == 0 ? 'BALANCED' : ($d > 0 ? 'SURPLUS' : 'SHORTAGE') ?>)
                </td>
            </tr>
        </tbody>
    </table>

    <h6 class="fw-bold mb-2">2. Physical Denomination Breakdown</h6>
    <table class="table table-bordered table-sm mb-4 text-center small">
        <thead class="table-light">
            <tr>
                <th>Denomination</th>
                <th>Count (Nos.)</th>
                <th class="text-end">Total Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>₹500 Notes</td>
                <td><?= (int)($denom['500'] ?? 0) ?></td>
                <td class="text-end">₹<?= number_format(((int)($denom['500'] ?? 0)) * 500, 2) ?></td>
            </tr>
            <tr>
                <td>₹200 Notes</td>
                <td><?= (int)($denom['200'] ?? 0) ?></td>
                <td class="text-end">₹<?= number_format(((int)($denom['200'] ?? 0)) * 200, 2) ?></td>
            </tr>
            <tr>
                <td>₹100 Notes</td>
                <td><?= (int)($denom['100'] ?? 0) ?></td>
                <td class="text-end">₹<?= number_format(((int)($denom['100'] ?? 0)) * 100, 2) ?></td>
            </tr>
            <tr>
                <td>₹50 Notes</td>
                <td><?= (int)($denom['50'] ?? 0) ?></td>
                <td class="text-end">₹<?= number_format(((int)($denom['50'] ?? 0)) * 50, 2) ?></td>
            </tr>
            <tr>
                <td>₹20 Notes</td>
                <td><?= (int)($denom['20'] ?? 0) ?></td>
                <td class="text-end">₹<?= number_format(((int)($denom['20'] ?? 0)) * 20, 2) ?></td>
            </tr>
            <tr>
                <td>₹10 &amp; Coins</td>
                <td>-</td>
                <td class="text-end">₹<?= number_format((float)($denom['coins'] ?? 0), 2) ?></td>
            </tr>
            <tr class="table-light fw-bold">
                <td colspan="2" class="text-start">Total Physical Cash Count</td>
                <td class="text-end">₹<?= number_format((float)$sheet['physical_closing'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($sheet['notes'])): ?>
        <div class="mb-4 small">
            <strong>Notes / Observations:</strong><br>
            <?= nl2br($e($sheet['notes'])) ?>
        </div>
    <?php endif; ?>

    <div class="row pt-5 mt-4 text-center">
        <div class="col-6">
            <div class="border-top pt-2 small fw-semibold">
                Cashier / Counter Executive Signature<br>
                <span class="text-muted fw-normal"><?= $e($sheet['cashier_name']) ?></span>
            </div>
        </div>
        <div class="col-6">
            <div class="border-top pt-2 small fw-semibold">
                Accounts Manager / Director Sign-off<br>
                <span class="text-muted fw-normal">Authorized Verification</span>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center no-print mt-5 pt-3 border-top">
        <a href="javascript:window.close()" class="btn btn-outline-secondary btn-sm">Close</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i>Print Register Sheet</button>
    </div>
</div>

</body>
</html>
