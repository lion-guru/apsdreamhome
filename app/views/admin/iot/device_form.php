<?php
$device = $device ?? null;
$isEdit = !empty($device);
$catalog = $catalog ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
$cats = ['security'=>'Security','energy'=>'Energy','water'=>'Water','climate'=>'Climate','lighting'=>'Lighting','safety'=>'Safety','access'=>'Access','smart'=>'Smart'];
$statuses = ['offline'=>'Offline','online'=>'Online','configuring'=>'Configuring','fault'=>'Fault'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-server me-2"></i><?= $isEdit ? 'Edit' : 'Register' ?> Device</h2>
    <a href="<?= BASE_URL ?>/admin/iot/devices" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/iot/device/save">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $device['id'] ?>"><?php endif; ?>
            <div class="mb-3"><label class="form-label">Device Name</label><input type="text" name="name" class="form-control" value="<?= $isEdit ? htmlspecialchars($device['name']) : '' ?>" required></div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Catalog Template</label>
                    <select name="catalog_id" class="form-select" id="catalogSel">
                        <option value="">— None / Custom —</option>
                        <?php foreach ($catalog as $c): ?><option value="<?= $c['id'] ?>" <?= $isEdit && ($device['catalog_id'] ?? '')==$c['id']?'selected':'' ?> data-cat="<?= $c['category'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3"><label class="form-label">Device UID / Serial</label><input type="text" name="device_uid" class="form-control" value="<?= $isEdit ? htmlspecialchars($device['device_uid'] ?? '') : '' ?>" placeholder="MAC / serial number"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" id="catSel">
                        <?php foreach ($cats as $k=>$l): ?><option value="<?= $k ?>" <?= $isEdit && ($device['category'] ?? '')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach ($statuses as $k=>$l): ?><option value="<?= $k ?>" <?= $isEdit && ($device['status'] ?? '')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 mb-3"><label class="form-label">Firmware</label><input type="text" name="firmware_version" class="form-control" value="<?= $isEdit ? htmlspecialchars($device['firmware_version'] ?? '') : '' ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Property ID</label><input type="number" name="property_id" class="form-control" value="<?= $isEdit ? ($device['property_id'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Colony ID</label><input type="number" name="colony_id" class="form-control" value="<?= $isEdit ? ($device['colony_id'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Location / Zone</label><input type="text" name="location" class="form-control" value="<?= $isEdit ? htmlspecialchars($device['location'] ?? '') : '' ?>" placeholder="e.g. Main Gate"></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
        </form>
    </div>
</div>

<script>
document.getElementById('catalogSel')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const cat = opt.getAttribute('data-cat');
    if (cat) document.getElementById('catSel').value = cat;
});
</script>
