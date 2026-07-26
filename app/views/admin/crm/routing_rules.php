<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-route me-2 text-info"></i>Lead Routing Rules</h4>
        <a href="<?= BASE_URL ?>/admin/crm/routing/create" class="btn btn-info"><i class="fas fa-plus me-1"></i>Add Rule</a>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> Rule created successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> Rule updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-trash me-1"></i> Rule deleted.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-gradient-info text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['total_rules'] ?? 0 ?></div>
                    <small>Total Rules</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-success text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['active_rules'] ?? 0 ?></div>
                    <small>Active Rules</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-warning text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['routed_today'] ?? 0 ?></div>
                    <small>Routed Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-gradient-primary text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['routed_total'] ?? 0 ?></div>
                    <small>Total Routed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">Routing Rules</h6>
                    <?php if (!empty($rules)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Rule Name</th>
                                        <th>Source Pattern</th>
                                        <th>City Pattern</th>
                                        <th>Budget Range</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rules as $i => $rule): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($rule['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($rule['source_pattern'] ?? '*') ?></code></td>
                                            <td><code><?= htmlspecialchars($rule['city_pattern'] ?? '*') ?></code></td>
                                            <td>
                                                <?php
                                                $min = (float)($rule['min_budget'] ?? 0);
                                                $max = (float)($rule['max_budget'] ?? 0);
                                                if ($min > 0 && $max > 0) {
                                                    echo '₹' . number_format($min) . ' – ₹' . number_format($max);
                                                } elseif ($min > 0) {
                                                    echo '₹' . number_format($min) . '+';
                                                } elseif ($max > 0) {
                                                    echo 'Up to ₹' . number_format($max);
                                                } else {
                                                    echo '<span class="text-muted">Any</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= $rule['priority'] ?? 100 ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/crm/routing/<?= $rule['id'] ?>/toggle" style="display:inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-<?= ($rule['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                                        <?= ($rule['is_active'] ?? 0) ? '<i class="fas fa-check-circle"></i> Active' : '<i class="fas fa-times-circle"></i> Inactive' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= BASE_URL ?>/admin/crm/routing/<?= $rule['id'] ?>/edit" class="btn btn-outline-info"><i class="fas fa-edit"></i></a>
                                                    <form method="POST" action="<?= BASE_URL ?>/admin/crm/routing/<?= $rule['id'] ?>/delete" style="display:inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <button class="btn btn-outline-danger" onclick="return confirm('Delete this rule?')"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-route fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No routing rules configured yet.</p>
                            <a href="<?= BASE_URL ?>/admin/crm/routing/create" class="btn btn-info">Create First Rule</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle me-1 text-info"></i>How Routing Works</h6>
                    <p class="small text-muted mb-1">Rules are evaluated top-to-bottom by priority (lowest first).</p>
                    <p class="small text-muted mb-1"><strong>Pattern matching:</strong></p>
                    <ul class="small text-muted ps-3">
                        <li><code>*</code> = Match all</li>
                        <li><code>website,portal</code> = Match specific sources</li>
                        <li><code>social*</code> = Wildcard match</li>
                    </ul>
                    <p class="small text-muted mb-0"><strong>Budget:</strong> Set min/max to filter by lead budget range.</p>
                </div>
            </div>

            <?php if (!empty($stats['top_rules'])): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6><i class="fas fa-chart-bar me-1 text-warning"></i>Top Rules</h6>
                        <?php foreach ($stats['top_rules'] as $tr): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted"><?= htmlspecialchars($tr['name']) ?></small>
                                <span class="badge bg-info"><?= $tr['route_count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
