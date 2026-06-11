<?php

/**
 * Create API Developer - APS Dream Home Admin
 */
$page_title = $page_title ?? 'Create API Developer';

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Create API Developer</h1>
            <p class="text-muted">Register a new developer and generate API key</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/api/developers/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label for="dev_name" class="form-label">Developer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dev_name" name="dev_name" required
                                   placeholder="Enter developer name">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="Enter email address">
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                A secure API key will be auto-generated upon creation.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Developer
                            </button>
                            <a href="<?php echo BASE_URL; ?>/admin/api/developers" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
