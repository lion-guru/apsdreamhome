<?php
$cert = $cert ?? null;
$isEdit = !empty($cert);
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-certificate me-2"></i><?= $isEdit ? 'Edit' : 'Add' ?> Certification</h2>
    <a href="<?= BASE_URL ?>/admin/sustainable/certifications" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/sustainable/certification/save">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $cert['id'] ?>"><?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= $isEdit ? htmlspecialchars($cert['name'] ?? '') : '' ?>" required>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" value="<?= $isEdit ? htmlspecialchars($cert['code'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Authority</label><input type="text" name="authority" class="form-control" value="<?= $isEdit ? htmlspecialchars($cert['authority'] ?? '') : '' ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Level</label><input type="text" name="level" class="form-control" value="<?= $isEdit ? htmlspecialchars($cert['level'] ?? '') : '' ?>" placeholder="Platinum / 5-Star"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Icon Class</label><input type="text" name="icon" class="form-control" value="<?= $isEdit ? htmlspecialchars($cert['icon'] ?? 'fa-leaf') : 'fa-leaf' ?>" placeholder="fa-leaf"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Badge Color</label><input type="color" name="color" class="form-control form-control-color" value="<?= $isEdit ? htmlspecialchars($cert['color'] ?? '#2e7d32') : '#2e7d32' ?>"></div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" <?= (!$isEdit || ($cert['is_active'] ?? 1)) ? 'checked' : '' ?> id="active">
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars($cert['description'] ?? '') : '' ?></textarea>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save</button>
        </form>
    </div>
</div>
