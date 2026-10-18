<?php
$data = $data ?? ['method' => 'weighted_average', 'method_label' => 'Weighted Average', 'materials' => [], 'total_quantity' => 0, 'total_value' => 0, 'count' => 0];
$method = $method ?? 'weighted_average';
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Inventory Valuation</h1>
        <div>
            <form method="get" class="d-inline" onsubmit="return confirm('Change valuation method to ' + document.querySelector('select[name=\"method\"]').value + '?');">
                <input type="hidden" name="method" id="methodSelect" value="<?= htmlspecialchars($method) ?>">
                <label class="me-2" style="margin-bottom:0;">Method:</label>
                <select name="method" class="form-select form-select-sm d-inline w-auto" style="display:none;" id="methodField">
                    <option value="weighted_average" <?= $method === 'weighted_average' ? 'selected' : '' ?>>Weighted Average</option>
                    <option value="fifo" <?= $method === 'fifo' ? 'selected' : '' ?>>FIFO</option>
                    <option value="lifo" <?= $method === 'lifo' ? 'selected' : '' ?>>LIFO</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-calculator me-2"></i>Apply</button>
            </form>
            <a href="<?= BASE_URL ?>/admin/inventory" class="btn btn-sm btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-2"></i>Back to Inventory</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $data['count'] ?></h3><small>Materials</small></div></div></div>
        <div class="col-md-4"><div class="card bg-success text-white"><div class="card-body"><h3><?= (float)$data['total_quantity'] ?></h3><small>Total Units</small></div></div></div>
        <div class="col-md-4"><div class="card bg-info text-white"><div class="card-body"><h3><?= number_format((float)$data['total_value'], 2) ?></h3><small>Total Value (<?= htmlspecialchars($data['method_label']) ?>)</small></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Stock Valuation Detail</h5></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Value</th>
                        <th class="text-end">Book Value</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th class="text-end">Method</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data['materials'])): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No materials with stock found.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['materials'] as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['material_name']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($m['material_category'])) ?></td>
                            <td><?= htmlspecialchars($m['unit']) ?></td>
                            <td class="text-end"><?= number_format((float)$m['quantity'], 2) ?></td>
                            <td class="text-end"><?= number_format((float)$m['unit_cost'], 4) ?></td>
                            <td class="text-end"><?= number_format((float)$m['total_value'], 2) ?></td>
                            <td class="text-end font-weight-bold"><?= number_format((float)$m['book_value'], 2) ?></td>
                            <td><span class="badge bg-<?= $m['status'] === 'in_stock' ? 'success' : ($m['status'] === 'low_stock' ? 'warning' : 'danger') ?>"><?= ucfirst(str_replace('_', ' ', $m['status'])) ?></span></td>
                            <td><?= htmlspecialchars($m['location'] ?? '-') ?></td>
                            <td class="text-end"><span class="badge bg-light text-dark"><?= htmlspecialchars($m['method']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($data['materials'])): ?>
                <tfoot>
                    <tr class="table-light font-weight-bold">
                        <td colspan="4" class="text-end">Total</td>
                        <td class="text-end"><?= number_format((float)$data['total_quantity'], 2) ?></td>
                        <td class="text-end">-</td>
                        <td class="text-end"><?= number_format((float)$data['total_value'], 2) ?></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
