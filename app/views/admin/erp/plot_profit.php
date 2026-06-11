<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Plot P&L Report</h4>
    <a href="<?php echo BASE_URL; ?>/admin/erp/inventory" class="btn btn-outline-primary btn-sm"><i class="fas fa-cubes me-1"></i>Inventory</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <small class="text-muted">Total Sale Value</small>
                <div class="fs-4 fw-bold text-success">₹<?php echo number_format($totals['sale_price'], 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <small class="text-muted">Total Land Cost</small>
                <div class="fs-4 fw-bold text-danger">₹<?php echo number_format($totals['land_cost'], 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <small class="text-muted">Total Dev Cost</small>
                <div class="fs-4 fw-bold text-warning">₹<?php echo number_format($totals['dev_cost'], 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <small class="text-muted">Net Profit</small>
                <div class="fs-4 fw-bold <?php echo $totals['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                    ₹<?php echo number_format($totals['profit'], 0); ?>
                    <small>(<?php echo $totals['margin_pct']; ?>%)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card aps-cp-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Plot #</th>
                    <th>Colony</th>
                    <th>Area (sqft)</th>
                    <th>Land Cost (₹)</th>
                    <th>Dev Cost (₹)</th>
                    <th>Total Cost (₹)</th>
                    <th>Sale Price (₹)</th>
                    <th>Profit (₹)</th>
                    <th>Margin</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($plots)): ?>
                <tr><td colspan="10" class="text-center py-4 text-muted">No data available.</td></tr>
                <?php else: ?>
                <?php foreach ($plots as $p): ?>
                <?php $profitClass = $p['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($p['plot_number'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($p['colony_name'] ?? '-'); ?></td>
                    <td><?php echo number_format((float)($p['area_sqft'] ?? 0)); ?></td>
                    <td>₹<?php echo number_format((float)($p['land_cost'] ?? 0), 0); ?></td>
                    <td>₹<?php echo number_format((float)($p['dev_cost'] ?? 0), 0); ?></td>
                    <td class="fw-semibold">₹<?php echo number_format((float)($p['total_cost'] ?? 0), 0); ?></td>
                    <td>₹<?php echo number_format((float)($p['sale_price'] ?? 0), 0); ?></td>
                    <td class="fw-bold <?php echo $profitClass; ?>">₹<?php echo number_format((float)($p['profit'] ?? 0), 0); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $p['margin_pct'] >= 20 ? 'success' : ($p['margin_pct'] >= 0 ? 'warning' : 'danger'); ?>">
                            <?php echo $p['margin_pct']; ?>%
                        </span>
                    </td>
                    <td><span class="status-badge status-<?php echo $p['status'] ?? 'available'; ?>"><?php echo ucfirst(str_replace('_', ' ', $p['status'] ?? 'available')); ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

