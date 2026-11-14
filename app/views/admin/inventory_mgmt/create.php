<?php
$categories = $categories ?? ['cement','steel','sand','bricks','tiles','paint','plumbing','electrical','wood','glass','morang','gitti','damar','soling_stone','electric_pole','transformer','jcb_diesel','other'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Material</h1>
        <a href="<?= BASE_URL ?>/admin/inventory" class="btn btn-secondary">Back</a>
    </div>
    <div class="card"><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/inventory/store">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row">
                <div class="col-md-4"><label>Material Name <span class="text-danger">*</span></label><input aria-label="Material name" type="text" name="material_name" class="form-control" required></div>
                <div class="col-md-4"><label>Category <span class="text-danger">*</span></label><select aria-label="Material category" name="material_category" class="form-select" required><?php foreach ($categories as $c): ?><option value="<?= $c ?>"><?= ucfirst($c) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>SKU</label><input aria-label="Sku" type="text" name="sku" class="form-control" placeholder="e.g., CEM-OPC53"></div>
                <div class="col-md-3"><label>Unit <span class="text-danger">*</span></label><input aria-label="Unit" type="text" name="unit" class="form-control" value="Nos" required></div>
                <div class="col-md-3"><label>Current Stock <span class="text-danger">*</span></label><input aria-label="Current stock" type="number" name="current_stock" class="form-control" step="0.01" min="0" value="0" required></div>
                <div class="col-md-3"><label>Unit Cost (₹) <span class="text-danger">*</span></label><input aria-label="Unit cost" type="number" name="unit_cost" class="form-control" step="0.01" min="0" value="0" required></div>
                <div class="col-md-3"><label>Minimum Stock</label><input aria-label="Minimum stock" type="number" name="minimum_stock" class="form-control" step="0.01" min="0" value="0"></div>
                <div class="col-md-3"><label>Maximum Stock</label><input aria-label="Maximum stock" type="number" name="maximum_stock" class="form-control" step="0.01" min="0" value="0"></div>
                <div class="col-md-4"><label>Location</label><input aria-label="Location" type="text" name="location" class="form-control" placeholder="Warehouse / Site"></div>
                <div class="col-md-4"><label>Supplier Name</label><input aria-label="Supplier name" type="text" name="supplier_name" class="form-control"></div>
                <div class="col-md-4"><label>Supplier Contact</label><input aria-label="Supplier contact" type="text" name="supplier_contact" class="form-control"></div>
                <div class="col-md-12"><label>Notes</label><textarea aria-label="Notes" name="notes" class="form-control" rows="2"></textarea></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add Material</button></div>
            </div>
        </form>
    </div></div>
</div>