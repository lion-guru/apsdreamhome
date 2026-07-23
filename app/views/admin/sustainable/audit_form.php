<?php
$audit = $audit ?? null;
$isEdit = !empty($audit);
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bolt me-2"></i><?= $isEdit ? 'Edit' : 'New' ?> Energy Audit</h2>
    <a href="<?= BASE_URL ?>/admin/sustainable/audits" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/audit/save">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $audit['id'] ?>"><?php endif; ?>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Project Name</label><input type="text" name="project_name" class="form-control" value="<?= $isEdit ? htmlspecialchars($audit['project_name'] ?? '') : '' ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Project ID</label><input type="number" name="project_id" class="form-control" value="<?= $isEdit ? ($audit['project_id'] ?? '') : '' ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Audit Date</label><input type="date" name="audit_date" class="form-control" value="<?= $isEdit && !empty($audit['audit_date']) ? $audit['audit_date'] : '' ?>"></div>
            </div>
            <div class="mb-3"><label class="form-label">Auditor Name</label><input type="text" name="auditor_name" class="form-control" value="<?= $isEdit ? htmlspecialchars($audit['auditor_name'] ?? '') : '' ?>"></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Energy Score (0-100)</label><input type="number" step="0.01" name="energy_score" class="form-control" value="<?= $isEdit ? ($audit['energy_score'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Annual kWh</label><input type="number" step="0.01" name="annual_kwh" class="form-control" value="<?= $isEdit ? ($audit['annual_kwh'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Solar Capacity (kWp)</label><input type="number" step="0.01" name="solar_capacity_kwp" class="form-control" value="<?= $isEdit ? ($audit['solar_capacity_kwp'] ?? '') : '' ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Water Savings (kL/yr)</label><input type="number" step="0.01" name="water_savings_kl" class="form-control" value="<?= $isEdit ? ($audit['water_savings_kl'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Renewable %</label><input type="number" step="0.01" name="renewable_pct" class="form-control" value="<?= $isEdit ? ($audit['renewable_pct'] ?? '0') : '0' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Est. CO₂ (t/yr)</label><input type="number" step="0.01" name="estimated_co2_tonnes_yr" class="form-control" value="<?= $isEdit ? ($audit['estimated_co2_tonnes_yr'] ?? '') : '' ?>"></div>
            </div>
            <div class="mb-3"><label class="form-label">Recommendations (one per line)</label><textarea name="recommendations" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars(is_array($audit['recommendations'] ?? null) ? implode("\n", $audit['recommendations']) : '') : '' ?></textarea></div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= $isEdit ? htmlspecialchars($audit['notes'] ?? '') : '' ?></textarea></div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="draft" <?= $isEdit && ($audit['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="completed" <?= $isEdit && ($audit['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="reviewed" <?= $isEdit && ($audit['status'] ?? '') === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
        </form>
    </div>
</div>
