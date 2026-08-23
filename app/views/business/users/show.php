<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Associate Details</h4>
        <div>
            <a href="<?= BASE_URL ?>/business/users/edit/<?= $user['id'] ?? 0 ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?= BASE_URL ?>/business/users" class="btn btn-outline-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card aps-cp-card">
                <div class="card-body text-center">
                    <div class="avatar-lg mx-auto mb-3">
                        <?php if (!empty($user['photo'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['photo'] ?? '') ?>" alt="Photo" class="rounded-circle" width="100" height="100" class="style-44820">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto style-44468">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="mb-1"><?= htmlspecialchars($user['name'] ?? '') ?></h5>
                    <p class="text-muted mb-2"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <p class="mb-2"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($user['phone'] ?? '') ?></p>
                    <p class="mb-1">
                        <strong>Sponsor:</strong> <?= htmlspecialchars($user['sponsor_name'] ?? '—') ?>
                    </p>
                    <p class="mb-0">
                        <span class="badge bg-<?= match($user['level'] ?? '') { 'platinum' => 'dark', 'gold' => 'warning text-dark', 'silver' => 'secondary', default => 'info' } ?> fs-6">
                            <?= ucfirst($user['level'] ?? 'bronze') ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white text-center">
                        <div class="card-body aps-cp-card-body">
                            <h3 class="mb-0"><?= number_format($stats['total_downline'] ?? 0) ?></h3>
                            <small>Total Downline</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body aps-cp-card-body">
                            <h3 class="mb-0"><?= number_format($stats['active_downline'] ?? 0) ?></h3>
                            <small>Active Downline</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body aps-cp-card-body">
                            <h3 class="mb-0">₹<?= number_format($stats['total_earnings'] ?? 0) ?></h3>
                            <small>Total Earnings</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark text-center">
                        <div class="card-body aps-cp-card-body">
                            <h3 class="mb-0">₹<?= number_format($stats['pending_commission'] ?? 0) ?></h3>
                            <small>Pending Commission</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Activity</th>
                                    <th>Details</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($activities ?? [])): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No recent activity.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($activity['type'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars($activity['description'] ?? '') ?></td>
                                            <td><?= date('d M Y h:i A', strtotime($activity['created_at'] ?? 'now')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
