<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Receipt #<?= $booking['id'] ?> - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        .receipt-box { max-width: 800px; margin: 30px auto; padding: 40px; border: 1px solid #dee2e6; border-radius: 8px; background: #fff; }
        .receipt-header { text-align: center; border-bottom: 2px solid #1a73e8; padding-bottom: 20px; margin-bottom: 25px; }
        .receipt-header h2 { color: #1a73e8; font-weight: 700; }
        .receipt-header .subtitle { color: #666; font-size: 0.9rem; }
        .amount-box { background: #f0f7ff; border-left: 4px solid #1a73e8; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .amount-box .label { font-size: 0.85rem; color: #666; }
        .amount-box .value { font-size: 1.5rem; font-weight: 700; color: #1a73e8; }
        .receipt-footer { border-top: 1px dashed #dee2e6; padding-top: 20px; margin-top: 25px; font-size: 0.85rem; color: #666; }
        .status-badge { padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; }
        .print-btn { position: fixed; bottom: 20px; right: 20px; z-index: 1000; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="btn btn-primary print-btn" onclick="window.print()"><i class="fas fa-print me-2"></i>Print / Download PDF</button>

    <div class="receipt-box">
        <div class="receipt-header">
            <h2><i class="fas fa-home me-2"></i>APS Dream Home</h2>
            <div class="subtitle">Booking Receipt & Confirmation</div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong>Receipt No:</strong> RCP-<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?><br>
                <strong>Date:</strong> <?= date('d M Y', strtotime($booking['booking_date'] ?? $booking['created_at'] ?? 'now')) ?>
            </div>
            <div class="text-end">
                <span class="badge bg-<?= $currentStatus === 'confirmed' || $currentStatus === 'completed' ? 'success' : ($currentStatus === 'cancelled' ? 'danger' : 'warning') ?> fs-6">
                    <?= ucfirst($currentStatus) ?>
                </span>
            </div>
        </div>

        <table class="table table-bordered">
            <tr><th style="width:180px">Booking Number</th><td><strong>#<?= $booking['id'] ?></strong> (<?= htmlspecialchars($booking['booking_number'] ?? '') ?>)</td></tr>
            <tr><th>Plot Details</th><td>
                <strong>Plot #<?= htmlspecialchars($booking['plot_number'] ?? '') ?></strong><br>
                <?= htmlspecialchars($booking['colony_name'] ?? '') ?>
                <?php if (!empty($booking['district_name'])): ?>, <?= htmlspecialchars($booking['district_name']) ?><?php endif; ?>
                <?php if (!empty($booking['state_name'])): ?>, <?= htmlspecialchars($booking['state_name']) ?><?php endif; ?>
            </td></tr>
            <tr><th>Plot Size</th><td>
                <?php if (!empty($booking['dimension_label'])): ?><?= htmlspecialchars($booking['dimension_label']) ?> — <?php endif; ?>
                <?= number_format(floatval($booking['area_sqft'] ?? 0)) ?> sqft
            </td></tr>
            <tr><th>Block</th><td><?= htmlspecialchars($booking['block'] ?? 'N/A') ?></td></tr>
            <tr><th>Customer</th><td><?= htmlspecialchars($user['name'] ?? $booking['customer_name'] ?? '') ?><br>
                <?= htmlspecialchars($user['email'] ?? '') ?><br>
                <?= htmlspecialchars($user['phone'] ?? '') ?>
            </td></tr>
            <tr><th>Booking Type</th><td><?= htmlspecialchars($booking['booking_type'] ?? 'Standard') ?></td></tr>
        </table>

        <div class="amount-box">
            <div class="row">
                <div class="col-6">
                    <div class="label">Total Plot Price</div>
                    <div class="value">&#8377;<?= number_format(intval($booking['total_amount'] ?? $booking['plot_price'] ?? 0)) ?></div>
                </div>
                <div class="col-6 text-end">
                    <div class="label">Amount Paid</div>
                    <div class="value">&#8377;<?= number_format(intval($booking['amount'] ?? 0)) ?></div>
                </div>
            </div>
            <?php if (!empty($emis)): ?>
            <hr>
            <div class="label mb-2">Payment Schedule</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($emis as $emi): ?>
                    <tr>
                        <td><?= $emi['installment_no'] ?></td>
                        <td><?= date('d M Y', strtotime($emi['due_date'])) ?></td>
                        <td>&#8377;<?= number_format(intval($emi['amount'])) ?></td>
                        <td>&#8377;<?= number_format(intval($emi['paid_amount'] ?? 0)) ?></td>
                        <td><span class="badge bg-<?= $emi['status'] === 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($emi['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="row mt-3">
            <div class="col-6">
                <strong>Plot Features:</strong><br>
                <i class="fas fa-check-circle text-success me-1"></i> Corner Plot: <?= !empty($booking['corner_plot']) ? 'Yes' : 'No' ?><br>
                <i class="fas fa-check-circle text-success me-1"></i> Park Facing: <?= !empty($booking['park_facing']) ? 'Yes' : 'No' ?><br>
            </div>
            <div class="col-6 text-end">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="APS Dream Home" style="max-height:60px;" onerror="this.style.display='none'" /><br>
                <small class="text-muted">Authorized Signature</small>
            </div>
        </div>

        <div class="receipt-footer">
            <div class="row">
                <div class="col-6">
                    <strong>APS Dream Home</strong><br>
                    Gorakhpur, Uttar Pradesh<br>
                    Phone: <?= htmlspecialchars($phoneDisplay) ?>
                </div>
                <div class="col-6 text-end">
                    <small>This is a computer-generated receipt.<br>
                    Booking Subject to Terms & Conditions.<br>
                    RERA Registered Project.</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>