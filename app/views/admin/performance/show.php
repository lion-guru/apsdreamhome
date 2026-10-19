<?php
/** Admin Performance - Show Goal */

$page_title = $page_title ?? 'Goal: ' . ($goal['title'] ?? 'Unknown');
$goal = $goal ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.progress-bar-mini { height: 8px; border-radius: 4px; }
.status-badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-bullseye me-2 text-primary"></i><?= htmlspecialchars($goal['title'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($goal['employee_name'] ?? '') ?> • <?= strtoupper($goal['goal_type'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($goal['status'] !== 'completed'): ?>
                <button class="btn btn-success btn-sm" onclick="recordProgress(<?= $goal['id'] ?>)"><i class="fas fa-chart-line me-1"></i> Record Progress</button>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/performance/<?= $goal['id'] ?>/edit" class="btn btn-outline-secondary"><i class="fas fa-edit me-1"></i> Edit</a>
            <a href="<?= BASE_URL ?>/admin/performance" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <!-- Goal Header -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Goal Overview</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-<?= $goal['goal_type'] === 'okr' ? 'primary' : ($goal['goal_type'] === 'kpi' ? 'success' : 'info') ?>">
                    <?= strtoupper($goal['goal_type']) ?>
                </span>
                <span class="badge bg-<?= 
                    $goal['status'] === 'completed' ? 'success' : 
                    ($goal['status'] === 'on_track' ? 'info' : 
                    ($goal['status'] === 'at_risk' ? 'warning' : 
                    ($goal['status'] === 'off_track' ? 'danger' : 'secondary'))) ?> status-badge">
                    <?= ucfirst(str_replace('_', ' ', $goal['status'])) ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fw-bold fs-2 text-primary"><?= $goal['progress_percentage'] ?? 0 ?>%</div>
                        <div class="text-muted small">Progress</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fw-bold fs-4"><?= $goal['target_value'] ? number_format($goal['target_value']) : '—' ?> <?= htmlspecialchars($goal['target_unit'] ?? '') ?></div>
                        <div class="text-muted small">Target</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fw-bold fs-4"><?= $goal['current_value'] ? number_format($goal['current_value']) : 0 ?> <?= htmlspecialchars($goal['target_unit'] ?? '') ?></div>
                        <div class="text-muted small">Current</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fw-bold fs-4"><?= $goal['progress_percentage'] ?? 0 ?>%</div>
                        <div class="text-muted small">Completion</div>
                    </div>
                </div>
            </div>
            <div class="progress progress-bar-mini mb-3">
                <div class="progress-bar bg-<?= ($goal['progress_percentage'] ?? 0) >= 100 ? 'success' : (($goal['progress_percentage'] ?? 0) >= 75 ? 'success' : (($goal['progress_percentage'] ?? 0) >= 50 ? 'info' : 'warning')) ?>" role="progressbar" style="width: <?= $goal['progress_percentage'] ?? 0 ?>%" aria-valuenow="<?= $goal['progress_percentage'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted">Employee</small>
                    <div class="fw-semibold"><?= htmlspecialchars($goal['employee_name'] ?? '—') ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Assigned By</small>
                    <div class="fw-semibold"><?= htmlspecialchars($goal['assigned_by_name'] ?? '—') ?></div>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Type</small>
                    <span class="badge bg-<?= $goal['goal_type'] === 'okr' ? 'primary' : ($goal['goal_type'] === 'kpi' ? 'success' : 'info') ?>">
                        <?= strtoupper($goal['goal_type']) ?>
                    </span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Weightage</small>
                    <div class="fw-semibold"><?= $goal['weightage'] ?? 100 ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-calendar-alt me-2"></i>Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted">Start Date</small>
                            <div class="fw-semibold"><?= $goal['start_date'] ? date('d M Y', strtotime($goal['start_date'])) : '—' ?></div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">End Date</small>
                            <div class="fw-semibold<?= $goal['end_date'] && $goal['end_date'] < date('Y-m-d') && $goal['status'] !== 'completed' ? ' text-danger' : '' ?>">
                                <?= $goal['end_date'] ? date('d M Y', strtotime($goal['end_date'])) : '—' ?>
                                <?php if ($goal['end_date'] && $goal['end_date'] < date('Y-m-d') && $goal['status'] !== 'completed'): ?>
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($goal['next_review_date'])): ?>
                        <div class="col-md-6">
                            <small class="text-muted">Next Review</small>
                            <div class="fw-semibold"><?= date('d M Y', strtotime($goal['next_review_date'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <small class="text-muted">Review Frequency</small>
                            <div class="fw-semibold"><?= ucfirst($goal['review_frequency'] ?? 'monthly') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-sitemap me-2"></i>Alignment</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($goal['parent_goal_id'])): ?>
                        <small class="text-muted">Parent Goal</small>
                        <div class="fw-semibold"><?= htmlspecialchars($goal['parent_goal_title'] ?? '—') ?></div>
                    <?php else: ?>
                        <div class="text-muted">Top-level goal (no parent)</div>
                    <?php endif; ?>
                    
                    <?php 
                    $children = $db->fetchAll("SELECT id, title, progress_percentage, status FROM employee_goals WHERE parent_goal_id = ? AND tenant_id = ? ORDER BY created_at", [$goal['id'], $tenantId ?? 1]);
                    if ($children): ?>
                        <hr>
                        <small class="text-muted">Child Goals (<?= count($children) ?>)</small>
                        <div class="mt-2">
                            <?php foreach ($children as $child): ?>
                                <div class="small mb-1">
                                    <span class="badge bg-<?= $child['status'] === 'completed' ? 'success' : 'primary' ?>"><?= ucfirst($child['status']) ?></span>
                                    <span class="ms-1"><?= htmlspecialchars($child['title']) ?></span>
                                    <span class="text-muted ms-2"><?= $child['progress_percentage'] ?? 0 ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if (!empty($goal['description'])): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-align-left me-2"></i>Description</h5>
        </div>
        <div class="card-body">
            <div><?= nl2br(htmlspecialchars($goal['description'])) ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Progress History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2"></i>Progress History</h5>
            <button class="btn btn-sm btn-primary" onclick="recordProgress(<?= $goal['id'] ?>)"><i class="fas fa-plus me-1"></i> Record Progress</button>
        </div>
        <div class="card-body">
            <?php 
            $progressHistory = $db->fetchAll("SELECT * FROM goal_progress WHERE goal_id = ? AND tenant_id = ? ORDER BY checkpoint_date DESC", [$goal['id'], $tenantId ?? 1]);
            if (empty($progressHistory)): ?>
            <div class="text-center py-4 text-muted">No progress records yet</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Date</th>
                            <th style="width": 120px>Actual Value</th>
                            <th style="width: 100px">Progress %</th>
                            <th style="width: 120px">Status</th>
                            <th>Notes</th>
                            <th style="width: 150px">Reviewed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progressHistory as $ph): 
                            $pctClass = $ph['percentage_complete'] >= 100 ? 'success' : ($ph['percentage_complete'] >= 75 ? 'success' : ($ph['percentage_complete'] >= 50 ? 'info' : 'warning'));
                        ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($ph['checkpoint_date'])) ?></td>
                            <td><?= number_format($ph['actual_value']) ?> <?= htmlspecialchars($goal['target_unit'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $ph['percentage_complete'] >= 100 ? 'success' : ($ph['percentage_complete'] >= 75 ? 'success' : ($ph['percentage_complete'] >= 50 ? 'info' : 'warning')) ?>"><?= $ph['percentage_complete'] ?>%</span></td>
                            <td><span class="badge bg-<?= $ph['status'] === 'achieved' ? 'success' : ($ph['status'] === 'on_track' ? 'info' : ($ph['status'] === 'at_risk' ? 'warning' : 'danger')) ?>"><?= ucfirst($ph['status']) ?></span></td>
                            <td><?= htmlspecialchars($ph['notes'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($ph['reviewer_name'] ?? '—') ?></td>
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