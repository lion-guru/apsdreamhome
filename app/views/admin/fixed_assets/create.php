<?php
$departments = $departments ?? [];
$employees = $employees ?? [];
$categories = $categories ?? ['building','vehicle','furniture','equipment','land','computer','other'];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Fixed Asset</h1>
        <a href="<?= BASE_URL ?>/admin/fixed-assets" class="btn btn-secondary">Back</a>
    </div>
    <div class="card"><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/fixed-assets/store">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row">
                <div class="col-md-4"><label>Asset Code</label><input type="text" name="asset_code" class="form-control" required></div>
                <div class="col-md-4"><label>Asset Name</label><input type="text" name="asset_name" class="form-control" required></div>
                <div class="col-md-4"><label>Category</label><select name="category" class="form-select" required><?php foreach ($categories as $c) { ?><option value="<?= $c ?>"><?= ucfirst($c) ?></option><?php } ?></select></div>
                <div class="col-md-4"><label>Serial Number</label><input type="text" name="serial_number" class="form-control"></div>
                <div class="col-md-4"><label>Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="<?= $today ?>" required></div>
                <div class="col-md-4"><label>Purchase Cost</label><input type="number" name="purchase_cost" class="form-control" step="0.01" min="0" required></div>
                <div class="col-md-4"><label>Salvage Value</label><input type="number" name="salvage_value" class="form-control" step="0.01" value="0"></div>
                <div class="col-md-4"><label>Useful Life Years</label><input type="number" name="useful_life_years" class="form-control" min="1" value="5" required></div>
                <div class="col-md-4"><label>Method</label><select name="depreciation_method" class="form-select"><option value="straight_line">Straight Line</option><option value="declining_balance">Declining Balance</option></select></div>
                <div class="col-md-4"><label>Depreciation Rate</label><input type="number" name="depreciation_rate" class="form-control" step="0.01" value="20"></div>
                <div class="col-md-4"><label>Location</label><input type="text" name="location" class="form-control"></div>
                <div class="col-md-4"><label>Department</label><select name="department_id" class="form-select"><option value="">None</option><?php foreach ($departments as $d) { ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php } ?></select></div>
                <div class="col-md-4"><label>Assigned To</label><select name="assigned_to" class="form-select"><option value="">None</option><?php foreach ($employees as $e) { ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option><?php } ?></select></div>
                <div class="col-md-4"><label>Vendor</label><input type="text" name="vendor_name" class="form-control"></div>
                <div class="col-md-4"><label>Invoice</label><input type="text" name="invoice_number" class="form-control"></div>
                <div class="col-md-4"><label>Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control"></div>
                <div class="col-md-12"><label>Notes</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                <div class="col-12"><button type="submit" class="btn btn-primary">Create Asset</button></div>
            </div>
        </form>
    </div></div>
</div>