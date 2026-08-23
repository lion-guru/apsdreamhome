<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">Push Notifications</h1>
            <p class="text-muted mb-0">Manage and send web push notifications to subscribers</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/push-notifications/send" class="btn btn-primary">
                <i class="fas fa-paper-plane me-1"></i> Send Push
            </a>
            <a href="<?= BASE_URL ?>/admin/push-notifications/log" class="btn btn-outline-secondary">
                <i class="fas fa-history me-1"></i> View Log
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-primary"><?= number_format($stats['total_subscribers'] ?? 0) ?></div>
                    <div class="text-muted">Active Subscribers</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-success"><?= number_format($stats['sent_today'] ?? 0) ?></div>
                    <div class="text-muted">Sent Today</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-info"><?= ($stats['success_rate'] ?? 0) ?>%</div>
                    <div class="text-muted">Success Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Log -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-semibold">Recent Notifications</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($log)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3 opacity-25"></i>
                    <p>No push notifications sent yet.</p>
                    <a href="<?= BASE_URL ?>/admin/push-notifications/send" class="btn btn-primary btn-sm">Send First Notification</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Body</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($log, 0, 15) as $entry): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($entry['title'] ?? '') ?></td>
                                    <td class="text-muted style-20300">
                                        <?= htmlspecialchars($entry['body'] ?? '') ?>
                                    </td>
                                    <td>
                                        <?php
                                            $statusBadge = match($entry['status'] ?? 'sent') {
                                                'sent' => 'success',
                                                'failed' => 'danger',
                                                default => 'secondary',
                                            };
                                        ?>
                                        <span class="badge bg-<?= $statusBadge ?>"><?= htmlspecialchars($entry['status'] ?? 'sent') ?></span>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($entry['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
