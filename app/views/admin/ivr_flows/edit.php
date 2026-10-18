<?php
/**
 * Edit IVR Flow
 * S347
 */
$page_title = $page_title ?? 'Edit IVR Flow';
$flow = $flow ?? [];
$steps = $steps ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit IVR Flow: <?= htmlspecialchars($flow['name'] ?? '') ?></h4>
            <div>
                <a href="/admin/ivr-flows/<?= $flow['id'] ?>/steps" class="btn btn-outline-primary me-2">
                    <i class="fas fa-list me-1"></i>Manage Steps
                </a>
                <a href="/admin/ivr-flows" class="btn btn-secondary">Back to Flows</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Flow Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/ivr-flows/<?= $flow['id'] ?>/update">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Flow Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required value="<?= htmlspecialchars($flow['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($flow['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Welcome Message</label>
                        <textarea class="form-control" name="welcome_message" rows="3"><?= htmlspecialchars($flow['welcome_message'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" <?= ($flow['is_active'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active (can receive calls)</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Flow
                    </button>
                    <a href="/admin/ivr-flows" class="btn btn-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Flow Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary"><?= count($steps) ?></div>
                        <small class="text-muted">Steps</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success">--</div>
                        <small class="text-muted">Total Calls</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>