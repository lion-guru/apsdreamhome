<?php
$txn = $txn ?? [];
$types = $types ?? [];
$frequencies = $frequencies ?? [];
$users = $users ?? [];
$vendors = $vendors ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Recurring Transaction</h1>
        <a href="<?= BASE_URL ?>//admin/recurring" class="btn btn-secondary">Back</a>
    </div>
    <div class="card"><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>//admin/recurring/update/<?= $txn['id'] ?? '' ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row">
                <div class="col-md-4"><label>Type</label><select name="transaction_type" class="form-select"><?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= ($txn['transaction_type'] ?? '')===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Amount (₹)</label><input type="number" name="amount" class="form-control" step="0.01" min="0.01" value="<?= $txn['amount'] ?? '' ?>"></div>
                <div class="col-md-4"><label>Frequency</label><select name="frequency" class="form-select"><?php foreach ($frequencies as $f): ?><option value="<?= $f ?>" <?= ($txn['frequency'] ?? '')===$f?'selected':'' ?>><?= ucfirst($f) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Start Date</label><input type="date" name="start_date" class="form-control" value="<?= $txn['start_date'] ?? '' ?>"></div>
                <div class="col-md-4"><label>End Date</label><input type="date" name="end_date" class="form-control" value="<?= $txn['end_date'] ?? '' ?>"></div>
                <div class="col-md-4"><label>Max Executions</label><input type="number" name="max_executions" class="form-control" min="1" value="<?= $txn['max_executions'] ?? '' ?>"></div>
                <div class="col-md-4"><label>User</label><select name="user_id" class="form-select"><option value="">None</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= ($txn['user_id'] ?? '')==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Vendor</label><select name="vendor_id" class="form-select"><option value="">None</option><?php foreach ($vendors as $v): ?><option value="<?= $v['id'] ?>" <?= ($txn['vendor_id'] ?? '')==$v['id']?'selected':'' ?>><?= htmlspecialchars($v['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Payment Method</label><input type="text" name="payment_method" class="form-control" value="<?= htmlspecialchars($txn['payment_method'] ?? '') ?>"></div>
                <div class="col-md-4"><label>Reference ID</label><input type="text" name="reference_id" class="form-control" value="<?= htmlspecialchars($txn['reference_id'] ?? '') ?>"></div>
                <div class="col-md-12"><label>Description</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($txn['description'] ?? '') ?></textarea></div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?= !empty($txn['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Recurring Transaction</button></div>
            </div>
        </form>
    </div></div>
</div>