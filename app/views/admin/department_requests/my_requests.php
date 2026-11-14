<?php
/**
 * My Requests View - requests submitted by current user
 */
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">My Requests</h1>
        <a href="<?= BASE_URL ?>/admin/department-requests/submit" class="btn btn-primary">
            <i class="fas fa-plus"></i> Submit New Request
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($requests)): ?>
        <p class="text-muted text-center py-4">You haven't submitted any requests yet</p>
        <div class="text-center">
            <a href="<?= BASE_URL ?>/admin/department-requests/submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Submit Your First Request
            </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Department</th>
                        <th scope="col">Title</th>
                        <th scope="col">Type</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>/admin/department-requests/<?= $req['id'] ?>">#<?= $req['id'] ?></a></td>
                        <td><?= htmlspecialchars($req['department_name'] ?? $req['department_code']) ?></td>
                        <td><?= htmlspecialchars($req['title'] ?? '') ?></td>
                        <td><span class="badge bg-info"><?= ucfirst($req['request_type']) ?></span></td>
                        <td>
                            <span class="badge bg-<?= $req['priority'] === 'urgent' ? 'danger' : ($req['priority'] === 'high' ? 'warning' : ($req['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                <?= ucfirst($req['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $req['status'] === 'open' ? 'secondary' : ($req['status'] === 'in_progress' ? 'warning' : ($req['status'] === 'resolved' ? 'success' : ($req['status'] === 'rejected' ? 'danger' : 'info'))) ?>">
                                <?= ucfirst($req['status']) ?>
                            </span>
                        </td>
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