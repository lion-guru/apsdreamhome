<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-bell text-primary me-2"></i><?php echo __('notifications_heading', [], 'Notifications'); ?></h4>
        <div>
            <span class="badge bg-secondary me-2" id="notifCountBadge"><?php echo $unread_count ?? 0; ?> <?php echo __('notifications_unread', [], 'unread'); ?></span>
            <?php if (($unread_count ?? 0) > 0): ?>
                <button class="btn btn-sm btn-outline-primary" onclick="markAllRead()">
                    <i class="fas fa-check-double me-1"></i><?php echo __('notifications_mark_all_read', [], 'Mark All Read'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
            <p><?php echo __('notifications_empty', [], 'No notifications yet. We\'ll notify you when something important happens!'); ?></p>
        </div>
    <?php else: ?>
        <div class="list-group" id="notifList">
            <?php foreach ($notifications as $n):
                $iconMap = [
                    'success' => 'fas fa-check-circle text-success',
                    'warning' => 'fas fa-exclamation-triangle text-warning',
                    'error' => 'fas fa-times-circle text-danger',
                    'info' => 'fas fa-info-circle text-info',
                ];
                $icon = $iconMap[$n['type'] ?? 'info'] ?? 'fas fa-bell text-secondary';
                $isUnread = empty($n['is_read']) || $n['is_read'] == 0;
            ?>
            <div class="list-group-item list-group-item-action d-flex align-items-start gap-3 notif-item <?php echo $isUnread ? 'notif-unread' : ''; ?>" data-id="<?php echo htmlspecialchars($n['notification_id'] ?? ''); ?>">
                <i class="<?php echo $icon; ?> fa-lg mt-1"></i>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <strong><?php echo htmlspecialchars($n['title'] ?? ''); ?></strong>
                        <small class="text-muted"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></small>
                    </div>
                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars($n['message'] ?? ''); ?></p>
                    <?php if (!empty($n['action_url'])): ?>
                        <a href="<?php echo htmlspecialchars($n['action_url'] ?? ''); ?>" class="btn btn-sm btn-link ps-0"><?php echo __('notifications_view_details', [], 'View Details'); ?> &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php if ($isUnread): ?>
                    <button class="btn btn-sm btn-light mark-read-btn" onclick="markRead(this)" title="<?php echo __('notifications_mark_as_read', [], 'Mark as read'); ?>"><i class="fas fa-check text-success"></i></button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.notif-unread { border-left: 4px solid var(--bs-primary, #0d6efd); background: #f8f9ff; }
.notif-item:hover { background: #f0f4ff; }
</style>

<script>
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

function markRead(btn) {
    var item = btn.closest('.notif-item');
    var id = item.dataset.id;
    if (!id) return;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', BASE_URL + '/user/notifications/' + encodeURIComponent(id) + '/read', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
    xhr.onload = function() {
        if (xhr.status === 200) {
            item.classList.remove('notif-unread');
            btn.remove();
            updateCount();
        }
    };
    xhr.send();
}

function markAllRead() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', BASE_URL + '/user/notifications/read-all', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.querySelectorAll('.notif-unread').forEach(function(el) {
                el.classList.remove('notif-unread');
                var btn = el.querySelector('.mark-read-btn');
                if (btn) btn.remove();
            });
            var badge = document.getElementById('notifCountBadge');
            if (badge) badge.textContent = '0 unread';
            var markBtn = document.querySelector('button[onclick="markAllRead()"]');
            if (markBtn) markBtn.remove();
            updateHeaderNotifCount();
        }
    };
    xhr.send();
}

function updateCount() {
    var remaining = document.querySelectorAll('.notif-unread').length;
    var badge = document.getElementById('notifCountBadge');
    if (badge) badge.textContent = remaining + ' unread';
    if (typeof updateHeaderNotifCount === 'function') updateHeaderNotifCount();
}
</script>
