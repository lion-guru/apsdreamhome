<?php $page_title = 'Create Feature Flag'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-plus me-2"></i>New Feature Flag</h2>
        <a href="<?= BASE_URL ?>/admin/feature-flags" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/feature-flags/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Key <small class="text-muted">(lowercase, numbers, underscores)</small></label>
                        <input type="text" name="key" class="form-control" required pattern="[a-z0-9_]+" placeholder="new_checkout_flow">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Display Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="New Checkout Flow">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="What does this flag control?"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Group</label>
                        <select name="group" class="form-select">
                            <?php foreach (($groups ?? ['general']) as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rollout %</label>
                        <input type="number" name="rollout_percentage" class="form-control" min="0" max="100" value="100">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="enabled" value="1" class="form-check-input" id="ffEnabled" checked>
                            <label class="form-check-label" for="ffEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date <small class="text-muted">(optional)</small></label>
                        <input type="datetime-local" name="start_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date <small class="text-muted">(optional)</small></label>
                        <input type="datetime-local" name="end_date" class="form-control">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Flag</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
