<?php
$colonies = $colonies ?? [];
$base     = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-calculator me-2"></i>Colony Land Costing & Plot Pricing</h4>
    </div>

    <?php if (empty($colonies)): ?>
        <div class="alert alert-info">No colonies found. Create a colony first from Colony Management.</div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Colony Name</th>
                                <th class="text-end">Net Sellable (SqFt)</th>
                                <th class="text-end">Landing Cost/SqFt</th>
                                <th class="text-end">Suggested Price/SqFt</th>
                                <th class="text-end">Final Price/SqFt</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colonies as $col): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($col['colony_name'] ?? '') ?></strong></td>
                                    <td class="text-end">
                                        <?= isset($col['net_sellable_sqft']) ? number_format((float)$col['net_sellable_sqft'], 0) . ' SqFt' : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <?= isset($col['landing_cost_sqft']) && $col['landing_cost_sqft'] > 0 ? '₹' . number_format((float)$col['landing_cost_sqft'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <?= isset($col['suggested_price_sqft']) && $col['suggested_price_sqft'] > 0 ? '₹' . number_format((float)$col['suggested_price_sqft'], 2) : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <?= isset($col['final_price_sqft']) && $col['final_price_sqft'] > 0 ? '<strong>₹' . number_format((float)$col['final_price_sqft'], 2) . '</strong>' : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (empty($col['costing_id'])): ?>
                                            <span class="badge bg-secondary">Not Costed</span>
                                        <?php elseif ($col['is_approved']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending Approval</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($col['costing_id'])): ?>
                                            <a href="<?= $base ?>/admin/colony-costing/<?= (int)$col['costing_id'] ?>"
                                               class="btn btn-sm btn-outline-primary me-1" title="View Report">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= $base ?>/admin/colony-costing/create/<?= (int)$col['colony_id'] ?>"
                                           class="btn btn-sm btn-outline-success" title="<?= empty($col['costing_id']) ? 'Add Costing' : 'Revise Costing' ?>">
                                            <i class="fas fa-<?= empty($col['costing_id']) ? 'plus' : 'edit' ?>"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
