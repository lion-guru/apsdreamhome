<?php
/** Employee Self-Service - Performance & Goals */

$page_title = $page_title ?? 'My Performance & Goals';
$goals = $goals ?? [];
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
            <h4 class="mb-1 fw-bold"><i class="fas fa-bullseye me-2 text-primary"></i>My Performance & Goals</h4>
            <p class="text-muted mb-0 small">Track your goals, OKRs, and performance progress</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count($goals) ?></div>
                    <div class="text-white-50 small">Total Goals</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count(array_filter($goals, fn($g) => $g['status'] === 'in_progress')) ?></div>
                    <div class="text-white-50 small">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count(array_filter($goals, fn($g) => $g['status'] === 'completed')) ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= count(array_filter($goals, fn($g) => in_array($g['status'], ['at_risk', 'off_track']))) ?></div>
                    <div class="text-white-50 small">At Risk</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="goalsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">
                Active <span class="badge bg-info ms-1"><?= count(array_filter($goals, fn($g) => in_array($g['status'], ['active', 'in_progress', 'on_track']))) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                Completed <span class="badge bg-success ms-1"><?= count(array_filter($goals, fn($g) => $g['status'] === 'completed')) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="at-risk-tab" data-bs-toggle="tab" data-bs-target="#at-risk" type="button" role="tab">
                At Risk <span class="badge bg-warning ms-1"><?= count(array_filter($goals, fn($g) => in_array($g['status'], ['at_risk', 'off_track']))) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="goalsTabsContent">
        <!-- Active Goals -->
        <div class="tab-pane fade show active" id="active" role="tabpanel">
            <?php $activeGoals = array_filter($goals, fn($g) => in_array($g['status'], ['active', 'in_progress', 'on_track'])); ?>
            <?php if (empty($activeGoals)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Active Goals</h5>
                <p class="text-muted small">Create your first goal to start tracking</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($activeGoals as $goal): 
                    $progress = $goal['progress_percentage'] ?? 0;
                    $isOverdue = $goal['end_date'] && $goal['end_date'] < date('Y-m-d') && $goal['status'] !== 'completed';
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card goal-row h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-<?= $goal['goal_type'] === 'okr' ? 'primary' : ($goal['goal_type'] === 'kpi' ? 'success' : 'info') ?>">
                                    <?= strtoupper($goal['goal_type']) ?>
                                </span>
                                <span class="badge bg-<?= 
                                    $goal['status'] === 'completed' ? 'success' : 
                                    ($goal['status'] === 'on_track' ? 'info' : 
                                    ($goal['status'] === 'at_risk' ? 'warning' : 'danger')) ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $goal['status'])) ?>
                                </span>
                            </div>
                            <h6 class="fw-semibold"><?= htmlspecialchars($goal['title']) ?></h6>
                            <p class="small text-muted"><?= htmlspecialchars($goal['description'] ?? '') ?></p>
                            
                            <div class="progress progress-bar-mini mb-2">
                                <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : ($progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : 'warning')) ?>" role="progressbar" style="width: <?= $goal['progress_percentage'] ?? 0 ?>%" aria-valuenow="<?= $goal['progress_percentage'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span><?= $goal['progress_percentage'] ?? 0 ?>% Complete</span>
                                <span>Target: <?= $goal['target_value'] ?? '—' ?> <?= $goal['target_unit'] ?? '' ?></span>
                            </div>
                            <div class="small text-muted">
                                <div>Period: <?= $goal['start_date'] ? date('d M Y', strtotime($goal['start_date'])) : '—' ?> to <?= $goal['end_date'] ? date('d M Y', strtotime($goal['end_date'])) : '—' ?></div>
                                <?php if (!empty($goal['next_review_date'])): ?>
                                    <div>Next Review: <?= date('d M Y', strtotime($goal['next_review_date'])) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($isOverdue): ?>
                                <div class="alert alert-danger py-1 mt-2 mb-0">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Overdue
                                </div>
                            <?php endif; ?>
                        </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Completed -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            <?php $completedGoals = array_filter($goals, fn($g) => $g['status'] === 'completed'); ?>
            <?php if (empty($completedGoals)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-muted">No Completed Goals</h5>
                <p class="text-muted small">Completed goals will appear here</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($completedGoals as $goal): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-success">Completed</span>
                            </div>
                            <h6 class="fw-semibold"><?= htmlspecialchars($goal['title']) ?></h6>
                            <p class="small text-muted"><?= htmlspecialchars($goal['description'] ?? '') ?></p>
                            
                            <div class="progress progress-bar-mini mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span>100% Complete</span>
                                <span>Target: <?= $goal['target_value'] ?? '—' ?> <?= $goal['target_unit'] ?? '' ?></span>
                            </div>
                            <div class="small text-muted">
                                Completed: <?= $goal['completed_at'] ? date('d M Y', strtotime($goal['completed_at'])) : date('d M Y', strtotime($goal['end_date'])) ?>
                            </div>
                        </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- At Risk -->
        <div class="tab-pane fade" id="at-risk" role="tabpanel">
            <?php $atRiskGoals = array_filter($goals, fn($g) => in_array($g['status'], ['at_risk', 'off_track'])); ?>
            <?php if (empty($atRiskGoals)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5 class="text-muted">No At-Risk Goals</h5>
                <p class="text-muted small">All goals are on track!</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($atRiskGoals as $goal): 
                    $progress = $goal['progress_percentage'] ?? 0;
                    $isOverdue = $goal['end_date'] && $goal['end_date'] < date('Y-m-d') && $goal['status'] !== 'completed';
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card goal-row border-<?= $goal['status'] === 'off_track' ? 'danger' : 'warning' ?> h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-<?= $goal['goal_type'] === 'okr' ? 'primary' : ($goal['goal_type'] === 'kpi' ? 'success' : 'info') ?>">
                                    <?= strtoupper($goal['goal_type']) ?>
                                </span>
                                <span class="badge bg-<?= $goal['status'] === 'off_track' ? 'danger' : 'warning' ?> status-badge">
                                    <?= ucfirst($goal['status']) ?>
                                </span>
                            </div>
                            <h6 class="fw-semibold"><?= htmlspecialchars($goal['title']) ?></h6>
                            <p class="small text-muted"><?= htmlspecialchars($goal['description'] ?? '') ?></p>
                            
                            <div class="progress progress-bar-mini mb-2">
                                <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : ($progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : ($goal['status'] === 'off_track' ? 'danger' : 'warning'))) ?>" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-2">
                                <span><?= $progress ?>% Complete</span>
                                <span>Target: <?= $goal['target_value'] ?? '—' ?> <?= $goal['target_unit'] ?? '' ?></span>
                            </div>
                            <div class="small text-muted">
                                <div>Period: <?= $goal['start_date'] ? date('d M Y', strtotime($goal['start_date'])) : '—' ?> to <?= $goal['end_date'] ? date('d M Y', strtotime($goal['end_date'])) : '—' ?></div>
                                <?php if ($isOverdue): ?>
                                    <div class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</div>
                                <?php endif; ?>
                            </div>
                        </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>