<?php
/** OTS settlement sanction letter — printable. */
/** @var array $booking */
/** @var array $customer */
/** @var array $overdue */
$booking = $booking ?? [];
$customer = $customer ?? [];
$scheme = $scheme ?? null;
$overdue = $overdue ?? ['principal' => 0, 'penalty' => 0, 'count' => 0];
$waived = $waived ?? 0;
$payoff = $payoff ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$inr = function ($v) { return '₹' . number_format((float)$v, 2); };
?>
<style>
.ots .sheet { max-width: 760px; margin: 0 auto; border: 2px solid #111; padding: 24px 28px; background: #fff; color: #111; font-size: 13.5px; }
.ots table.kv { width: 100%; border-collapse: collapse; margin: 10px 0; }
.ots table.kv td { border: 1px solid #999; padding: 5px 8px; }
.ots table.kv td.k { width: 38%; background: #f4f4f4; font-weight: 600; }
.ots .toolbar { max-width: 760px; margin: 0 auto 14px; display: flex; gap: 8px; }
.ots .sigrow { display: flex; gap: 24px; margin-top: 28px; }
.ots .sigbox { flex: 1; border-top: 1px solid #111; padding-top: 4px; font-size: 12px; }
@media print { .ots .toolbar { display: none !important; } body { background: #fff !important; } }
</style>
<div class="ots">
    <div class="toolbar">
        <a href="<?= $base ?>/admin/finance/ots-schemes" class="btn btn-outline-secondary btn-sm">← Schemes</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Letter</button>
    </div>
    <div class="sheet">
        <h4 class="text-center mb-0">One-Time Settlement Sanction Letter (एकमुश्त समाधान पत्र)</h4>
        <p class="text-center text-muted">APS Dream Homes Pvt. Ltd.<?= $scheme ? ' — <strong>' . $e($scheme['scheme_name']) . '</strong> (' . $e($scheme['start_date']) . ' → ' . $e($scheme['end_date']) . ')' : ' — <strong>no active scheme (standard terms)</strong>' ?></p>
        <table class="kv">
            <tr><td class="k">Buyer</td><td><?= $e($customer['name'] ?? $booking['customer_name'] ?? '') ?> (<?= $e($customer['phone'] ?? '') ?>)</td><td class="k">Booking</td><td><?= $e($booking['booking_number'] ?? '') ?></td></tr>
            <tr><td class="k">Overdue installments</td><td><?= (int)($overdue['count'] ?? 0) ?></td><td class="k">Overdue principal</td><td><?= $inr($overdue['principal'] ?? 0) ?></td></tr>
            <tr><td class="k">Accrued penalty</td><td><?= $inr($overdue['penalty'] ?? 0) ?></td><td class="k">Waiver (<?= $scheme ? number_format((float)$scheme['waiver_percentage'], 2) . '%' : '—' ?>)</td><td class="text-success fw-bold">− <?= $inr($waived) ?></td></tr>
            <tr><td class="k">One-time payoff</td><td colspan="3"><strong><?= $inr($payoff) ?></strong> — pay in full to close all overdue dues under this scheme.</td></tr>
        </table>
        <?php if (!empty($scheme['terms_conditions'])): ?><p class="small"><strong>Terms:</strong> <?= $e($scheme['terms_conditions']) ?></p><?php endif; ?>
        <div class="sigrow"><div class="sigbox">Authorised Signatory + seal</div><div class="sigbox">Buyer acceptance + date</div></div>
    </div>
</div>
