<?php $page_title = 'Edit Feature Flag'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-edit me-2"></i>Edit: <code><?= htmlspecialchars($flag['key']) ?></code></h2>
        <a href="<?= BASE_URL ?>/admin/feature-flags" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/feature-flags/update/<?= htmlspecialchars($flag['key']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Display Name</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($flag['name'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($flag['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Group</label>
                                <select name="group" class="form-select">
                                    <?php foreach (($groups ?? ['general']) as $g): ?>
                                        <option value="<?= htmlspecialchars($g) ?>" <?= ($flag['group'] ?? 'general') === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rollout %</label>
                                <input type="number" name="rollout_percentage" class="form-control" min="0" max="100" value="<?= (int)($flag['rollout_percentage'] ?? 100) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date <small class="text-muted">(optional)</small></label>
                                <input type="datetime-local" name="start_date" class="form-control" value="<?= $flag['start_date'] ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($flag['start_date']))) : '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date <small class="text-muted">(optional)</small></label>
                                <input type="datetime-local" name="end_date" class="form-control" value="<?= $flag['end_date'] ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($flag['end_date']))) : '' ?>">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="enabled" value="1" class="form-check-input" id="ffEnabled" <?= !empty($flag['enabled']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ffEnabled">Enabled</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Flag Info</h6></div>
                <div class="card-body">
                    <p class="mb-1"><small class="text-muted">Key</small><br><code><?= htmlspecialchars($flag['key']) ?></code></p>
                    <p class="mb-1"><small class="text-muted">Status</small><br>
                        <?php if (!empty($flag['enabled'])): ?><span class="badge bg-success">ON</span>
                        <?php else: ?><span class="badge bg-secondary">OFF</span><?php endif; ?>
                    </p>
                    <p class="mb-1"><small class="text-muted">Created</small><br><?= htmlspecialchars($flag['created_at'] ?? '') ?></p>
                    <p class="mb-0"><small class="text-muted">Updated</small><br><?= htmlspecialchars($flag['updated_at'] ?? '') ?></p>
                    <hr>
                    <p class="small text-muted mb-2">Check in code with:</p>
                    <code class="small d-block bg-light p-2 rounded">(new \App\Services\FeatureFlagService())-&gt;isEnabled('<?= htmlspecialchars($flag['key']) ?>')</code>
                    <hr>
                    <form method="POST" action="<?= BASE_URL ?>/admin/feature-flags/delete/<?= htmlspecialchars($flag['key']) ?>" onsubmit="return confirm('Delete this flag permanently?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-trash me-1"></i>Delete Flag</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
