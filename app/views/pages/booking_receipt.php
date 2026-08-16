<?php require_once __DIR__ . '/../../Helpers/TranslationHelper.php'; ?>
<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Booking Receipt #<?= $booking['id'] ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
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
    <button class="btn btn-primary print-btn" onclick="window.print()"><i class="fas fa-print me-2"></i><?= __('receipt_print', [], 'Print / Download PDF') ?></button>

    <div class="receipt-box">
        <div class="receipt-header">
            <h2><i class="fas fa-home me-2"></i><?= __('receipt_company', [], 'APS Dream Home') ?></h2>
            <div class="subtitle"><?= __('receipt_subtitle', [], 'Booking Receipt & Confirmation') ?></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong><?= __('receipt_no', [], 'Receipt No:') ?></strong> RCP-<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?><br>
                <strong><?= __('receipt_date', [], 'Date:') ?></strong> <?= date('d M Y', strtotime($booking['booking_date'] ?? $booking['created_at'] ?? 'now')) ?>
            </div>
            <div class="text-end">
                <span class="badge bg-<?= $currentStatus === 'confirmed' || $currentStatus === 'completed' ? 'success' : ($currentStatus === 'cancelled' ? 'danger' : 'warning') ?> fs-6">
                    <?= ucfirst($currentStatus) ?>
                </span>
            </div>
        </div>

        <table class="table table-bordered">
            <tr><th class="style-83841"><?= __('receipt_booking_number', [], 'Booking Number') ?></th><td><strong>#<?= $booking['id'] ?></strong> (<?= htmlspecialchars($booking['booking_number'] ?? '') ?>)</td></tr>
            <tr><th><?= __('receipt_plot_details', [], 'Plot Details') ?></th><td>
                <strong><?= __('receipt_plot_no', [], 'Plot #') ?><?= htmlspecialchars($booking['plot_number'] ?? '') ?></strong><br>
                <?= htmlspecialchars($booking['colony_name'] ?? '') ?>
                <?php if (!empty($booking['district_name'])): ?>, <?= htmlspecialchars($booking['district_name']) ?><?php endif; ?>
                <?php if (!empty($booking['state_name'])): ?>, <?= htmlspecialchars($booking['state_name']) ?><?php endif; ?>
            </td></tr>
            <tr><th><?= __('receipt_plot_size', [], 'Plot Size') ?></th><td>
                <?php if (!empty($booking['dimension_label'])): ?><?= htmlspecialchars($booking['dimension_label']) ?> — <?php endif; ?>
                <?= number_format(floatval($booking['area_sqft'] ?? 0)) ?> sqft
            </td></tr>
            <tr><th><?= __('receipt_block', [], 'Block') ?></th><td><?= htmlspecialchars($booking['block'] ?? 'N/A') ?></td></tr>
            <tr><th><?= __('receipt_customer', [], 'Customer') ?></th><td><?= htmlspecialchars($user['name'] ?? $booking['customer_name'] ?? '') ?><br>
                <?= htmlspecialchars($user['email'] ?? '') ?><br>
                <?= htmlspecialchars($user['phone'] ?? '') ?>
            </td></tr>
            <tr><th><?= __('receipt_type', [], 'Booking Type') ?></th><td><?= htmlspecialchars($booking['booking_type'] ?? __('receipt_standard', [], 'Standard')) ?></td></tr>
        </table>

        <div class="amount-box">
            <div class="row">
                <div class="col-6">
                    <div class="label"><?= __('receipt_total_price', [], 'Total Plot Price') ?></div>
                    <div class="value">&#8377;<?= number_format(intval($booking['total_amount'] ?? $booking['plot_price'] ?? 0)) ?></div>
                </div>
                <div class="col-6 text-end">
                    <div class="label"><?= __('receipt_amount_paid', [], 'Amount Paid') ?></div>
                    <div class="value">&#8377;<?= number_format(intval($booking['amount'] ?? 0)) ?></div>
                </div>
            </div>
            <?php if (!empty($emis)): ?>
            <hr>
            <div class="label mb-2"><?= __('receipt_payment_schedule', [], 'Payment Schedule') ?></div>
            <table class="table table-sm mb-0">
                <thead><tr><th>#</th><th><?= __('receipt_th_due_date', [], 'Due Date') ?></th><th><?= __('receipt_th_amount', [], 'Amount') ?></th><th><?= __('receipt_th_paid', [], 'Paid') ?></th><th><?= __('receipt_th_status', [], 'Status') ?></th></tr></thead>
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
                <strong><?= __('receipt_plot_features', [], 'Plot Features:') ?></strong><br>
                <i class="fas fa-check-circle text-success me-1"></i> <?= __('receipt_corner', [], 'Corner Plot:') ?> <?= !empty($booking['corner_plot']) ? __('receipt_yes', [], 'Yes') : __('receipt_no_label', [], 'No') ?><br>
                <i class="fas fa-check-circle text-success me-1"></i> <?= __('receipt_park_facing', [], 'Park Facing:') ?> <?= !empty($booking['park_facing']) ? __('receipt_yes', [], 'Yes') : __('receipt_no_label', [], 'No') ?><br>
            </div>
            <div class="col-6 text-end">
                <img src="<?= BASE_URL ?>/assets/images/logo/apslogonew.jpg" alt="APS Dream Home" class="style-78171" onerror="this.style.display='none'" /><br>
                <small class="text-muted"><?= __('receipt_authorized_sig', [], 'Authorized Signature') ?></small>
            </div>
        </div>

        <div class="receipt-footer">
            <div class="row">
                <div class="col-6">
                    <strong>APS Dream Home</strong><br>
                    <?= __('receipt_footer_address', [], 'Gorakhpur, Uttar Pradesh') ?><br>
                    <?= __('receipt_footer_phone', [], 'Phone:') ?> <?= htmlspecialchars($phoneDisplay) ?>
                </div>
                <div class="col-6 text-end">
                    <small><?= __('receipt_footer_computer', [], 'This is a computer-generated receipt.') ?><br>
                    <?= __('receipt_footer_terms', [], 'Booking Subject to Terms & Conditions.') ?><br>
                    <?= __('receipt_footer_rera', [], 'RERA Registered Project.') ?></small>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>