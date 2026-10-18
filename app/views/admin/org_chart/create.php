<?php
/** Admin Org Chart Create/Edit */

$page_title = $page_title ?? ($mode === 'edit' ? 'Edit Position' : 'Add Position');
$mode = $mode ?? 'create';
$node = $node ?? [];
$employees = $employees ?? [];
$nodes = $nodes ?? [];
$departments = $departments ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-sitemap me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Add or edit an organizational chart position</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/org-chart" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Org Chart
        </a>
    </div>

    <form method="POST" id="nodeForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= $node['id'] ?>">
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-user-tie me-2"></i>Position Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $e): ?>
                                <?php 
                                $hasNode = false;
                                foreach ($nodes as $n) { 
                                    if ($n['employee_id'] == $e['id'] && ($mode !== 'edit' || $n['id'] != $node['id'])) { 
                                        $hasNode = true; 
                                        break; 
                                    } 
                                }
                                if (!$hasNode): ?>
                                    <option value="<?= $e['id'] ?>" <?= ($node['employee_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($e['name'] . ' (' . $e['designation'] . ' - ' . $e['department'] . ')') ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Only employees without a position are shown</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reports To</label>
                        <select name="parent_id" class="form-select">
                            <option value="">Top Level (No Manager)</option>
                            <?php foreach ($nodes as $n): ?>
                                <?php if ($mode === 'edit' && $n['id'] == $node['id']) continue; ?>
                                <option value="<?= $n['id'] ?>" <?= ($node['parent_id'] ?? '') == $n['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($n['position_title'] . ' - ' . ($n['employee_name'] ?? 'Vacant')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Position Title <span class="text-danger">*</span></label>
                        <input type="text" name="position_title" class="form-control" required value="<?= htmlspecialchars($node['position_title'] ?? '') ?>" placeholder="e.g., Senior Manager">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>" <?= ($node['department'] ?? '') === $dept ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $node['sort_order'] ?? 0 ?>" min="0">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_vacant" value="1" id="is_vacant" <?= !empty($node['is_vacant']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_vacant">Vacant Position</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= empty($node['is_active']) ? '' : 'checked' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Additional Info</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Vacant Since</label>
                        <input type="date" name="vacant_since" class="form-control" value="<?= htmlspecialchars($node['vacant_since'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Custom Data (JSON)</label>
                        <textarea name="custom_data" class="form-control" rows="3" placeholder='{"location": "Building A", "extension": "1234"}'><?= htmlspecialchars($node['custom_data'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/org-chart" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Save Position
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.getElementById('nodeForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
    
    // Validate custom_data is valid JSON
    const customData = document.querySelector('[name="custom_data"]').value;
    if (customData) {
        try {
            JSON.parse(customData);
        } catch (e) {
            alert('Custom Data must be valid JSON');
            e.preventDefault();
        }
    }
});
</script>