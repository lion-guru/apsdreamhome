ï»¿<?php $page_title = $page_title ?? 'Custom Field'; $field = $field ?? null; ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1"><i class="fas fa-sliders-h me-2 text-primary"></i><?= $field ? 'Edit' : 'Add' ?> Custom Field</h4></div>
        <a href="<?= BASE_URL ?>/admin/crm/custom-fields" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="cf-card style-9250">
        <form method="POST" action="<?= BASE_URL ?>/admin/crm/custom-fields/<?= $field ? $field['id'] . '/update' : 'store' ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="mb-3"><label class="form-label fw-bold">Field Name (DB column)</label><input type="text" name="field_name" class="form-control" value="<?= htmlspecialchars($field['field_name'] ?? '') ?>" required pattern="[a-z_]+" title="Lowercase letters and underscores only"></div>
            <div class="mb-3"><label class="form-label fw-bold">Field Label (Display)</label><input type="text" name="field_label" class="form-control" value="<?= htmlspecialchars($field['field_label'] ?? '') ?>" required></div>
            <div class="row mb-3">
                <div class="col-md-4"><label class="form-label fw-bold">Type</label><select name="field_type" class="form-select"><?php foreach (['text','select','textarea','checkbox','date','number'] as $t): ?><option value="<?= $t ?>" <?= ($field['field_type'] ?? 'text') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label fw-bold">Section</label><select name="section" class="form-select"><?php foreach (['general','preferences','financial','location'] as $s): ?><option value="<?= $s ?>" <?= ($field['section'] ?? 'general') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label fw-bold">Order</label><input type="number" name="order_index" class="form-control" value="<?= $field['order_index'] ?? 0 ?>"></div>
            </div>
            <div class="mb-3"><label class="form-label fw-bold">Options (one per line, for select fields)</label><textarea name="options" class="form-control" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3"><?= htmlspecialchars($field['options_list'] ?? '') ?></textarea></div>
            <div class="row mb-4">
                <div class="col-md-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_required" value="1" <?= !empty($field['is_required']) ? 'checked' : '' ?>><label class="form-check-label">Required</label></div></div>
                <div class="col-md-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_searchable" value="1" <?= !empty($field['is_searchable']) ? 'checked' : '' ?>><label class="form-check-label">Searchable</label></div></div>
            </div>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i><?= $field ? 'Update' : 'Create' ?> Field</button>
        </form>
    </div>
</div>
