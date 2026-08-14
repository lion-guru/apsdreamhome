<?php $calls = $calls ?? []; $agents = $agents ?? []; $page = max(1, (int)($page ?? 1)); $totalPages = max(1, (int)($totalPages ?? 1)); $total = (int)($total ?? 0); $filters = $filters ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-list me-2"></i>All Scheduled Calls (<?= $total ?>)</h4>
    <div>
        <a href="<?= BASE_URL ?>admin/voice-scheduler/schedule" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Schedule</a>
        <a href="<?= BASE_URL ?>admin/voice-scheduler" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= ($filters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="agent_id" class="form-select form-select-sm">
                    <option value="">All Agents</option>
                    <?php foreach ($agents as $a): ?>
                    <option value="<?= htmlspecialchars($a['agent_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['agent_id'] ?? '') === ($a['agent_id'] ?? '') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="From">
            </div>
            <div class="col-auto">
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="To">
            </div>
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Search lead/phone">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                <a href="<?= BASE_URL ?>admin/voice-scheduler/calls" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($calls)): ?>
        <div class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No calls found</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>ID</th><th>Lead</th><th>Phone</th><th>Scheduled</th><th>Agent</th><th>Priority</th><th>Status</th><th>Attempts</th><th>Action</th>
                </tr></thead>
                <tbody>
                <?php foreach ($calls as $c): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>admin/voice-scheduler/calls/<?= $c['id'] ?>">#<?= $c['id'] ?></a></td>
                        <td><?= htmlspecialchars($c['lead_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?: ($c['lead_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= date('d M Y', strtotime($c['scheduled_date'] ?? '')) ?><br><small class="text-muted"><?= date('h:i A', strtotime($c['scheduled_time'] ?? '')) ?></small></td>
                        <td><?= htmlspecialchars($c['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge bg-<?= $c['priority'] === 'urgent' ? 'danger' : ($c['priority'] === 'high' ? 'warning' : ($c['priority'] === 'low' ? 'secondary' : 'primary')) ?>">
                                <?= $c['priority'] ?? 'medium' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $c['status'] === 'completed' ? 'success' : ($c['status'] === 'failed' ? 'danger' : ($c['status'] === 'cancelled' ? 'secondary' : ($c['status'] === 'processing' ? 'info' : 'warning'))) ?>">
                                <?= $c['status'] ?? 'pending' ?>
                            </span>
                        </td>
                        <td><?= (int)($c['attempt_count'] ?? 0) ?>/<?= (int)($c['max_attempts'] ?? 3) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>admin/voice-scheduler/calls/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&agent_id=<?= urlencode($filters['agent_id'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filters['status'] ?? '') ?>&agent_id=<?= urlencode($filters['agent_id'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&agent_id=<?= urlencode($filters['agent_id'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
