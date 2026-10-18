<?php
$asset = $asset ?? [];
$departments = $departments ?? [];
$employees = $employees ?? [];
$categories = $categories ?? ['building','vehicle','furniture','equipment','land','computer','other'];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Asset: <?= htmlspecialchars($asset['asset_name'] ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/fixed-assets" class="btn btn-secondary">Back</a>
    </div>
    <div class="card"><div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/fixed-assets/update/<?= $asset['id'] ?? '' ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="row g-3">
                <div class="col-md-4"><label>Asset Name <span class="text-danger">*</span></label><input type="text" name="asset_name" class="form-control" required value="<?= htmlspecialchars($asset['asset_name'] ?? '') ?>"></div>
                <div class="col-md-4"><label>Category</label><select name="category" class="form-select"><?php foreach ($categories as $c): ?><option value="<?= $c ?>" <?= ($asset['category'] ?? '')===$c?'selected':'' ?>><?= ucfirst($c) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Status</label><select name="status" class="form-select"><?php foreach (['active','disposed','sold','stolen','damaged','retired'] as $s): ?><option value="<?= $s ?>" <?= ($asset['status'] ?? '')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Location</label><input type="text" name="location" class="form-control" value="<?= htmlspecialchars($asset['location'] ?? '') ?>"></div>
                <div class="col-md-4"><label>Department</label><select name="department_id" class="form-select"><option value="">None</option><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($asset['department_id'] ?? '')==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Assigned To</label><select name="assigned_to" class="form-select"><option value="">None</option><?php foreach ($employees as $e): ?><option value="<?= $e['id'] ?>" <?= ($asset['assigned_to'] ?? '')==$e['id']?'selected':'' ?>><?= htmlspecialchars($e['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label>Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control" value="<?= $asset['warranty_expiry'] ?? '' ?>"></div>
                <div class="col-md-12"><label>Notes</label><textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($asset['notes'] ?? '') ?></textarea></div>
                <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Asset</button></div>
            </div>
        </form>
    </div></div>
</div>