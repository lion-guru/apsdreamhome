<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Notifications</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="markAllRead()">
            <i class="fas fa-check-double"></i> Mark All as Read
        </button>
    </div>

    <ul class="nav nav-tabs mb-4" id="notificationTabs">
        <li class="nav-item">
            <a class="nav-link active" data-filter="all" href="#">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-filter="unread" href="#">Unread</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-filter="read" href="#">Read</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-filter="important" href="#">Important</a>
        </li>
    </ul>

    <div class="notification-list">
        <?php if (empty($notifications ?? [])): ?>
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No notifications yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <?php
                    $isUnread = !($notification['is_read'] ?? false);
                    $isImportant = !empty($notification['is_important']);
                ?>
                <div class="card mb-2 notification-item <?= $isUnread ? 'border-start border-start-4 border-primary' : '' ?> <?= $isImportant ? 'border-warning' : '' ?>" data-status="<?= $isUnread ? 'unread' : 'read' ?>" data-important="<?= $isImportant ? 1 : 0 ?>">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <div class="avatar-sm bg-<?= match($notification['type'] ?? 'info') { 'success' => 'success', 'warning' => 'warning', 'danger' => 'danger', default => 'primary' } ?> text-white rounded-circle d-flex align-items-center justify-content-center style-75848">
                                    <i class="fas fa-<?= match($notification['type'] ?? 'info') { 'success' => 'check-circle', 'warning' => 'exclamation-triangle', 'danger' => 'times-circle', default => 'info-circle' } ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 <?= $isUnread ? 'fw-bold' : '' ?>">
                                            <?= htmlspecialchars($notification['title'] ?? '') ?>
                                            <?php if ($isUnread): ?>
                                                <span class="badge bg-primary ms-1">New</span>
                                            <?php endif; ?>
                                            <?php if ($isImportant): ?>
                                                <span class="badge bg-warning text-dark ms-1">Important</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1 text-muted small"><?= htmlspecialchars($notification['message'] ?? '') ?></p>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            <?= date('d M Y h:i A', strtotime($notification['created_at'] ?? 'now')) ?>
                                        </small>
                                    </div>
                                    <div class="btn-group btn-group-sm ms-3">
                                        <?php if ($isUnread): ?>
                                            <button class="btn btn-outline-primary" onclick="markRead(this, <?= $notification['id'] ?? 0 ?>)" title="Mark as read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-danger" onclick="deleteNotification(<?= $notification['id'] ?? 0 ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#notificationTabs .nav-link').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('#notificationTabs .nav-link').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.dataset.filter;
            document.querySelectorAll('.notification-item').forEach(function (item) {
                if (filter === 'all') {
                    item.style.display = '';
                } else if (filter === 'unread') {
                    item.style.display = item.dataset.status === 'unread' ? '' : 'none';
                } else if (filter === 'read') {
                    item.style.display = item.dataset.status === 'read' ? '' : 'none';
                } else if (filter === 'important') {
                    item.style.display = item.dataset.important === '1' ? '' : 'none';
                }
            });
        });
    });
});

function markRead(btn, id) {
    fetch('<?= BASE_URL ?>/api/notifications/mark-read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    }).then(function () {
        var card = btn.closest('.notification-item');
        card.classList.remove('border-primary');
        card.dataset.status = 'read';
        btn.closest('.btn-group').removeChild(btn);
        var title = card.querySelector('h6');
        if (title) title.classList.remove('fw-bold');
        var badge = card.querySelector('.badge.bg-primary');
        if (badge) badge.remove();
    });
}

function markAllRead() {
    fetch('<?= BASE_URL ?>/api/notifications/mark-all-read', {
        method: 'POST'
    }).then(function () {
        document.querySelectorAll('.notification-item[data-status="unread"]').forEach(function (card) {
            card.classList.remove('border-primary');
            card.dataset.status = 'read';
            var title = card.querySelector('h6');
            if (title) title.classList.remove('fw-bold');
            var badge = card.querySelector('.badge.bg-primary');
            if (badge) badge.remove();
            var btn = card.querySelector('.btn-outline-primary');
            if (btn) btn.closest('.btn-group').removeChild(btn);
        });
    });
}

function deleteNotification(id) {
    if (!confirm('Delete this notification?')) return;
    fetch('<?= BASE_URL ?>/api/notifications/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    }).then(function () {
        var card = document.querySelector('.notification-item .btn-outline-danger[onclick*="' + id + '"]');
        if (card) card.closest('.notification-item').remove();
    });
}
</script>
