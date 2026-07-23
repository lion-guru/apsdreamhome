<?php
$item = $item ?? null;
$isEdit = !empty($item);
$csrf = $_SESSION['csrf_token'] ?? '';
$cats = ['security'=>'Security','energy'=>'Energy','water'=>'Water','climate'=>'Climate','lighting'=>'Lighting','safety'=>'Safety','access'=>'Access','smart'=>'Smart'];
$protos = ['wifi'=>'WiFi','zigbee'=>'Zigbee','zwave'=>'Z-Wave','ble'=>'Bluetooth','lora'=>'LoRa','mqtt'=>'MQTT','cellular'=>'Cellular'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-microchip me-2"></i><?= $isEdit ? 'Edit' : 'Add' ?> Catalog Item</h2>
    <a href="<?= BASE_URL ?>/admin/iot/catalog" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/iot/catalog/save">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $item['id'] ?>"><?php endif; ?>
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= $isEdit ? htmlspecialchars($item['name']) : '' ?>" required></div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach ($cats as $k=>$l): ?><option value="<?= $k ?>" <?= $isEdit && ($item['category'] ?? '')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Protocol</label>
                    <select name="protocol" class="form-select">
                        <?php foreach ($protos as $k=>$l): ?><option value="<?= $k ?>" <?= $isEdit && ($item['protocol'] ?? '')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Manufacturer</label><input type="text" name="manufacturer" class="form-control" value="<?= $isEdit ? htmlspecialchars($item['manufacturer'] ?? '') : '' ?>"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Model</label><input type="text" name="model" class="form-control" value="<?= $isEdit ? htmlspecialchars($item['model'] ?? '') : '' ?>"></div>
            </div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars($item['description'] ?? '') : '' ?></textarea></div>
            <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" <?= (!$isEdit || ($item['is_active'] ?? 1)) ? 'checked' : '' ?> id="a"><label class="form-check-label" for="a">Active</label></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
        </form>
    </div>
</div>
