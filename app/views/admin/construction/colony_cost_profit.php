<?php
/** @var array $rows */
/** @var array $grand */
$rows = $rows ?? []; $grand = $grand ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$grandCost = ($grand['civil'] ?? 0) + ($grand['material'] ?? 0);
$grandProfit = ($grand['contracted'] ?? 0) - $grandCost;
$grandMargin = ($grand['contracted'] ?? 0) > 0 ? round($grandProfit * 100 / $grand['contracted'], 1) : 0;
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-scale-balanced me-2"></i>Colony Actual Cost vs Profit</h4>
        <p class="text-muted mb-0">Civil work + consumed material vs contracted plot value &amp; collected cash</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/construction/civil-work" class="btn btn-outline-primary btn-sm"><i class="fas fa-tractor me-1"></i>Civil Work Log</a>
        <a href="<?= $base ?>/admin/construction/material-inventory" class="btn btn-outline-secondary btn-sm"><i class="fas fa-boxes me-1"></i>Materials</a>
        <button class="btn btn-dark btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4 text-danger">₹<?= number_format($grandCost, 0) ?></div>
        <div class="text-muted small">Total Civil + Material Cost</div>
        <small class="text-muted">Civil ₹<?= number_format($grand['civil'] ?? 0, 0) ?> · Material ₹<?= number_format($grand['material'] ?? 0, 0) ?></small>
    </div></div></div>
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4 text-primary">₹<?= number_format($grand['contracted'] ?? 0, 0) ?></div>
        <div class="text-muted small">Contracted (Sold + Booked)</div>
    </div></div></div>
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4 text-info">₹<?= number_format($grand['collected'] ?? 0, 0) ?></div>
        <div class="text-muted small">Cash Collected</div>
    </div></div></div>
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4 text-<?= $grandProfit >= 0 ? 'success' : 'danger' ?>">₹<?= number_format($grandProfit, 0) ?> (<?= $grandMargin ?>%)</div>
        <div class="text-muted small">Gross Profit &amp; Margin</div>
    </div></div></div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header"><span><i class="fas fa-table me-2"></i>Per-Colony Breakdown (<?= count($rows) ?>)</span></div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-4">No colonies found.</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>Colony</th><th class="text-end">Civil Work</th><th class="text-end">Material Used</th>
                <th class="text-end">Total Cost</th><th class="text-end">Contracted</th><th class="text-end">Collected</th>
                <th class="text-end">Profit</th><th class="text-end">Margin</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($r['name']) ?>
                        <small class="text-muted d-block"><?= (int)$r['plots'] ?> sold/booked plots</small></td>
                    <td class="text-end">₹<?= number_format($r['civil'], 0) ?></td>
                    <td class="text-end">₹<?= number_format($r['material'], 0) ?></td>
                    <td class="text-end fw-semibold">₹<?= number_format($r['cost'], 0) ?></td>
                    <td class="text-end text-primary">₹<?= number_format($r['contracted'], 0) ?></td>
                    <td class="text-end text-info">₹<?= number_format($r['collected'], 0) ?></td>
                    <td class="text-end fw-bold text-<?= $r['profit'] >= 0 ? 'success' : 'danger' ?>">₹<?= number_format($r['profit'], 0) ?></td>
                    <td class="text-end"><span class="badge bg-<?= $r['margin'] >= 20 ? 'success' : ($r['margin'] >= 0 ? 'warning text-dark' : 'danger') ?>"><?= $r['margin'] ?>%</span></td>
                    <td><a href="<?= $base ?>/admin/construction/civil-work" class="btn btn-sm btn-outline-primary">Log work</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<style>@media print { .btn { display: none !important; } .aps-cp-card { box-shadow: none !important; } }</style>
