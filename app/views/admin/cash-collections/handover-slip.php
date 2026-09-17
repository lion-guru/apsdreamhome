<?php
/** @var string $date */
/** @var array $rows */
/** @var array $by_cashier */
/** @var float $grand_total */
$date = $date ?? date('Y-m-d');
$rows = $rows ?? [];
$by_cashier = $by_cashier ?? [];
$grand_total = $grand_total ?? 0;
$slipNo = 'CHS-' . str_replace('-', '', $date) . '-' . substr(md5($date . count($rows)), 0, 6);
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="<?= BASE_URL ?>/admin/cash-collections" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Collections</a>
        <form method="GET" action="<?= BASE_URL ?>/admin/cash-collections/handover-slip" class="d-flex gap-2">
            <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($date) ?>">
            <button type="submit" class="btn btn-sm btn-primary">View</button>
        </form>
        <button class="btn btn-success btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Slip</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <h4 class="mb-0">APS Dream Home — Daily Cash Handover Slip</h4>
                <div class="text-muted">Slip <?= htmlspecialchars($slipNo) ?> · Date <?= htmlspecialchars($date) ?></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr><th>Cashier</th><th class="text-center">Receipts</th><th class="text-end">Cash Total</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($by_cashier)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No cash collections on this date.</td></tr>
                        <?php else: foreach ($by_cashier as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td class="text-center"><?= (int)$c['count'] ?></td>
                                <td class="text-end">Rs.<?= number_format((float)$c['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr><th colspan="2" class="text-end">Grand Total Cash</th><th class="text-end">Rs.<?= number_format((float)$grand_total, 2) ?></th></tr>
                    </tfoot>
                </table>
            </div>
            <?php if (!empty($rows)): ?>
            <h6 class="mt-4 mb-2">Receipt-wise breakup</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>#</th><th>Customer</th><th>Cashier</th><th>Booking</th><th class="text-end">Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= (int)($r['id'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($r['customer_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['collector_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($r['booking_number'] ?? '-') ?></td>
                            <td class="text-end">Rs.<?= number_format((float)($r['amount'] ?? 0), 2) ?></td>
                            <td><?= htmlspecialchars($r['status'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <div class="row mt-5">
                <div class="col-4 text-center">
                    <div style="border-top:1px solid #333; padding-top:6px;">Cashier Signature<br><small class="text-muted">Name / Date</small></div>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top:1px solid #333; padding-top:6px;">Verified By<br><small class="text-muted">Accountant / Date</small></div>
                </div>
                <div class="col-4 text-center">
                    <div style="border-top:1px solid #333; padding-top:6px;">Received By (Bank / Director)<br><small class="text-muted">Name / Date</small></div>
                </div>
            </div>
        </div>
    </div>
</div>
<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">@media print { .no-print { display: none !important; } }</style>
