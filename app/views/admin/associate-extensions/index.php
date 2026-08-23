<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i>Associate Extensions</h1>
        <div>
            <span class="badge bg-primary fs-6"><?= count($users ?? []) ?> users</span>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Points</th>
                            <th>Badges</th>
                            <th>Training %</th>
                            <th>Visits</th>
                            <th>Referrals</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users ?? [])): ?>
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                                <h5>No users Found</h5>
                                <p class="mb-3">No associate extension records exist yet.</p>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($a['name'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($a['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($a['phone'] ?? '') ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= (int)($a['points'] ?? 0) ?></span></td>
                                    <td><?= htmlspecialchars($a['badges'] ?? '—') ?></td>
                                    <td>
                                        <div class="progress style-39312">
                                            <div class="progress-bar bg-info style-97207"><?= (int)($a['training_progress'] ?? 0) ?>%</div>
                                        </div>
                                    </td>
                                    <td><?= (int)($a['total_visits'] ?? 0) ?></td>
                                    <td><?= (int)($a['total_referrals'] ?? 0) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/associate-extensions/show/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
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
