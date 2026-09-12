<?php
// Colony-by-colony P&L ledger. Vars: $rows, $totals. Layout-rendered.
$rows = $rows ?? [];
$totals = $totals ?? [];
function pnl_color($v) { return $v >= 0 ? 'success' : 'danger'; }
?>
<style>
.pnl-card { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.meter { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
.meter > div { height: 100%; border-radius: 4px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Colony P&amp;L Ledger</h4>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/erp/inventory" class="btn btn-outline-secondary btn-sm"><i class="fas fa-cubes me-1"></i>Inventory</a>
        <a href="<?php echo BASE_URL; ?>/admin/erp/plot-profit" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-pie me-1"></i>Plot P&amp;L</a>
        <a href="<?php echo BASE_URL; ?>/admin/erp/defaulters" class="btn btn-outline-warning btn-sm"><i class="fas fa-exclamation-triangle me-1"></i>EMI Defaulters</a>
        <a href="<?php echo BASE_URL; ?>/admin/erp/colony-pnl?export=csv" class="btn btn-success btn-sm"><i class="fas fa-file-csv me-1"></i>Export CSV</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total Cost', $totals['cost'] ?? 0, 'danger', 'fa-wallet'],
        ['Booking Value', $totals['value'] ?? 0, 'primary', 'fa-file-contract'],
        ['Collected', $totals['collected'] ?? 0, 'success', 'fa-hand-holding-usd'],
        ['Outstanding', $totals['outstanding'] ?? 0, 'warning', 'fa-hourglass-half'],
        ['Realized Profit', $totals['realized'] ?? 0, pnl_color($totals['realized'] ?? 0), 'fa-trophy'],
        ['Projected Profit', $totals['projected'] ?? 0, pnl_color($totals['projected'] ?? 0), 'fa-bullseye'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-md-2 col-6">
        <div class="card pnl-card border-start border-4 border-<?php echo $c[2]; ?>">
            <div class="card-body py-3">
                <div class="fs-5 fw-bold text-<?php echo $c[2]; ?>">₹<?php echo number_format($c[1], 0); ?></div>
                <div class="text-muted small text-uppercase"><i class="fas <?php echo $c[3]; ?> me-1"></i><?php echo $c[0]; ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($rows as $r):
        $margin = (float)($r['margin'] ?? 0);
        $meterW = min(100, max(0, $margin > 0 ? min(100, $margin) : 0));
    ?>
    <div class="col-md-4">
        <div class="card pnl-card h-100">
            <div class="card-body">
                <h5 class="card-title mb-1"><?php echo e($r['name'] ?? ''); ?></h5>
                <div class="text-muted small mb-2"><?php echo e($r['location'] ?? ''); ?> · <?php echo (int)($r['sold'] ?? 0); ?>/<?php echo (int)($r['plots'] ?? 0); ?> sold</div>
                <div class="meter mb-2"><div class="bg-<?php echo pnl_color($r['projected'] ?? 0); ?>" style="width: <?php echo $meterW; ?>%"></div></div>
                <div class="small">
                    <div class="d-flex justify-content-between"><span>Land + Dev Cost</span><strong>₹<?php echo number_format($r['cost'] ?? 0, 0); ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Booking Value</span><strong>₹<?php echo number_format($r['value'] ?? 0, 0); ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Collected / Outstanding</span><strong class="text-success">₹<?php echo number_format($r['collected'] ?? 0, 0); ?></strong> <span class="text-warning">₹<?php echo number_format($r['outstanding'] ?? 0, 0); ?></span></div>
                    <div class="d-flex justify-content-between"><span>Realized Profit</span><strong class="text-<?php echo pnl_color($r['realized'] ?? 0); ?>">₹<?php echo number_format($r['realized'] ?? 0, 0); ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Projected / Margin</span><strong class="text-<?php echo pnl_color($r['projected'] ?? 0); ?>">₹<?php echo number_format($r['projected'] ?? 0, 0); ?> (<?php echo $margin; ?>%)</strong></div>
                    <div class="d-flex justify-content-between text-muted"><span>Cost / sq.ft</span><span>₹<?php echo number_format($r['cost_per_sqft'] ?? 0, 2); ?></span></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?>
    <div class="col-12"><div class="alert alert-info">No colonies found.</div></div>
    <?php endif; ?>
</div>

<div class="card pnl-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Colony</th><th>Plots</th><th class="text-end">Land</th><th class="text-end">Dev</th><th class="text-end">Cost</th><th class="text-end">Value</th><th class="text-end">Collected</th><th class="text-end">Outstanding</th><th class="text-end">Realized</th><th class="text-end">Projected</th><th class="text-end">Margin</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?php echo e($r['name'] ?? ''); ?></strong></td>
                        <td><?php echo (int)($r['sold'] ?? 0); ?>/<?php echo (int)($r['plots'] ?? 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($r['land'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($r['dev'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($r['cost'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($r['value'] ?? 0, 0); ?></td>
                        <td class="text-end text-success">₹<?php echo number_format($r['collected'] ?? 0, 0); ?></td>
                        <td class="text-end text-warning">₹<?php echo number_format($r['outstanding'] ?? 0, 0); ?></td>
                        <td class="text-end text-<?php echo pnl_color($r['realized'] ?? 0); ?>">₹<?php echo number_format($r['realized'] ?? 0, 0); ?></td>
                        <td class="text-end text-<?php echo pnl_color($r['projected'] ?? 0); ?>">₹<?php echo number_format($r['projected'] ?? 0, 0); ?></td>
                        <td class="text-end"><?php echo $r['margin'] ?? 0; ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-secondary fw-bold">
                        <td>TOTAL</td><td><?php echo (int)($totals['sold'] ?? 0); ?>/<?php echo (int)($totals['plots'] ?? 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['land'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['dev'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['cost'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['value'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['collected'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['outstanding'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['realized'] ?? 0, 0); ?></td>
                        <td class="text-end">₹<?php echo number_format($totals['projected'] ?? 0, 0); ?></td>
                        <td class="text-end"><?php echo $totals['margin'] ?? 0; ?>%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
