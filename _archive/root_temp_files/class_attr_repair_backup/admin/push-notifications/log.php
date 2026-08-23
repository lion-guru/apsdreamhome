<div class="container-fluid py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications">Push Notifications</a></li>
                <li class="breadcrumb-item active">Log</li>
            </ol>
        </nav>
        <h1 class="h3 mb-1 fw-bold">Push Notification Log</h1>
        <p class="text-muted mb-0">Delivery history for all sent push notifications</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($log)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                    <p>No notifications in the log yet.</p>
                    <a href="<?= BASE_URL ?>/admin/push-notifications/send" class="btn btn-primary btn-sm">Send First Notification</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Body</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>Details</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($log as $entry): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($entry['title'] ?? '') ?></td>
                                    <td class="text-muted" class="style-62358">
                                        <?= htmlspecialchars($entry['body'] ?? '') ?>
                                    </td>
                                    <td>
                                        <?php if (($entry['user_id'] ?? null) === null): ?>
                                            <span class="badge bg-primary">All</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">User #<?= htmlspecialchars($entry['user_id'] ?? '') ?></span>
                                        <?php endif; ?>
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
                                    <td class="text-muted small">
                                        <?php if (!empty($entry['error_message'])): ?>
                                            <span class="text-danger" title="<?= htmlspecialchars($entry['error_message'] ?? '') ?>">
                                                <i class="fas fa-exclamation-triangle"></i> Error
                                            </span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
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
