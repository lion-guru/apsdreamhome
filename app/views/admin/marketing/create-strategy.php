<?php

/**
 * Create Marketing Strategy - APS Dream Home Admin
 */

// Session started by controller
if (!isset($_SESSION['admin_id']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header("Location: " . BASE_URL . "/admin/login");
    exit();
}

$page_title = 'Create Strategy';
$page_description = 'Create a new marketing strategy';

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2"></i>Create Marketing Strategy</h1>
            <p class="text-muted mb-0">Create a new marketing strategy or campaign</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/marketing/strategies" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/marketing/strategies/store">
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="Enter strategy title">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Describe the marketing strategy"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image URL</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    <div class="form-text">Optional. URL to an image representing this strategy.</div>
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="active" class="form-check-input" id="activeSwitch" checked>
                        <label class="form-check-label" for="activeSwitch">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Create Strategy
                </button>
            </form>
        </div>
    </div>
</div>
