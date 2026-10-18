<?php
$types = $types ?? [];
$frequencies = $frequencies ?? [];
$users = $users ?? [];
$vendors = $vendors ?? [];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Recurring Transaction</h1>
        <a href="<?= BASE_URL ?>//admin/recurring" class="btn btn-secondary">Back</a>
    </div>
    <div class="card"><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>//admin/recurring/store">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row">
                <div class="col-md-4"><label>Type <span class="text-danger">*</span></label><select name="transaction_type" class="form-select" required><?php foreach ($types as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Amount (₹) <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" step="0.01" min="0.01" required></div>
                <div class="col-md-4"><label>Frequency</label><select name="frequency" class="form-select"><?php foreach ($frequencies as $f): ?><option value="<?= $f ?>"><?= ucfirst($f) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" value="<?= $today ?>" required></div>
                <div class="col-md-4"><label>End Date</label><input type="date" name="end_date" class="form-control"></div>
                <div class="col-md-4"><label>Max Executions</label><input type="number" name="max_executions" class="form-control" min="1" placeholder="Unlimited"></div>
                <div class="col-md-4"><label>User</label><select name="user_id" class="form-select"><option value="">None</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Vendor</label><select name="vendor_id" class="form-select"><option value="">None</option><?php foreach ($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Payment Method</label><input type="text" name="payment_method" class="form-control" placeholder="e.g., bank_transfer"></div>
                <div class="col-md-4"><label>Reference ID</label><input type="text" name="reference_id" class="form-control"></div>
                <div class="col-md-12"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Recurring Transaction</button></div>
            </div>
        </form>
    </div></div>
</div>