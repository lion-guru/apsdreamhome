<?php $page_title = 'Audit Logs'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Audit Logs</h2>
        <a href="<?= BASE_URL ?>/admin/audit-log/stats" class="btn btn-outline-primary"><i class="fas fa-chart-bar me-1"></i>Statistics</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= number_format($total ?? 0) ?></h3><small class="text-muted">Matching Events</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= number_format($stats['total'] ?? 0) ?></h3><small class="text-muted">Last 30 Days</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= count($stats['byAction'] ?? []) ?></h3><small class="text-muted">Distinct Actions</small></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3><?= count($stats['byRole'] ?? []) ?></h3><small class="text-muted">Active Roles</small></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/audit-log" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (($actions ?? []) as $a): ?>
                            <option value="<?= htmlspecialchars($a) ?>" <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Role</label>
                    <select name="user_role" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (($roles ?? []) as $r): ?>
                            <option value="<?= htmlspecialchars($r) ?>" <?= ($filters['user_role'] ?? '') === $r ? 'selected' : '' ?>><?= htmlspecialchars($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Entity Type</label>
                    <select name="entity_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (($entity_types ?? []) as $e): ?>
                            <option value="<?= htmlspecialchars($e) ?>" <?= ($filters['entity_type'] ?? '') === $e ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['success','failed','pending'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($logs)): ?>
                <p class="text-muted text-center py-4">No audit events match these filters.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Status</th><th>IP</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td><small><?= htmlspecialchars(date('d M H:i', strtotime($l['created_at']))) ?></small></td>
                                <td><small>#<?= (int)($l['user_id'] ?? 0) ?> <?= htmlspecialchars($l['user_role'] ?? '') ?></small></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($l['action'] ?? '') ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($l['action_type'] ?? '') ?></small></td>
                                <td><small><?= htmlspecialchars(trim(($l['entity_type'] ?? '') . ' #' . ($l['entity_id'] ?? ''), ' #')) ?: '—' ?></small></td>
                                <td>
                                    <?php $st = $l['status'] ?? 'success'; ?>
                                    <span class="badge bg-<?= $st === 'success' ? 'success' : ($st === 'failed' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($st) ?></span>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($l['ip_address'] ?? '') ?></small></td>
                                <td><a href="<?= BASE_URL ?>/admin/audit-log/<?= (int)$l['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (($total_pages ?? 1) > 1): ?>
                    <div class="d-flex justify-content-center gap-1 p-3">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= BASE_URL ?>/admin/audit-log?page=<?= $i ?>&action=<?= urlencode($filters['action'] ?? '') ?>&user_role=<?= urlencode($filters['user_role'] ?? '') ?>&entity_type=<?= urlencode($filters['entity_type'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>" class="btn btn-sm <?= $i === ($page ?? 1) ? 'btn-primary' : 'btn-outline-secondary' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
