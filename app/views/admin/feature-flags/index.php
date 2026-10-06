<?php $page_title = 'Feature Flags'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Feature Flags</h2>
        <a href="<?= BASE_URL ?>/admin/feature-flags/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Flag
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= number_format($stats['total'] ?? 0) ?></h3><small class="text-muted">Total Flags</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success"><?= number_format($stats['enabled'] ?? 0) ?></h3><small class="text-muted">Enabled</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-secondary"><?= number_format($stats['disabled'] ?? 0) ?></h3><small class="text-muted">Disabled</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-info"><?= number_format($stats['groups'] ?? 0) ?></h3><small class="text-muted">Groups</small></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/feature-flags" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="key or name..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Group</label>
                    <select name="group" class="form-select">
                        <option value="">All groups</option>
                        <?php foreach (($groups ?? []) as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>" <?= ($current_group ?? '') === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="enabled" <?= ($current_status ?? '') === 'enabled' ? 'selected' : '' ?>>Enabled</option>
                        <option value="disabled" <?= ($current_status ?? '') === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-list me-2"></i>Flags (<?= count($flags ?? []) ?>)</h6></div>
        <div class="card-body p-0">
            <?php if (empty($flags)): ?>
                <p class="text-muted text-center py-4">No feature flags found. <a href="<?= BASE_URL ?>/admin/feature-flags/create">Create one</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Key</th><th>Name</th><th>Group</th><th>Status</th><th>Rollout</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($flags as $f): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($f['key']) ?></code></td>
                                <td><?= htmlspecialchars($f['name']) ?><br><small class="text-muted"><?= htmlspecialchars(substr($f['description'] ?? '', 0, 60)) ?></small></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($f['group'] ?? 'general') ?></span></td>
                                <td>
                                    <?php if ($f['enabled']): ?>
                                        <span class="badge bg-success">ON</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">OFF</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= (int)($f['rollout_percentage'] ?? 100) ?>%</small></td>
                                <td><small class="text-muted"><?= htmlspecialchars($f['updated_at'] ?? '') ?></small></td>
                                <td class="text-end text-nowrap">
                                    <form method="POST" action="<?= BASE_URL ?>/admin/feature-flags/toggle/<?= htmlspecialchars($f['key']) ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-sm <?= $f['enabled'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>" title="<?= $f['enabled'] ? 'Disable' : 'Enable' ?>">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>
                                    <a href="<?= BASE_URL ?>/admin/feature-flags/edit/<?= htmlspecialchars($f['key']) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
