<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i>Notifications</h5>
        <?php if (!empty($notifications)): ?>
            <button class="btn btn-sm btn-outline-secondary mark-all-read" data-url="<?= BASE_URL ?>/admin/notifications/read-all">
                <i class="fas fa-check-double me-1"></i>Mark All Read
            </button>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($notifications)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <?php
                        $badgeClass = match ($n['type'] ?? '') {
                            'lead' => 'bg-primary',
                            'property' => 'bg-success',
                            'user' => 'bg-purple',
                            'booking' => 'bg-warning text-dark',
                            'payment' => 'bg-warning',
                            default => 'bg-secondary'
                        };
                        $icon = match ($n['type'] ?? '') {
                            'lead' => 'fa-user-plus',
                            'property' => 'fa-home',
                            'user' => 'fa-user',
                            'booking' => 'fa-calendar-check',
                            'payment' => 'fa-credit-card',
                            default => 'fa-bell'
                        };
                        $time = !empty($n['created_at']) ? timeAgo($n['created_at']) : '';
                    ?>
                    <div class="list-group-item list-group-item-action py-3 px-3 notification-item d-flex align-items-start gap-3 <?= empty($n['is_read']) ? 'bg-light' : '' ?>" data-id="<?= $n['id'] ?>">
                        <div class="mt-1">
                            <span class="badge rounded-circle p-2 <?= $badgeClass ?> d-inline-flex align-items-center justify-content-center style-63078">
                                <i class="fas <?= $icon ?> fa-fw small"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="d-block text-truncate"><?= htmlspecialchars($n['title'] ?? ucfirst($n['type'] ?? 'Notification')) ?></strong>
                                    <p class="mb-1 text-muted small text-break"><?= htmlspecialchars($n['message'] ?? '') ?></p>
                                </div>
                                <small class="text-muted text-nowrap ms-2"><?= $time ?></small>
                            </div>
                            <div class="d-flex gap-2 mt-1">
                                <?php if (!empty($n['action_url'])): ?>
                                    <a href="<?= BASE_URL ?><?= htmlspecialchars($n['action_url'] ?? '') ?>" class="btn btn-sm btn-outline-primary px-2 py-0">
                                        <i class="fas fa-eye fa-fw"></i> View
                                    </a>
                                <?php endif; ?>
                                <?php if (empty($n['is_read'])): ?>
                                    <button class="btn btn-sm btn-outline-secondary px-2 py-0 mark-read" data-id="<?= $n['id'] ?>" data-url="<?= BASE_URL ?>/admin/notifications/read/<?= $n['id'] ?>">
                                        <i class="fas fa-check fa-fw"></i> Read
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($notifications) >= 20): ?>
                <div class="text-center py-3">
                    <a href="<?= BASE_URL ?>/admin/notifications" class="btn btn-sm btn-link">View All Notifications</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3 text-muted">
                    <i class="fas fa-bell fa-3x"></i>
                </div>
                <h6 class="text-muted mb-1">No new notifications</h6>
                <p class="text-muted small mb-0">You're all caught up!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
function timeAgo($timestamp) {
    if (empty($timestamp)) return '';
    $now = new DateTime();
    $then = new DateTime($timestamp);
    $diff = $now->getTimestamp() - $then->getTimestamp();

    if ($diff < 60) return $diff . ' sec ago';
    if ($diff < 3600) return intdiv($diff, 60) . ' min ago';
    if ($diff < 86400) return intdiv($diff, 3600) . ' hour' . (intdiv($diff, 3600) > 1 ? 's' : '') . ' ago';
    if ($diff < 172800) return 'Yesterday';
    return $then->format('d M');
}
?>
