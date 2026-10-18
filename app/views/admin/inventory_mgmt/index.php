<?php
$materials = $materials ?? [];
$stats = $stats ?? ['total_materials'=>0,'in_stock'=>0,'low_stock'=>0,'out_of_stock'=>0,'total_value'=>0,'by_category'=>[]];
$filters = $filters ?? ['status'=>'','category'=>''];
$categories = $categories ?? [];
$statuses = $statuses ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Inventory Management</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/inventory/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Material</a>
            <a href="<?= BASE_URL ?>/admin/inventory/usage-log" class="btn btn-outline-secondary ms-2"><i class="fas fa-history me-2"></i>Usage Log</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total_materials'] ?></h3><small>Total Materials</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= $stats['in_stock'] ?></h3><small>In Stock</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= $stats['low_stock'] ?></h3><small>Low Stock</small></div></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_value'] ?? 0) ?></h3><small>Total Value</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Category</label><select name="category" class="form-select"><option value="">All</option><?php foreach ($categories as $c): ?><option value="<?= $c ?>" <?= $filters['category']===$c?'selected':'' ?>><?= ucfirst($c) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">All</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/inventory" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Name</th><th>Category</th><th>SKU</th><th>Stock</th><th>Min</th><th>Unit Cost</th><th>Value</th><th>Status</th><th>Location</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($materials)): ?>
            <tr><td colspan="10" class="text-center text-muted py-4">No materials found</td></tr>
        <?php else: ?>
            <?php foreach ($materials as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['material_name']) ?></td>
                    <td><?= ucfirst($m['material_category']) ?></td>
                    <td><code><?= htmlspecialchars($m['sku'] ?? '—') ?></code></td>
                    <td><?= $m['current_stock'] ?> <?= htmlspecialchars($m['unit']) ?></td>
                    <td><?= $m['minimum_stock'] ?></td>
                    <td>₹<?= number_format($m['unit_cost'], 2) ?></td>
                    <td>₹<?= number_format($m['total_value'], 2) ?></td>
                    <td>
                        <?php if ($m['status'] === 'in_stock'): ?>
                            <span class="badge bg-success">In Stock</span>
                        <?php elseif ($m['status'] === 'low_stock'): ?>
                            <span class="badge bg-warning">Low Stock</span>
                        <?php elseif ($m['status'] === 'out_of_stock'): ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Discontinued</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($m['location'] ?? '—') ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-success" onclick="stockIn(<?= $m['id'] ?>)"><i class="fas fa-plus"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="stockOut(<?= $m['id'] ?>)"><i class="fas fa-minus"></i></button>
                        <a href="<?= BASE_URL ?>/admin/inventory/edit/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table></div></div>
</div>

<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockModalTitle">Stock In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="stockForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="material_id" id="stockMaterialId">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Quantity</label>
                            <input type="number" name="quantity" id="stockQty" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6" id="stockCostWrap">
                            <label>Unit Cost (₹)</label>
                            <input type="number" name="unit_cost" id="stockCost" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Date</label>
                            <input type="date" name="usage_date" id="stockDate" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mt-2" id="stockColonyWrap" style="display:none">
                            <label>Colony</label>
                            <input type="number" name="colony_id" id="stockColony" class="form-control" placeholder="Colony ID">
                        </div>
                        <div class="col-md-6 mt-2" id="stockUsedByWrap" style="display:none">
                            <label>Used By</label>
                            <input type="text" name="used_by" id="stockUsedBy" class="form-control" placeholder="Person name">
                        </div>
                        <div class="col-md-6 mt-2" id="stockPurposeWrap" style="display:none">
                            <label>Purpose</label>
                            <input type="text" name="purpose" id="stockPurpose" class="form-control">
                        </div>
                        <div class="col-md-12 mt-2">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="stockForm" class="btn btn-primary" id="stockSubmitBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function stockIn(id) {
    document.getElementById('stockModalTitle').textContent = 'Stock In';
    document.getElementById('stockMaterialId').value = id;
    document.getElementById('stockCostWrap').style.display = '';
    document.getElementById('stockColonyWrap').style.display = 'none';
    document.getElementById('stockUsedByWrap').style.display = 'none';
    document.getElementById('stockPurposeWrap').style.display = 'none';
    document.getElementById('stockForm').action = '<?= BASE_URL ?>/admin/inventory/stock-in';
    document.getElementById('stockSubmitBtn').textContent = 'Add Stock';
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}
function stockOut(id) {
    document.getElementById('stockModalTitle').textContent = 'Stock Out';
    document.getElementById('stockMaterialId').value = id;
    document.getElementById('stockCostWrap').style.display = 'none';
    document.getElementById('stockColonyWrap').style.display = '';
    document.getElementById('stockUsedByWrap').style.display = '';
    document.getElementById('stockPurposeWrap').style.display = '';
    document.getElementById('stockForm').action = '<?= BASE_URL ?>/admin/inventory/stock-out';
    document.getElementById('stockSubmitBtn').textContent = 'Issue Stock';
    new bootstrap.Modal(document.getElementById('stockModal')).show();
}
</script>