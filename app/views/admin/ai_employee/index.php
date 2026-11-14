<?php
/** AI Employee Desk — roster, queue, stats */
$employees = $employees ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'done' => 0, 'failed' => 0];
$queue = $queue ?? [];
$base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-robot me-2 text-primary"></i>AI Employee Desk</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card h-100"><div class="card-body text-center">
            <h3><?= (int)($stats['total'] ?? 0) ?></h3><small class="text-muted">Tasks total</small>
        </div></div></div>
        <div class="col-md-3"><div class="card h-100 border-warning"><div class="card-body text-center">
            <h3><?= (int)($stats['pending'] ?? 0) ?></h3><small class="text-muted">Pending</small>
        </div></div></div>
        <div class="col-md-3"><div class="card h-100 border-success"><div class="card-body text-center">
            <h3><?= (int)($stats['done'] ?? 0) ?></h3><small class="text-muted">Done</small>
        </div></div></div>
        <div class="col-md-3"><div class="card h-100 border-danger"><div class="card-body text-center">
            <h3><?= (int)($stats['failed'] ?? 0) ?></h3><small class="text-muted">Failed</small>
        </div></div></div>
    </div>

    <div class="card mb-4"><div class="card-header fw-bold">Employees</div>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Workload</th><th>Last active</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($employees as $e): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($e['name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($e['role'] ?? '') ?></td>
                    <td><span class="badge bg-<?= ($e['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($e['status'] ?? '') ?></span></td>
                    <td><?= (int)($e['workload'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($e['last_active'] ?? '-') ?></td>
                    <td class="d-flex gap-1">
                        <form method="POST" action="<?= $base ?>/admin/ai-employee/<?= (int)$e['id'] ?>/status">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="status" value="<?= ($e['status'] ?? '') === 'active' ? 'paused' : 'active' ?>">
                            <button class="btn btn-sm btn-outline-primary" type="submit"><?= ($e['status'] ?? '') === 'active' ? 'Pause' : 'Activate' ?></button>
                        </form>
                        <form method="POST" action="<?= $base ?>/admin/ai-employee/<?= (int)$e['id'] ?>/run">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <button class="btn btn-sm btn-success" type="submit" <?= ($e['status'] ?? '') !== 'active' ? 'disabled' : '' ?>>Run next</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($employees)): ?>
                <tr><td colspan="6" class="text-center text-muted">No AI employees seeded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
    </div>

    <div class="card mb-4"><div class="card-header fw-bold">Enqueue Lead Task</div><div class="card-body">
        <form method="POST" action="<?= $base ?>/admin/ai-employee/enqueue-lead" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select aria-label="Employee id" name="employee_id" class="form-select" required>
                    <?php foreach ($employees as $e): ?>
                        <option value="<?= (int)$e['id'] ?>">Aira <?= htmlspecialchars($e['role'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Task</label>
                <select aria-label="Type" name="type" class="form-select">
                    <option value="qualify_lead">Qualify lead (AI note)</option>
                    <option value="followup_draft">Draft follow-up (AI msg)</option>
                    <option value="notify_stale">Notify stale lead owner</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lead ID</label>
                <input type="number" name="lead_id" class="form-control" required min="1">
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Queue task</button></div>
        </form>
    </div></div>

    <div class="card"><div class="card-header fw-bold">Task Queue</div>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Employee</th><th>Type</th><th>Lead</th><th>Status</th><th>Created</th><th>Result</th></tr></thead>
            <tbody>
            <?php foreach ($queue as $q): ?>
                <tr>
                    <td><?= (int)$q['id'] ?></td>
                    <td><?= (int)$q['employee_id'] ?></td>
                    <td><?= htmlspecialchars($q['type'] ?? '') ?></td>
                    <td><?= $q['lead_id'] ? '#' . (int)$q['lead_id'] : '-' ?></td>
                    <td><span class="badge bg-<?= in_array($q['status'], ['done']) ? 'success' : ($q['status'] === 'failed' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($q['status'] ?? '') ?></span></td>
                    <td><?= htmlspecialchars($q['created_at'] ?? '') ?></td>
                    <td><small><?= htmlspecialchars(substr((string)($q['result'] ?? ''), 0, 90)) ?></small></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($queue)): ?>
                <tr><td colspan="7" class="text-center text-muted">Queue empty — enqueue a lead task.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
    </div>
</div>
