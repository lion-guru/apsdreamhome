<?php
$pageTitle = $pageTitle ?? ($reward ? 'Edit Reward' : 'Add Reward');
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$reward = $reward ?? null;
$isEdit = $reward !== null;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?> me-2 text-success"></i><?= $isEdit ? 'Edit' : 'Add' ?> Reward</h1>
        <a href="<?= $base ?>/admin/loyalty/rewards" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Reward Details</h6></div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= $base ?>/admin/loyalty/rewards/<?= $isEdit ? ($reward['id'] . '/update') : 'store' ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Reward Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($reward['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($reward['description'] ?? '') ?></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Points Required <span class="text-danger">*</span></label>
                                <input type="number" name="points_required" class="form-control" value="<?= htmlspecialchars($reward['points_required'] ?? $reward['points'] ?? '') ?>" required min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock / Quantity</label>
                                <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($reward['stock'] ?? $reward['quantity'] ?? 1) ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="general" <?= ($reward['category'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                                    <option value="voucher" <?= ($reward['category'] ?? '') === 'voucher' ? 'selected' : '' ?>>Voucher</option>
                                    <option value="merchandise" <?= ($reward['category'] ?? '') === 'merchandise' ? 'selected' : '' ?>>Merchandise</option>
                                    <option value="service" <?= ($reward['category'] ?? '') === 'service' ? 'selected' : '' ?>>Service</option>
                                    <option value="discount" <?= ($reward['category'] ?? '') === 'discount' ? 'selected' : '' ?>>Discount</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reward Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($isEdit && !empty($reward['image'])): ?>
                                <small class="text-muted">Current: <a href="<?= $base ?>/<?= $reward['image'] ?>" target="_blank"><?= $reward['image'] ?></a></small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($reward['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($reward['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i><?= $isEdit ? 'Update' : 'Create' ?> Reward</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
