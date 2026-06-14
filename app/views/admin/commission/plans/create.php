<?php
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-plus-circle me-2"></i>Create Commission Plan</h5>
        <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans" class="btn btn-link btn-sm">Back to Plans</a>
    </div>
    <div class="aps-cp-card-body">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error'] ?? '') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars($base) ?>/admin/commission-plans/store">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                    <input type="text" name="plan_name" class="form-control" required placeholder="e.g. Standard Commission Plan">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Plan Code <span class="text-danger">*</span></label>
                    <input type="text" name="plan_code" class="form-control" required placeholder="e.g. STANDARD" maxlength="20" style="text-transform: uppercase;">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <select name="plan_type" class="form-select">
                        <option value="hybrid">Hybrid (Direct + Team)</option>
                        <option value="direct">Direct Commission Only</option>
                        <option value="team">Team Override Only</option>
                        <option value="binary">Binary Tree</option>
                        <option value="matrix">Matrix</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Describe this commission plan..."></textarea>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                A new plan gets 7 default commission levels (Associate → Site Manager) with standard percentages. You can customize these after creation.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Plan</button>
                <a href="<?= htmlspecialchars($base) ?>/admin/commission-plans" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
