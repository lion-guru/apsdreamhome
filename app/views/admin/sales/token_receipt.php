<?php
/** Provisional token allotment slip (kachchi receipt) — printable. */
/** @var array $token */
$token = $token ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$qrText = 'TOKEN|' . ($token['receipt_no'] ?? '') . '|plot:' . ($token['plot_number'] ?? '') . '|amt:' . ($token['token_amount'] ?? '');
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=' . urlencode($qrText);
?>
<style>
.tkn { background: #fff; color: #111; font-size: 13.5px; }
.tkn .sheet { max-width: 720px; margin: 0 auto; border: 2px solid #111; padding: 24px 28px; background: #fff; }
.tkn table.kv { width: 100%; border-collapse: collapse; margin: 10px 0; }
.tkn table.kv td { border: 1px solid #999; padding: 5px 8px; }
.tkn table.kv td.k { width: 36%; background: #f4f4f4; font-weight: 600; }
.tkn .toolbar { max-width: 720px; margin: 0 auto 14px; display: flex; gap: 8px; }
.tkn .sigrow { display: flex; gap: 24px; margin-top: 28px; }
.tkn .sigbox { flex: 1; border-top: 1px solid #111; padding-top: 4px; font-size: 12px; }
@media print { .tkn .toolbar { display: none !important; } .tkn .sheet { border-width: 1px; } body { background: #fff !important; } }
</style>
<div class="tkn">
    <div class="toolbar">
        <a href="<?= $base ?>/admin/sales/token-bookings" class="btn btn-outline-secondary btn-sm">← Token Bookings</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Slip</button>
    </div>
    <div class="sheet">
        <h4 class="text-center mb-0">Provisional Token Allotment Slip (कच्ची रसीद)</h4>
        <p class="text-center text-muted">APS Dream Homes Pvt. Ltd. — token advance, not a sale deed</p>
        <table class="kv">
            <tr><td class="k">Receipt No.</td><td><strong><?= $e($token['receipt_no'] ?? '') ?></strong></td><td class="k">Date</td><td><?= $e($token['created_at'] ?? '') ?></td></tr>
            <tr><td class="k">Received From</td><td><?= $e($token['customer_name'] ?? '') ?> (<?= $e($token['customer_phone'] ?? '') ?>)</td><td class="k">Aadhaar</td><td><?= $e($token['customer_aadhaar'] ?? '—') ?></td></tr>
            <tr><td class="k">Plot</td><td><?= $e($token['plot_number'] ?? '') ?> (<?= $e($token['area_sqft'] ?? '') ?> sqft), <?= $e($token['colony_name'] ?? '') ?></td><td class="k">Token Amount</td><td><strong>₹<?= number_format((float)($token['token_amount'] ?? 0)) ?></strong> via <?= $e($token['payment_mode'] ?? '') ?></td></tr>
            <tr><td class="k">Payment Ref</td><td><?= $e($token['payment_ref'] ?? '—') ?></td><td class="k">Plot Locked Till</td><td><strong><?= $e($token['lock_expires_at'] ?? '') ?></strong> (7 days)</td></tr>
        </table>
        <p class="small">Terms: token adjusts against booking value on conversion within lock period; on expiry the plot auto-releases and token is refundable per policy. Status: <strong><?= $e($token['status'] ?? '') ?></strong>.</p>
        <div style="display:flex;gap:20px;align-items:center;">
            <img src="<?= $e($qrUrl) ?>" alt="verify QR" width="130" height="130">
            <small class="text-muted">Scan to verify receipt <?= $e($token['receipt_no'] ?? '') ?> (self-describing code).</small>
        </div>
        <div class="sigrow"><div class="sigbox">Received by (associate/office) + seal</div><div class="sigbox">Buyer signature</div></div>
    </div>
</div>
