<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-users-cog me-2 text-info"></i>Role-Based CRM Dashboard</h4>
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['total_leads'] ?? 0) ?></h3>
                    <small>Total Leads</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['hot_leads'] ?? 0) ?></h3>
                    <small>Hot Leads</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['pending_tasks'] ?? 0) ?></h3>
                    <small>Pending Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['won_deals'] ?? 0) ?></h3>
                    <small>Won Deals</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><i class="fas fa-chart-pie me-1"></i>Pipeline by Stage</div>
                <div class="card-body">
                    <?php if (!empty($stats['by_stage'])): ?>
                        <?php foreach ($stats['by_stage'] as $stage => $count): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between"><small><?= ucfirst($stage) ?></small><small><?= $count ?></small></div>
                                <div class="progress" style="height:6px">
                                    <div class="progress-bar" style="width:<?= $count > 0 ? ($count/max(array_sum($stats['by_stage']),1))*100 : 0 ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">No data yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><i class="fas fa-tasks me-1"></i>Recent Tasks</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($tasks)): ?>
                            <?php foreach ($tasks as $t): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <small><?= htmlspecialchars($t['description'] ?? '') ?></small>
                                    <span class="badge bg-<?= $t['status'] === 'completed' ? 'success' : 'warning' ?>"><?= $t['status'] ?? '' ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">No tasks</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>