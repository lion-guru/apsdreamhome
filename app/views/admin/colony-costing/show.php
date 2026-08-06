<?php
$costing    = $costing    ?? [];
$line_items = $line_items ?? [];
$history    = $history    ?? [];
$base       = defined('BASE_URL') ? BASE_URL : '';

$costingId  = (int)($costing['id']        ?? 0);
$colonyId   = (int)($costing['colony_id'] ?? 0);

// Group line items
$deductions = array_filter($line_items, fn($i) => $i['is_deduction']);
$costs      = array_filter($line_items, fn($i) => !$i['is_deduction']);

function fmtRs(float $v): string {
    return '₹' . number_format($v, 2);
}
function fmtSqft(float $v): string {
    return number_format($v, 0) . ' SqFt';
}
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0">
            <i class="fas fa-chart-bar me-2"></i>
            Costing Report: <?= htmlspecialchars($costing['colony_name'] ?? '') ?>
            <span class="badge bg-secondary ms-2">v<?= (int)($costing['version'] ?? 1) ?></span>
            <?php if ($costing['is_approved']): ?>
                <span class="badge bg-success ms-1"><i class="fas fa-check me-1"></i>Approved</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark ms-1">Pending Approval</span>
            <?php endif; ?>
        </h4>
        <div>
            <a href="<?= $base ?>/admin/colony-costing/create/<?= $colonyId ?>"
               class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-edit me-1"></i>Revise Costing
            </a>
            <a href="<?= $base ?>/admin/colony-costing" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-light">
                <div class="card-body py-3">
                    <div class="small text-muted">Net Sellable Area</div>
                    <div class="h4 text-primary"><?= fmtSqft((float)($costing['net_sellable_sqft'] ?? 0)) ?></div>
                    <div class="small text-muted">of <?= fmtSqft((float)($costing['total_land_sqft'] ?? 0)) ?> total</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-light">
                <div class="card-body py-3">
                    <div class="small text-muted">Landing Cost / SqFt</div>
                    <div class="h4 text-warning"><?= fmtRs((float)($costing['landing_cost_sqft'] ?? 0)) ?></div>
                    <div class="small text-muted">(Cost before markup)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 bg-light">
                <div class="card-body py-3">
                    <div class="small text-muted">Suggested Price / SqFt</div>
                    <div class="h4 text-info"><?= fmtRs((float)($costing['suggested_price_sqft'] ?? 0)) ?></div>
                    <div class="small text-muted">(System calculated)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-success border-2">
                <div class="card-body py-3">
                    <div class="small text-muted">Final Selling Price / SqFt</div>
                    <div class="h4 text-success"><strong><?= fmtRs((float)($costing['final_price_sqft'] ?? 0)) ?></strong></div>
                    <div class="small text-muted"><?= $costing['is_approved'] ? '✅ Admin-Approved' : '⏳ Pending' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Wastage Analysis -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-slash me-2"></i>Land Area & Wastage
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr><td>Total Land Purchased</td><td class="text-end fw-bold"><?= fmtSqft((float)($costing['total_land_sqft'] ?? 0)) ?></td></tr>
                            <tr><td class="text-danger">Roads (<?= $costing['road_wastage_pct'] ?? 0 ?>%)</td><td class="text-end text-danger">−<?= fmtSqft((float)($costing['total_land_sqft'] ?? 0) * (float)($costing['road_wastage_pct'] ?? 0) / 100) ?></td></tr>
                            <tr><td class="text-danger">Drainage (<?= $costing['drainage_wastage_pct'] ?? 0 ?>%)</td><td class="text-end text-danger">−<?= fmtSqft((float)($costing['total_land_sqft'] ?? 0) * (float)($costing['drainage_wastage_pct'] ?? 0) / 100) ?></td></tr>
                            <tr><td class="text-danger">Park (<?= $costing['park_wastage_pct'] ?? 0 ?>%)</td><td class="text-end text-danger">−<?= fmtSqft((float)($costing['total_land_sqft'] ?? 0) * (float)($costing['park_wastage_pct'] ?? 0) / 100) ?></td></tr>
                            <?php if ((float)($costing['other_wastage_pct'] ?? 0) > 0): ?>
                                <tr><td class="text-danger">Other (<?= $costing['other_wastage_pct'] ?>%)</td><td class="text-end text-danger">−<?= fmtSqft((float)($costing['total_land_sqft'] ?? 0) * (float)($costing['other_wastage_pct'] ?? 0) / 100) ?></td></tr>
                            <?php endif; ?>
                            <tr class="table-success fw-bold border-top"><td>Net Sellable Area</td><td class="text-end"><?= fmtSqft((float)($costing['net_sellable_sqft'] ?? 0)) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cost Breakdown per SqFt -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-layer-group me-2"></i>Cost Breakdown / Sellable SqFt
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr><td>Land Acquisition</td><td class="text-end"><?= fmtRs((float)($costing['land_purchase_rate'] ?? 0)) ?>/SqFt raw → allocated</td></tr>
                            <tr><td>Road Development</td><td class="text-end"><?= fmtRs((float)($costing['road_dev_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr><td>Drainage</td><td class="text-end"><?= fmtRs((float)($costing['drainage_dev_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr><td>Electricity</td><td class="text-end"><?= fmtRs((float)($costing['electricity_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr><td>Water Pipeline</td><td class="text-end"><?= fmtRs((float)($costing['water_pipeline_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr><td>Boundary Wall</td><td class="text-end"><?= fmtRs((float)($costing['boundary_wall_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr><td>Other Development</td><td class="text-end"><?= fmtRs((float)($costing['other_dev_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr><td>Admin Overhead (<?= $costing['admin_overhead_pct'] ?? 5 ?>%)</td><td class="text-end">—</td></tr>
                            <tr class="table-warning fw-bold border-top"><td>Total Landing Cost</td><td class="text-end"><?= fmtRs((float)($costing['landing_cost_sqft'] ?? 0)) ?>/SqFt</td></tr>
                            <tr class="text-muted"><td>MLM Commission (<?= $costing['marketing_commission_pct'] ?? 20 ?>%) of price</td><td class="text-end">—</td></tr>
                            <tr class="text-muted"><td>Profit Margin (<?= $costing['target_profit_pct'] ?? 20 ?>%) of price</td><td class="text-end">—</td></tr>
                            <tr class="table-success fw-bold"><td>Suggested Selling Price</td><td class="text-end"><?= fmtRs((float)($costing['suggested_price_sqft'] ?? 0)) ?>/SqFt</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$costing['is_approved']): ?>
        <!-- Approve Form -->
        <div class="card shadow-sm mt-4 border-success">
            <div class="card-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>Approve This Costing
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $base ?>/admin/colony-costing/approve/<?= $costingId ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label" for="final_approve_price">Final Selling Price (₹/SqFt) <span class="text-danger">*</span></label>
                            <input type="number" id="final_approve_price" name="final_price_sqft" class="form-control form-control-lg"
                                   value="<?= number_format((float)($costing['suggested_price_sqft'] ?? 0), 2, '.', '') ?>"
                                   step="0.01" min="0" required>
                            <small class="text-muted">Suggested: <?= fmtRs((float)($costing['suggested_price_sqft'] ?? 0)) ?></small>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2"></i>Approve & Set Final Price
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Version History -->
    <?php if (count($history) > 1): ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-history me-2"></i>Costing Version History
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Version</th><th>Label</th><th>Landing Cost</th><th>Suggested</th><th>Final</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr class="<?= $h['id'] == $costingId ? 'table-primary' : '' ?>">
                                <td>v<?= (int)$h['version'] ?></td>
                                <td><?= htmlspecialchars($h['costing_label'] ?? '') ?></td>
                                <td><?= fmtRs((float)$h['landing_cost_sqft']) ?></td>
                                <td><?= fmtRs((float)$h['suggested_price_sqft']) ?></td>
                                <td><?= fmtRs((float)$h['final_price_sqft']) ?></td>
                                <td><?= $h['is_approved'] ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-secondary">Draft</span>' ?></td>
                                <td><?= date('d M Y', strtotime($h['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
