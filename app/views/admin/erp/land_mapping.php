<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Farmer → Land → Plot Mapping</h4>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/erp/inventory" class="btn btn-outline-primary btn-sm"><i class="fas fa-cubes me-1"></i>Inventory</a>
        <a href="<?php echo BASE_URL; ?>/admin/erp/plot-profit" class="btn btn-outline-success btn-sm"><i class="fas fa-chart-line me-1"></i>P&L</a>
    </div>
</div>

<div class="card aps-cp-card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Farmer</th>
                    <th>Phone</th>
                    <th>Khasra #</th>
                    <th>Land Area</th>
                    <th>Purchase (₹)</th>
                    <th>Date</th>
                    <th>Registry #</th>
                    <th>Colony</th>
                    <th>Block</th>
                    <th>Plot #</th>
                    <th>Plot Area</th>
                    <th>Sale Price (₹)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                <tr><td colspan="13" class="text-center py-5 text-muted">
                    <i class="fas fa-database fa-2x mb-2 d-block"></i>
                    No mapping data found. Add farmers, land holdings, and plots first.
                </td></tr>
                <?php else: ?>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($row['farmer_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['farmer_phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['khasra_number'] ?? '-'); ?></td>
                    <td><?php echo number_format((float)($row['land_area'] ?? 0), 2); ?> <?php echo htmlspecialchars($row['land_area_unit'] ?? 'sqft'); ?></td>
                    <td>₹<?php echo $row['purchase_amount'] ? number_format((float)$row['purchase_amount'], 0) : '-'; ?></td>
                    <td><?php echo $row['purchase_date'] ?? '-'; ?></td>
                    <td><?php echo htmlspecialchars($row['registry_no'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['colony_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['block'] ?? '-'); ?></td>
                    <td class="fw-semibold"><?php echo htmlspecialchars($row['plot_number'] ?? $row['plot_code'] ?? '-'); ?></td>
                    <td><?php echo $row['area_sqft'] ? number_format((float)$row['area_sqft']) : '-'; ?></td>
                    <td>₹<?php echo $row['sale_price'] ? number_format((float)$row['sale_price'], 0) : '-'; ?></td>
                    <td>
                        <?php if ($row['plot_status'] ?? false): ?>
                        <span class="status-badge status-<?php echo e($row['plot_status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $row['plot_status'])); ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
