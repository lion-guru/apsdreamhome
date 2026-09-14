<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-boxes-stacked me-2"></i>Material Inventory &amp; Usage</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/projects/progress" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Project Progress</a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#usageModal"><i class="fas fa-box-arrow-in-down me-1"></i>Log Usage</button>
        </div>
    </div>

    <div class="card card-body shadow-sm mb-4">
        <form method="get" action="<?= BASE_URL ?>/admin/construction/material-inventory" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1">Category</label>
                <select name="category" class="form-select">
                    <option value="">-- All Categories --</option>
                    <?php
                    $categories = ['cement', 'steel', 'sand', 'bricks', 'tiles', 'paint', 'plumbing', 'electrical', 'wood', 'glass', 'other'];
                    foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= (($active_category ?? '') === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($cat)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- All Statuses --</option>
                    <?php
                    $statuses = ['in_stock' => 'In Stock', 'low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock', 'discontinued' => 'Discontinued'];
                    foreach ($statuses as $key => $label): ?>
                        <option value="<?= $key ?>" <?= (($active_status ?? '') === $key) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
            <?php if (!empty($active_category ?? '') || !empty($active_status ?? '')): ?>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/construction/material-inventory" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <?php
    $lowMaterials = array_filter($materials ?? [], function($m){ return in_array($m['status'] ?? '', ['low_stock','out_of_stock'], true); });
    if (!empty($lowMaterials)): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-triangle-exclamation fa-lg me-3"></i>
            <div><strong>Low stock alert:</strong> <?= count($lowMaterials) ?> material(s) need restocking — <?= htmlspecialchars(implode(', ', array_map(function($m){ return $m['material_name'] ?? $m['name'] ?? ''; }, array_slice($lowMaterials, 0, 5)))) ?><?= count($lowMaterials) > 5 ? ' +' . (count($lowMaterials)-5) . ' more' : '' ?>.</div>
        </div>
    <?php endif; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-0">₹<?= number_format((float)($total_value ?? 0), 0) ?></h2>
                    <small>Inventory Value</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm bg-info text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= count($materials ?? []) ?></h2>
                    <small>Material Types</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm bg-warning text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?php
                        $lowCount = 0;
                        foreach ($materials ?? [] as $m) {
                            if (in_array($m['status'] ?? '', ['low_stock', 'out_of_stock'], true)) { $lowCount++; }
                        }
                        echo $lowCount;
                    ?></h2>
                    <small>Low / Out of Stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm bg-success text-white h-100">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= count($usage_logs ?? []) ?></h2>
                    <small>Recent Usage Entries</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Materials</h5>
        </div>
        <div class="card-body p-0 aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Material</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Unit</th>
                            <th>Unit Cost</th>
                            <th>Total Value</th>
                            <th>Supplier</th>
                            <th>Last Restocked</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materials ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5>No Materials</h5>
                                <p class="mb-3">No materials found for the selected filters.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($materials as $i => $m): ?>
                                <?php $name = $m['material_name'] ?? $m['name'] ?? ''; ?>
                                <?php $stock = (float)($m['current_stock'] ?? 0); ?>
                                <?php $minStock = $m['minimum_stock'] !== null ? (float)$m['minimum_stock'] : null; ?>
                                <?php $st = $m['status'] ?? 'in_stock'; ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($name) ?></strong>
                                        <?php if (!empty($m['sku'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($m['sku']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($m['material_category'] ?? 'other')) ?></span></td>
                                    <td class="text-<?= ($minStock !== null && $stock <= $minStock) ? 'danger' : 'success' ?>">
                                        <strong><?= number_format($stock, 2) ?></strong>
                                        <?php if ($minStock !== null): ?>
                                            <span class="text-muted">/ min <?= number_format($minStock, 2) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($m['unit'] ?? '') ?></td>
                                    <td>₹<?= number_format((float)($m['unit_cost'] ?? 0), 2) ?></td>
                                    <td>₹<?= number_format((float)($m['total_value'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php if (!empty($m['supplier_name'])): ?>
                                            <?= htmlspecialchars($m['supplier_name']) ?>
                                            <?php if (!empty($m['supplier_contact'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($m['supplier_contact']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($m['last_restocked_at']) ? date('d M Y', strtotime($m['last_restocked_at'])) : '—' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $st === 'in_stock' ? 'success' : ($st === 'low_stock' ? 'warning' : ($st === 'out_of_stock' ? 'danger' : 'secondary')) ?>">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($st))) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Recent Usage Log</h5>
        </div>
        <div class="card-body p-0 aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Material</th>
                            <th>Colony</th>
                            <th>Quantity</th>
                            <th>Purpose</th>
                            <th>Used By</th>
                            <th>Usage Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usage_logs ?? [])): ?>
                            <tr><td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                <h5>No Usage Logged</h5>
                                <p class="mb-3">Log material usage to start tracking consumption.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($usage_logs as $i => $l): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($l['material_name'] ?? '') ?></strong>
                                        <br><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($l['material_category'] ?? 'other')) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($l['colony_name'] ?? '—') ?></td>
                                    <td><strong><?= number_format((float)($l['quantity'] ?? 0), 2) ?></strong> <?= htmlspecialchars($l['unit'] ?? 'qty') ?></td>
                                    <td><?= htmlspecialchars($l['purpose'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($l['used_by'] ?? '—') ?></td>
                                    <td><?= !empty($l['usage_date']) ? date('d M Y', strtotime($l['usage_date'])) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="usageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="<?= BASE_URL ?>/admin/construction/material-inventory/log-usage">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-box-arrow-in-down me-2"></i>Log Material Usage</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Material <span class="text-danger">*</span></label>
                                <select name="material_id" class="form-select" required>
                                    <option value="">-- Select material --</option>
                                    <?php foreach ($materials ?? [] as $m): ?>
                                        <?php $mName = $m['material_name'] ?? $m['name'] ?? ''; ?>
                                        <option value="<?= (int)($m['id'] ?? 0) ?>" <?= in_array($m['status'] ?? '', ['out_of_stock', 'discontinued'], true) ? 'disabled' : '' ?>>
                                            <?= htmlspecialchars($mName) ?>
                                            (stock: <?= number_format((float)($m['current_stock'] ?? 0), 2) ?><?= htmlspecialchars($m['unit'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Colony</label>
                                <select name="colony_id" class="form-select">
                                    <option value="">-- Optional --</option>
                                    <?php foreach ($colonies ?? [] as $c): ?>
                                        <option value="<?= (int)($c['id'] ?? 0) ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plot ID</label>
                                <input type="number" name="plot_id" class="form-control" min="1" step="1" placeholder="Optional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required value="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control" maxlength="20" value="qty" placeholder="bags, kg, sqft...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Purpose</label>
                                <input type="text" name="purpose" class="form-control" maxlength="255" placeholder="e.g. Road laying, Boundary wall">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Used By</label>
                                <input type="text" name="used_by" class="form-control" maxlength="255" placeholder="Contractor / employee name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Usage Date</label>
                                <input type="date" name="usage_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" maxlength="2000"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Log Usage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>