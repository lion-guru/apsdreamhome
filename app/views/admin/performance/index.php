<?php
/** Admin Performance & Goals - List */

$page_title = $page_title ?? 'Performance & Goals';
$goals = $goals ?? [];
$employees = $employees ?? [];
$filters = $filters ?? [];
$stats = $stats ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.goal-row { transition: background 0.15s; }
.goal-row:hover { background: #fefcf6; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
.progress-bar-mini { height: 8px; border-radius: 4px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-bullseye me-2 text-primary"></i>Performance & Goals</h4>
            <p class="text-muted mb-0 small">Manage employee goals, OKRs, and performance reviews</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/performance/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Goal
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['goals_by_status']['active'] ?? 0 ?></div>
                    <div class="text-white-50 small">Active Goals</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['goals_by_status']['completed'] ?? 0 ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['goals_by_status']['at_risk'] ?? 0 ?></div>
                    <div class="text-white-50 small">At Risk</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['avg_goal_progress'] ?? 0 ?>%</div>
                    <div class="text-white-50 small">Avg Progress</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['employee_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="at_risk" <?= ($filters['status'] ?? '') === 'at_risk' ? 'selected' : '' ?>>At Risk</option>
                        <option value="off_track" <?= ($filters['status'] ?? '') === 'off_track' ? 'selected' : '' ?>>Off Track</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="goal_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="okr" <?= ($filters['goal_type'] ?? '') === 'okr' ? 'selected' : '' ?>>OKR</option>
                        <option value="kpi" <?= ($filters['goal_type'] ?? '') === 'kpi' ? 'selected' : '' ?>>KPI</option>
                        <option value="personal" <?= ($filters['goal_type'] ?? '') === 'personal' ? 'selected' : '' ?>>Personal</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/performance" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Goals Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($goals)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Goals Found</h5>
                <p class="text-muted small mb-3">Create your first goal to start tracking performance</p>
                <a href="<?= BASE_URL ?>/admin/performance/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Create Goal</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%">Goal</th>
                            <th style="width: 15%">Employee</th>
                            <th style="width: 10%">Type</th>
                            <th style="width: 10%">Progress</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 15%">Period</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($goals as $goal): 
                            $statusClass = [
                                'active' => 'primary',
                                'completed' => 'success',
                                'on_track' => 'info',
                                'at_risk' => 'warning',
                                'off_track' => 'danger',
                                'cancelled' => 'secondary',
                            ][$goal['status']] ?? 'secondary';
                            
                            $progress = $goal['progress_percentage'] ?? 0;
                            $isOverdue = $goal['end_date'] && $goal['end_date'] < date('Y-m-d') && $goal['status'] !== 'completed';
                        ?>
                        <tr class="goal-row">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($goal['title']) ?></div>
                                <div class="small text-muted text-truncate" style="max-width: 300px;"><?= htmlspecialchars($goal['description'] ?? '') ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($goal['employee_name'] ?? '—') ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($goal['employee_email'] ?? '') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $goal['goal_type'] === 'okr' ? 'primary' : ($goal['goal_type'] === 'kpi' ? 'success' : 'info') ?>">
                                    <?= strtoupper($goal['goal_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="progress progress-bar-mini mb-1">
                                    <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : ($progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : 'warning')) ?>" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= $progress ?>%</div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $goal['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?= $goal['start_date'] ? date('d M Y', strtotime($goal['start_date'])) : '—' ?></small>
                                <div class="small text-muted">to <?= $goal['end_date'] ? date('d M Y', strtotime($goal['end_date'])) : '—' ?></div>
                                <?php if ($isOverdue): ?><span class="badge bg-danger ms-1">Overdue</span><?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/performance/<?= $goal['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/performance/<?= $goal['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-outline-success" onclick="recordProgress(<?= $goal['id'] ?>)" title="Record Progress"><i class="fas fa-chart-line"></i></button>
                                </div>
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

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function recordProgress(goalId) {
    const progress = prompt('Enter progress percentage (0-100):');
    if (progress === null) return;
    const pct = parseFloat(progress);
    if (isNaN(pct) || pct < 0 || pct > 100) {
        alert('Please enter a valid percentage (0-100)');
        return;
    }
    const notes = prompt('Notes (optional):') || '';
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    formData.append('checkpoint_date', '<?= date('Y-m-d') ?>');
    formData.append('actual_value', '0'); // Would need target value for proper calculation
    formData.append('notes', notes);
    
    fetch('<?= BASE_URL ?>/admin/performance/' + goalId + '/progress', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed');
    });
}
</script>