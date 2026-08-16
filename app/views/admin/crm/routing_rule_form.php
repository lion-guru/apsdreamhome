<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-route me-2 text-info"></i>
                        <?= $rule ? 'Edit Routing Rule' : 'Create Routing Rule' ?>
                    </h5>

                    <form method="POST" action="<?= BASE_URL ?>/admin/crm/routing/<?= $rule ? $rule['id'] . '/update' : 'store' ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label">Rule Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= htmlspecialchars($rule['name'] ?? '') ?>"
                                   placeholder="e.g. High Budget Leads, Website Referrals">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Source Pattern</label>
                                <input type="text" name="source_pattern" class="form-control"
                                       value="<?= htmlspecialchars($rule['source_pattern'] ?? '*') ?>"
                                       placeholder="* = all, or website,referral">
                                <small class="text-muted">Comma-separated. Use * for all sources. Supports wildcards.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City Pattern</label>
                                <input type="text" name="city_pattern" class="form-control"
                                       value="<?= htmlspecialchars($rule['city_pattern'] ?? '*') ?>"
                                       placeholder="* = all, or delhi,noida">
                                <small class="text-muted">Comma-separated. Use * for all cities.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Min Budget (₹)</label>
                                <input type="number" name="min_budget" class="form-control" step="1000"
                                       value="<?= (int)($rule['min_budget'] ?? 0) ?>">
                                <small class="text-muted">Set 0 for no minimum.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Budget (₹)</label>
                                <input type="number" name="max_budget" class="form-control" step="1000"
                                       value="<?= (int)($rule['max_budget'] ?? 0) ?>">
                                <small class="text-muted">Set 0 for no maximum.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Assign to Department</label>
                                <select name="target_department_id" class="form-select">
                                    <option value="">— None —</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= ($rule['target_department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['name'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Auto-routes to least-loaded user in this department.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assign to User (Override)</label>
                                <select name="target_user_id" class="form-select">
                                    <option value="">— None —</option>
                                    <?php foreach ($users as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($rule['target_user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u['name'] ?? '') ?> (<?= htmlspecialchars($u['email'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">If set, takes priority over department routing.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <input type="number" name="priority" class="form-control"
                                       value="<?= (int)($rule['priority'] ?? 100) ?>">
                                <small class="text-muted">Lower = evaluated first. Default: 100.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" <?= ($rule['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                                    <option value="0" <?= !($rule['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/admin/crm/routing" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-save me-1"></i><?= $rule ? 'Update Rule' : 'Create Rule' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
