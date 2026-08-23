<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Project Progress</h1>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>District</th>
                            <th>Progress</th>
                            <th>Last Updated</th>
                            <th>Manager</th>
                            <th>Budget</th>
                            <th>Spent</th>
                            <th>Risk</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h5>No Projects</h5>
                                <p class="mb-3">No project progress records found.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($projects as $p): ?>
                                <?php $budget = (float)($p['project_budget'] ?? 0); $spent = (float)($p['amount_spent'] ?? 0); ?>
                                <tr>
                                    <td><?= $p['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($p['name'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($p['district_name'] ?? '') ?></td>
                                    <td>
                                        <div class="progress style-39312">
                                            <div class="progress-bar bg-<?= ($p['progress_pct'] ?? 0) >= 100 ? 'success' : (($p['progress_pct'] ?? 0) >= 50 ? 'info' : 'warning') ?>" class="style-35683">
                                                <?= (int)($p['progress_pct'] ?? 0) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= isset($p['progress_last_updated']) ? date('d M Y', strtotime($p['progress_last_updated'])) : '—' ?></td>
                                    <td><?= htmlspecialchars($p['project_manager'] ?? '—') ?></td>
                                    <td>₹<?= number_format($budget, 2) ?></td>
                                    <td class="text-<?= $budget > 0 && $spent > $budget ? 'danger' : 'success' ?>">₹<?= number_format($spent, 2) ?></td>
                                    <td>
                                        <?php $flags = $p['risk_flags'] ?? ''; ?>
                                        <span class="badge bg-<?= empty($flags) ? 'success' : 'danger' ?>"><?= empty($flags) ? 'None' : htmlspecialchars($flags ?? '') ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/projects/progress/show/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
