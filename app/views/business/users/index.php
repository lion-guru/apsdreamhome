<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Business Associates</h4>
        <a href="<?= BASE_URL ?>/business/users/create" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add Associate
        </a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="associatesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Sponsor</th>
                            <th>Level</th>
                            <th>Joining Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users ?? [])): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No associates found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" class="style-43341">
                                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['phone'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['sponsor_name'] ?? 'â€”') ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($user['level'] ?? '') { 'platinum' => 'dark', 'gold' => 'warning text-dark', 'silver' => 'secondary', default => 'info' } ?>">
                                            <?= ucfirst($user['level'] ?? 'bronze') ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($user['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($user['status'] ?? 'active') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/business/users/show/<?= $user['id'] ?? 0 ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/business/users/edit/<?= $user['id'] ?? 0 ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/business/users/performance/<?= $user['id'] ?? 0 ?>" class="btn btn-outline-info" title="Performance">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
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
