<?php
/**
 * Department Requests Dashboard
 * Overview of all department requests with pending counts
 */
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Department Requests</h1>
        <p class="text-muted">Cross-department request workflow system</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($departments as $dept): ?>
    <div class="col-md-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-2" style="background: var(--card-bg);">
                    <i class="fas fa-building fa-2x" style="color: var(--text-primary);"></i>
                </div>
                <h5 class="card-title"><?= htmlspecialchars($dept['name']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($dept['code']) ?></p>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-warning"><?= $dept['pending_count'] ?> Pending</span>
                    <a href="<?= BASE_URL ?>/admin/department-requests/list?department=<?= $dept['code'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Pending Requests</h5>
                <a href="<?= BASE_URL ?>/admin/department-requests/submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Submit Request
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($pending_requests)): ?>
                <p class="text-muted text-center py-4">No pending requests</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_requests as $req): ?>
                            <tr>
                                <td><a href="<?= BASE_URL ?>/admin/department-requests/<?= $req['id'] ?>">#<?= $req['id'] ?></a></td>
                                <td><span class="badge bg-info"><?= ucfirst($req['request_type']) ?></span></td>
                                <td><?= htmlspecialchars($req['department_name'] ?? $req['department_code']) ?></td>
                                <td><?= htmlspecialchars($req['title']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $req['priority'] === 'urgent' ? 'danger' : ($req['priority'] === 'high' ? 'warning' : ($req['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                        <?= ucfirst($req['priority']) ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-<?= $req['status'] === 'submitted' ? 'secondary' : ($req['status'] === 'in_progress' ? 'warning' : 'info') ?>"><?= ucfirst($req['status']) ?></span></td>
                                <td><?= date('M j, Y g:i a', strtotime($req['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>