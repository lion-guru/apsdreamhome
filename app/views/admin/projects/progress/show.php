<?php $project = $project ?? []; $milestones = $milestones ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i><?= htmlspecialchars($project['name'] ?? 'Project') ?></h1>
        <a href="<?= BASE_URL ?>/admin/projects/progress" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Progress Overview</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h2 class="text-center mb-3"><?= (int)($project['progress_pct'] ?? 0) ?>% Complete</h2>
                    <div class="progress mb-4" style="height:30px;">
                        <div class="progress-bar bg-<?= ($project['progress_pct'] ?? 0) >= 100 ? 'success' : (($project['progress_pct'] ?? 0) >= 50 ? 'info' : 'warning') ?>" style="width:<?= (int)($project['progress_pct'] ?? 0) ?>%"></div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4"><strong>Budget:</strong> ₹<?= number_format((float)($project['project_budget'] ?? 0), 2) ?></div>
                        <div class="col-md-4"><strong>Spent:</strong> ₹<?= number_format((float)($project['amount_spent'] ?? 0), 2) ?></div>
                        <div class="col-md-4"><strong>Remaining:</strong> ₹<?= number_format(max(0, (float)($project['project_budget'] ?? 0) - (float)($project['amount_spent'] ?? 0)), 2) ?></div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Milestones</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($milestones)): ?>
                        <p class="text-muted text-center py-3">No milestones added yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($milestones as $ms): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($ms['title'] ?? '') ?></span>
                                    <span class="badge bg-<?= ($ms['status'] ?? '') == 'completed' ? 'success' : 'warning' ?>"><?= ucfirst($ms['status'] ?? 'pending') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Project Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th>District</th><td><?= htmlspecialchars($project['district_name'] ?? '') ?></td></tr>
                        <tr><th>State</th><td><?= htmlspecialchars($project['state_name'] ?? '') ?></td></tr>
                        <tr><th>Colony</th><td><?= htmlspecialchars($project['colony_name'] ?? '') ?></td></tr>
                        <tr><th>Last Updated</th><td><?= isset($project['progress_last_updated']) ? date('d M Y', strtotime($project['progress_last_updated'])) : '—' ?></td></tr>
                        <tr><th>Risk Flags</th><td><span class="badge bg-<?= empty($project['risk_flags'] ?? '') ? 'success' : 'danger' ?>"><?= empty($project['risk_flags'] ?? '') ? 'None' : htmlspecialchars($project['risk_flags'] ?? '') ?></span></td></tr>
                    </table></div>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Progress</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/projects/progress/update/<?= $project['id'] ?? 0 ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Progress %</label>
                            <input type="number" name="progress_pct" class="form-control" min="0" max="100" value="<?= (int)($project['progress_pct'] ?? 0) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Milestone Title</label>
                            <input type="text" name="milestone_title" class="form-control" placeholder="e.g. Foundation Complete">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Milestone Status</label>
                            <select name="milestone_status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update</button>
                    </form>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Team & Budget</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/projects/progress/budget/<?= $project['id'] ?? 0 ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Project Budget (₹)</label>
                            <input type="number" name="project_budget" class="form-control" step="0.01" value="<?= (float)($project['project_budget'] ?? 0) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount Spent (₹)</label>
                            <input type="number" name="amount_spent" class="form-control" step="0.01" value="<?= (float)($project['amount_spent'] ?? 0) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Project Manager</label>
                            <input type="text" name="project_manager" class="form-control" value="<?= htmlspecialchars($project['project_manager'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site Supervisor</label>
                            <input type="text" name="site_supervisor" class="form-control" value="<?= htmlspecialchars($project['site_supervisor'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contractor</label>
                            <input type="text" name="contractor_name" class="form-control" value="<?= htmlspecialchars($project['contractor_name'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-1"></i>Update Budget & Team</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
