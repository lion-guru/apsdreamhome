<?php
$feature = $feature ?? null;
$isEdit = !empty($feature);
$csrf = $_SESSION['csrf_token'] ?? '';
$cats = ['energy' => 'Energy', 'water' => 'Water', 'waste' => 'Waste', 'materials' => 'Materials', 'air' => 'Air', 'landscape' => 'Landscape', 'smart' => 'Smart'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-seedling me-2"></i><?= $isEdit ? 'Edit' : 'Add' ?> Green Feature</h2>
    <a href="<?= BASE_URL ?>/admin/sustainable/features" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/feature/save">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $feature['id'] ?>"><?php endif; ?>
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= $isEdit ? htmlspecialchars($feature['name']) : '' ?>" required></div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach ($cats as $k => $l): ?>
                            <option value="<?= $k ?>" <?= $isEdit && ($feature['category'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3"><label class="form-label">Icon Class</label><input type="text" name="icon" class="form-control" value="<?= $isEdit ? htmlspecialchars($feature['icon'] ?? 'fa-leaf') : 'fa-leaf' ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">CO₂ Saved (kg/yr)</label><input type="number" step="0.01" name="co2_saved_kg_yr" class="form-control" value="<?= $isEdit ? ($feature['co2_saved_kg_yr'] ?? '') : '' ?>"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Cost Estimate (₹)</label><input type="number" step="0.01" name="cost_estimate" class="form-control" value="<?= $isEdit ? ($feature['cost_estimate'] ?? '') : '' ?>"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars($feature['description'] ?? '') : '' ?></textarea>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" <?= (!$isEdit || ($feature['is_active'] ?? 1)) ? 'checked' : '' ?> id="active">
                <label class="form-check-label" for="active">Active</label>
            </div>
            <button type="submit" class="btn btn-info text-white"><i class="fas fa-save me-1"></i> Save</button>
        </form>
    </div>
</div>
