<?php
/**
 * Department Requests List - filtered by department
 */
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">
            Department Requests: <?= htmlspecialchars($department_name ?? $department_code) ?>
        </h1>
        <a href="<?= BASE_URL ?>/admin/department-requests/submit" class="btn btn-primary">
            <i class="fas fa-plus"></i> Submit Request
        </a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex gap-2">
            <a href="?department=<?= $department_code ?>" class="btn btn-outline-secondary btn-sm <?= empty($statusFilter) ? 'active' : '' ?>">All</a>
            <a href="?department=<?= $department_code ?>&status=submitted" class="btn btn-outline-secondary btn-sm <?= $statusFilter === 'submitted' ? 'active' : '' ?>">Submitted</a>
            <a href="?department=<?= $department_code ?>&status=in_progress" class="btn btn-outline-secondary btn-sm <?= $statusFilter === 'in_progress' ? 'active' : '' ?>">In Progress</a>
            <a href="?department=<?= $department_code ?>&status=review" class="btn btn-outline-secondary btn-sm <?= $statusFilter === 'review' ? 'active' : '' ?>">Review</a>
            <a href="?department=<?= $department_code ?>&status=completed" class="btn btn-outline-secondary btn-sm <?= $statusFilter === 'completed' ? 'active' : '' ?>">Completed</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($requests)): ?>
        <p class="text-muted text-center py-4">No requests found</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requester</th>
                        <th>Assignee</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/admin/department-requests/<?= $req['id'] ?>">#<?= $req['id'] ?></a></td>
                        <td><span class="badge bg-info"><?= ucfirst($req['request_type']) ?></span></td>
                        <td><?= htmlspecialchars($req['title']) ?></td>
                        <td>
                            <span class="badge bg-<?= $req['priority'] === 'urgent' ? 'danger' : ($req['priority'] === 'high' ? 'warning' : ($req['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                <?= ucfirst($req['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $req['status'] === 'submitted' ? 'secondary' : ($req['status'] === 'in_progress' ? 'warning' : ($req['status'] === 'completed' ? 'success' : ($req['status'] === 'rejected' ? 'danger' : 'info'))) ?>">
                                <?= ucfirst($req['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($req['requester_name_full'] ?? $req['requester_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($req['assignee_name'] ?? 'Unassigned') ?></td>
                        <td><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/department-requests/<?= $req['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5>Stats for <?= htmlspecialchars($department_name ?? $department_code) ?></h5>
        <div class="row g-2">
            <div class="col-2"><span class="badge bg-secondary">Total: <?= $stats['total'] ?? 0 ?></span></div>
            <div class="col-2"><span class="badge bg-info">In Progress: <?= $stats['in_progress'] ?? 0 ?></span></div>
            <div class="col-2"><span class="badge bg-warning">Review: <?= $stats['review'] ?? 0 ?></span></div>
            <div class="col-2"><span class="badge bg-success">Completed: <?= $stats['completed'] ?? 0 ?></span></div>
            <div class="col-2"><span class="badge bg-danger">Rejected: <?= $stats['rejected'] ?? 0 ?></span></div>
        </div>
    </div>
</div>