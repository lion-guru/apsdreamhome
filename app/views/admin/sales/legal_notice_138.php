<?php
/** Section 138 NI Act 15-day demand notice — printable DRAFT. */
/** @var array $receipt */
/** @var array $booking */
/** @var array $customer */
$receipt = $receipt ?? [];
$booking = $booking ?? [];
$customer = $customer ?? [];
$bounce_date = $bounce_date ?? '';
$cure_deadline = $cure_deadline ?? '';
$days_left = $days_left ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$blank = '<span class="fill"></span>';
$inr = function ($v) { return '₹' . number_format((float)$v, 2); };
?>
<style>
.n138 { background: #fff; color: #111; font-size: 13.5px; }
.n138 .sheet { max-width: 780px; margin: 0 auto 18px; border: 1px solid #999; padding: 26px 30px; background: #fff; }
.n138 .draft-stamp { border: 2px dashed #b91c1c; color: #b91c1c; font-weight: 700; text-align: center; padding: 4px; margin-bottom: 12px; letter-spacing: 2px; }
.n138 table.kv { width: 100%; border-collapse: collapse; margin: 10px 0; }
.n138 table.kv td { border: 1px solid #bbb; padding: 5px 8px; vertical-align: top; }
.n138 table.kv td.k { width: 34%; background: #f4f4f4; font-weight: 600; }
.n138 .fill { display: inline-block; min-width: 170px; border-bottom: 1px dotted #111; }
.n138 .sigrow { display: flex; gap: 24px; margin-top: 30px; }
.n138 .sigbox { flex: 1; border-top: 1px solid #111; padding-top: 4px; font-size: 12px; }
.n138 .toolbar { max-width: 780px; margin: 0 auto 14px; display: flex; gap: 8px; }
.n138 .countdown { font-size: 15px; font-weight: 700; color: <?= ((int)$days_left < 0) ? '#b91c1c' : '#166534' ?>; }
@media print {
    .n138 .toolbar { display: none !important; }
    .n138 .sheet { border: none; margin: 0; padding: 0; }
    body { background: #fff !important; }
}
</style>
<div class="n138">
    <div class="toolbar">
        <a href="<?= $base ?>/admin/sales/bookings/<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">← Back to Booking</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Notice</button>
    </div>
    <div class="sheet">
        <div class="draft-stamp">DRAFT — GET VETTED BY LEGAL COUNSEL BEFORE DISPATCH</div>
        <h4 class="text-center mb-1">Legal Demand Notice u/s 138, Negotiable Instruments Act, 1881</h4>
        <p class="text-center text-muted">(15-day cure notice for dishonoured cheque)</p>
        <p class="countdown text-center">Cure deadline: <?= $e($cure_deadline) ?> — <?= ((int)$days_left < 0) ? abs((int)$days_left) . ' day(s) OVERDUE' : (int)$days_left . ' day(s) left' ?></p>
        <table class="kv">
            <tr><td class="k">Ref / Date</td><td>APS/LEGAL/138/<?= date('Y') ?>/<?= $blank ?> &nbsp; dated <?= date('d-M-Y') ?></td></tr>
            <tr><td class="k">To (drawer)</td><td><strong><?= $e($customer['name'] ?? $booking['customer_name'] ?? '') ?></strong><br><?= $e($customer['address'] ?? '') !== '' ? $e($customer['address']) : $blank ?><br>Ph: <?= $e($customer['phone'] ?? '') ?></td></tr>
            <tr><td class="k">From (payee)</td><td>APS Dream Homes Pvt. Ltd. (booking <?= $e($booking['booking_number'] ?? '') ?>, plot <?= $e($booking['plot_number'] ?? '') ?>)</td></tr>
        </table>
        <p>Dear Sir / Madam,</p>
        <p>Your cheque detailed below, issued towards your plot dues, was returned unpaid on <strong><?= $e($bounce_date) ?></strong>:</p>
        <table class="kv">
            <tr><td class="k">Cheque No.</td><td><?= $e($receipt['cheque_number'] ?? '') !== '' ? $e($receipt['cheque_number']) : $blank ?></td><td class="k">Dated</td><td><?= $e($receipt['cheque_date'] ?? '') !== '' ? $e($receipt['cheque_date']) : $blank ?></td></tr>
            <tr><td class="k">Bank</td><td><?= $e($receipt['bank_name'] ?? '') !== '' ? $e($receipt['bank_name']) : $blank ?></td><td class="k">Amount</td><td><strong><?= $inr($receipt['amount'] ?? 0) ?></strong></td></tr>
            <tr><td class="k">Receipt No.</td><td><?= $e($receipt['receipt_number'] ?? '') ?></td><td class="k">Bounce memo</td><td><?= $e($receipt['bounce_reason'] ?? '') !== '' ? $e($receipt['bounce_reason']) : $blank ?></td></tr>
        </table>
        <p>We hereby demand payment of <strong><?= $inr($receipt['amount'] ?? 0) ?></strong> plus applicable bounce charges within <strong>15 days</strong> of receiving this notice (on or before <strong><?= $e($cure_deadline) ?></strong>), failing which we shall initiate proceedings under Section 138 of the Negotiable Instruments Act, 1881 entirely at your risk, cost and consequences.</p>
        <table class="kv">
            <tr><td class="k">Speed-post tracking</td><td><?= $blank ?></td><td class="k">Dispatched on</td><td><?= $blank ?></td></tr>
            <tr><td class="k">WhatsApp/SMS alert sent</td><td>Yes / No (circle) &nbsp; on <?= $blank ?></td><td class="k">Cleared online (UPI)</td><td>Yes / No — txn <?= $blank ?></td></tr>
        </table>
        <div class="sigrow"><div class="sigbox">Authorised Signatory, APS Dream Homes Pvt. Ltd. + seal</div><div class="sigbox">Advocate (name, enrolment no.)</div></div>
    </div>
</div>
