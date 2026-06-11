<?php

/**
 * Edit Marketing Strategy - APS Dream Home Admin
 */
$page_title = 'Edit Strategy';
$page_description = 'Edit marketing strategy details';
$s = $strategy ?? [];

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>Edit Marketing Strategy</h1>
            <p class="text-muted mb-0">Update marketing strategy details</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/marketing/strategies" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/marketing/strategies/update/<?= $s['id'] ?? 0 ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($s['title'] ?? '') ?>" placeholder="Enter strategy title">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Describe the marketing strategy"><?= htmlspecialchars($s['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image URL</label>
                    <input type="url" name="image_url" class="form-control" value="<?= htmlspecialchars($s['image_url'] ?? '') ?>" placeholder="https://example.com/image.jpg">
                    <div class="form-text">Optional. URL to an image representing this strategy.</div>
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="active" class="form-check-input" id="activeSwitch" <?= ($s['active'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activeSwitch">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update Strategy
                </button>
            </form>
        </div>
    </div>
</div>
