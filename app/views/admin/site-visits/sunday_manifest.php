<?php
/** Sunday dispatch manifest — printable per-vehicle sheets. */
/** @var array $trips */
/** @var string $date */
$trips = $trips ?? [];
$date = $date ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<style>
.mnf { background: #fff; color: #111; font-size: 13px; }
.mnf .sheet { max-width: 820px; margin: 0 auto 18px; border: 1px solid #999; padding: 20px 24px; background: #fff; }
.mnf table.kv { width: 100%; border-collapse: collapse; margin: 8px 0; }
.mnf table.kv td, .mnf table.kv th { border: 1px solid #999; padding: 4px 7px; }
.mnf .toolbar { max-width: 820px; margin: 0 auto 14px; display: flex; gap: 8px; align-items: center; }
.mnf .sigrow { display: flex; gap: 24px; margin-top: 16px; }
.mnf .sigbox { flex: 1; border-top: 1px solid #111; padding-top: 4px; font-size: 12px; }
@media print { .mnf .toolbar { display: none !important; } .mnf .sheet { border: none; margin: 0 0 10px; } .mnf .page-break { page-break-after: always; } body { background: #fff !important; } }
</style>
<div class="mnf">
    <div class="toolbar">
        <strong>Sunday Dispatch Manifest</strong>
        <form method="GET" class="d-flex gap-2 ms-auto">
            <input type="date" name="date" class="form-control form-control-sm" value="<?= $e($date) ?>" onchange="this.form.submit()">
        </form>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print All</button>
    </div>
    <?php if (empty($trips)): ?>
        <div class="sheet text-center text-muted">No trips scheduled for <?= $e($date) ?>.</div>
    <?php else: foreach ($trips as $t): ?>
        <div class="sheet <?= count($trips) > 1 ? 'page-break' : '' ?>">
            <h5 class="mb-1">Cab Dispatch — <?= $e($t['vehicle_no'] ?? '') ?> (<?= $e($t['vehicle_name'] ?? '') ?>, cap <?= (int)($t['capacity'] ?? 0) ?>)</h5>
            <p class="text-muted small mb-2"><?= $e($date) ?> · Driver: <strong><?= $e($t['driver_name'] ?? '—') ?></strong> (<?= $e($t['driver_phone'] ?? '') ?>) · → <?= $e($t['colony_name'] ?? '') ?> · Associate: <?= $e($t['associate_name'] ?? '—') ?></p>
            <table class="kv">
                <tr><th>#</th><th>Customer</th><th>Phone</th><th>Pickup Point</th><th>Family</th></tr>
                <?php $i = 1; foreach (($t['passengers'] ?? []) as $p): ?>
                    <tr><td><?= $i++ ?></td><td><?= $e($p['visitor_name'] ?? '') ?></td><td><?= $e($p['visitor_phone'] ?? '') ?></td><td><?= $e($p['pickup_location'] ?? $t['pickup_point'] ?? '') ?></td><td><?= $e($p['family_count'] ?? 1) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($t['passengers'])): ?><tr><td colspan="5" class="text-center text-muted">No passengers assigned yet.</td></tr><?php endif; ?>
            </table>
            <table class="kv">
                <tr><td class="k" style="width:40%;background:#f4f4f4;font-weight:600;">Odometer start / end</td><td>__________ / __________</td><td style="width:25%;background:#f4f4f4;font-weight:600;">Fuel voucher ₹</td><td>__________</td></tr>
            </table>
            <div class="sigrow"><div class="sigbox">Driver sign + time out</div><div class="sigbox">Dispatch desk sign</div><div class="sigbox">Associate sign on return</div></div>
        </div>
    <?php endforeach; endif; ?>
</div>
