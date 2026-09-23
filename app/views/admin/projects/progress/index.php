<?php $projects = $projects ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="fas fa-chart-line text-info me-2"></i>Project Progress Tracker</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/projects">Projects</a></li>
                    <li class="breadcrumb-item active">Progress Tracker</li>
                </ol>
            </nav>
        </div>
        <a href="<?= BASE_URL ?>/admin/projects" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Projects</a>
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
                                    <td style="min-width:120px;">
                                        <?php $pct = (int)($p['progress_pct'] ?? 0); ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;border-radius:4px;">
                                                <div class="progress-bar bg-<?= $pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning') ?>"
                                                     role="progressbar" style="width:<?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="small text-muted"><?= $pct ?>%</span>
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
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/projects/view/<?= $p['id'] ?>" class="btn btn-outline-secondary" title="Project"><i class="fas fa-building"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/projects/progress/show/<?= $p['id'] ?>" class="btn btn-outline-primary" title="Progress Detail"><i class="fas fa-chart-line"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/projects/edit/<?= $p['id'] ?>" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        </div>
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
