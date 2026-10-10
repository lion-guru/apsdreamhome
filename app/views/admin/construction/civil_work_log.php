<?php
/** @var array $colonies */
/** @var array $logs */
/** @var float $totalMonth */
/** @var array $work_types */
$colonies = $colonies ?? []; $logs = $logs ?? [];
$totalMonth = $totalMonth ?? 0; $work_types = $work_types ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$units = ['hours' => 'Hours', 'brass' => 'Brass', 'cft' => 'CFT', 'sqft' => 'Sqft', 'rft' => 'RFT', 'pcs' => 'Pcs', 'bags' => 'Bags', 'kg' => 'Kg', 'trip' => 'Trip', 'day' => 'Day', 'qty' => 'Qty'];
$typeColors = ['jcb_hours' => 'warning', 'road_soling' => 'secondary', 'morang' => 'secondary', 'gitti' => 'secondary', 'damar' => 'dark', 'brickwork' => 'danger', 'boundary_wall' => 'danger', 'pillar_demarcation' => 'info', 'cement_work' => 'secondary', 'electric_pole' => 'warning', 'transformer' => 'warning', 'drainage' => 'info', 'gate' => 'primary', 'other' => 'light'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-tractor me-2"></i>Civil Work Log — Roads, JCB &amp; Site Work</h4>
        <p class="text-muted mb-0">Machine hours, soling, morang, gitti, damar, poles — work with no stock to decrement</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/construction/colony-cost-profit" class="btn btn-success btn-sm"><i class="fas fa-chart-line me-1"></i>Cost vs Profit</a>
        <a href="<?= $base ?>/admin/construction/material-inventory" class="btn btn-outline-secondary btn-sm"><i class="fas fa-boxes me-1"></i>Material Inventory</a>
    </div>
</div>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-body py-3 d-flex align-items-center gap-3 flex-wrap">
        <div><span class="text-muted small d-block">Spent This Month</span>
        <strong class="h4 text-danger">₹<?= number_format($totalMonth, 0) ?></strong></div>
        <div class="ms-auto text-muted small"><?= count($logs) ?> recent entries</div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header"><span><i class="fas fa-plus me-2"></i>Log Site Work</span></div>
            <div class="aps-cp-card-body">
                <form method="POST" action="<?= $base ?>/admin/construction/civil-work/store" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="col-12"><label class="form-label small">Colony</label>
                        <select name="colony_id" class="form-select form-select-sm" required>
                            <option value="">— Select —</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-6"><label class="form-label small">Date</label>
                        <input type="date" name="work_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="col-6"><label class="form-label small">Work Type</label>
                        <select name="work_type" class="form-select form-select-sm">
                            <?php foreach ($work_types as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-12"><label class="form-label small">Description</label>
                        <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. JCB levelling Block B"></div>
                    <div class="col-4"><label class="form-label small">Qty</label>
                        <input type="number" step="0.01" min="0" name="quantity" class="form-control form-control-sm" required></div>
                    <div class="col-4"><label class="form-label small">Unit</label>
                        <select name="unit" class="form-select form-select-sm">
                            <?php foreach ($units as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-4"><label class="form-label small">Rate (₹)</label>
                        <input type="number" step="0.01" min="0" name="rate" class="form-control form-control-sm" required></div>
                    <div class="col-12"><label class="form-label small">Vendor / Contractor</label>
                        <input type="text" name="vendor_name" class="form-control form-control-sm"></div>
                    <div class="col-12"><label class="form-label small">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea></div>
                    <div class="col-12"><button class="btn btn-primary btn-sm w-100">Log Work (auto-totals amount)</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header"><span><i class="fas fa-list me-2"></i>Recent Entries</span></div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="text-center text-muted py-4">No civil work logged yet.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Colony</th><th>Work</th><th class="text-end">Qty</th><th class="text-end">Amount</th><th>Vendor</th></tr></thead>
                    <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($l['work_date'])) ?></td>
                            <td><?= htmlspecialchars($l['colony_name'] ?: '—') ?></td>
                            <td><span class="badge bg-<?= $typeColors[$l['work_type']] ?? 'secondary' ?>"><?= htmlspecialchars($work_types[$l['work_type']] ?? $l['work_type']) ?></span>
                                <div class="small text-muted"><?= htmlspecialchars($l['description'] ?: '') ?></div></td>
                            <td class="text-end"><?= number_format((float)$l['quantity'], 2) ?> <?= htmlspecialchars($l['unit']) ?></td>
                            <td class="text-end fw-bold">₹<?= number_format((float)$l['amount'], 0) ?></td>
                            <td class="small"><?= htmlspecialchars($l['vendor_name'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
