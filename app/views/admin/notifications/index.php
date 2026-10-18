<?php
/**
 * Admin Notification Center View
 * Provides complete notification history, category and status filtering,
 * instant search, date filtering, pagination, and single/bulk actions.
 */

function resolveActionUrl(?string $url): string {
    if (empty($url)) {
        return '';
    }
    $trimmed = trim($url);
    if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
        return $trimmed;
    }
    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    return $base . '/' . ltrim($trimmed, '/');
}

function formatRelativeTime(?string $datetime): string {
    if (empty($datetime)) {
        return '';
    }
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->getTimestamp() - $then->getTimestamp();
    if ($diff < 60) {
        return 'Just now';
    }
    if ($diff < 3600) {
        return intdiv($diff, 60) . 'm ago';
    }
    if ($diff < 86400) {
        return intdiv($diff, 3600) . 'h ago';
    }
    if ($diff < 172800) {
        return 'Yesterday';
    }
    return $then->format('d M Y, h:i A');
}

$curStatus   = $filters['status'] ?? 'all';
$curCategory = $filters['category'] ?? 'all';
$curQuery    = $filters['q'] ?? '';
$curDate     = $filters['date'] ?? '';

$curPage     = $pagination['current_page'] ?? 1;
$totalPages  = $pagination['total_pages'] ?? 1;
$totalItems  = $pagination['total_items'] ?? 0;
$perPage     = $pagination['per_page'] ?? 25;

$startItem   = $totalItems > 0 ? (($curPage - 1) * $perPage) + 1 : 0;
$endItem     = min($totalItems, $curPage * $perPage);

// Helper for building filter URLs
$buildFilterUrl = function($newParams = []) use ($curStatus, $curCategory, $curQuery, $curDate) {
    $merged = array_merge([
        'status'   => $curStatus,
        'category' => $curCategory,
        'q'        => $curQuery,
        'date'     => $curDate,
        'page'     => 1,
    ], $newParams);

    // Remove defaults / empties
    if ($merged['status'] === 'all') {
        unset($merged['status']);
    }
    if ($merged['category'] === 'all') {
        unset($merged['category']);
    }
    if (empty($merged['q'])) {
        unset($merged['q']);
    }
    if (empty($merged['date'])) {
        unset($merged['date']);
    }
    if (($merged['page'] ?? 1) == 1) {
        unset($merged['page']);
    }

    $qs = http_build_query($merged);
    return BASE_URL . '/admin/notifications' . ($qs ? ('?' . $qs) : '');
};
?>

<div class="container-fluid px-4 py-3">
    <!-- Header Row -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="fas fa-bell text-warning me-2"></i><?= htmlspecialchars($page_heading ?? 'Notification Center') ?>
            </h1>
            <p class="text-muted small mb-0">System alerts, new leads, customer payments, and team review notifications</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/admin/notification-preferences" class="btn btn-outline-secondary btn-sm px-3 shadow-sm" title="Configure channels (Web Push, WhatsApp, Email, In-App)">
                <i class="fas fa-sliders-h me-1"></i> Preferences
            </a>
            <button class="btn btn-outline-warning btn-sm px-3 shadow-sm" type="button" onclick="adminNotif.archiveNow()" title="Archive read notifications older than 90 days to keep DB fast">
                <i class="fas fa-archive me-1"></i> Archive 90d+
            </button>
            <button class="btn btn-primary btn-sm px-3 shadow-sm" type="button" onclick="adminNotif.markAllRead()">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </button>
            <button class="btn btn-outline-danger btn-sm px-3 shadow-sm" type="button" onclick="adminNotif.clearRead()">
                <i class="fas fa-trash-alt me-1"></i> Clear Read
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <!-- Status Tabs -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom pb-3 mb-3">
                <div class="nav nav-pills" role="tablist">
                    <a class="nav-link btn-sm <?= $curStatus === 'all' ? 'active' : '' ?>" href="<?= $buildFilterUrl(['status' => 'all']) ?>">
                        All <span class="badge rounded-pill ms-1 <?= $curStatus === 'all' ? 'bg-light text-dark' : 'bg-secondary' ?>"><?= $stats['all'] ?? 0 ?></span>
                    </a>
                    <a class="nav-link btn-sm <?= $curStatus === 'unread' ? 'active' : '' ?>" href="<?= $buildFilterUrl(['status' => 'unread']) ?>">
                        Unread <span class="badge rounded-pill ms-1 <?= $curStatus === 'unread' ? 'bg-danger' : 'bg-danger-subtle text-danger' ?>"><?= $stats['unread'] ?? 0 ?></span>
                    </a>
                    <a class="nav-link btn-sm <?= $curStatus === 'read' ? 'active' : '' ?>" href="<?= $buildFilterUrl(['status' => 'read']) ?>">
                        Read <span class="badge rounded-pill ms-1 <?= $curStatus === 'read' ? 'bg-light text-dark' : 'bg-secondary' ?>"><?= $stats['read'] ?? 0 ?></span>
                    </a>
                </div>

                <!-- Category Pills -->
                <div class="d-flex flex-wrap gap-1">
                    <a class="btn btn-sm <?= $curCategory === 'all' ? 'btn-dark' : 'btn-outline-secondary' ?>" href="<?= $buildFilterUrl(['category' => 'all']) ?>">All</a>
                    <a class="btn btn-sm <?= $curCategory === 'leads' ? 'btn-success' : 'btn-outline-success' ?>" href="<?= $buildFilterUrl(['category' => 'leads']) ?>">
                        <i class="fas fa-user-plus me-1"></i>Leads (<?= $stats['leads'] ?? 0 ?>)
                    </a>
                    <a class="btn btn-sm <?= $curCategory === 'payments' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= $buildFilterUrl(['category' => 'payments']) ?>">
                        <i class="fas fa-rupee-sign me-1"></i>Payments (<?= $stats['payments'] ?? 0 ?>)
                    </a>
                    <a class="btn btn-sm <?= $curCategory === 'alerts' ? 'btn-danger' : 'btn-outline-danger' ?>" href="<?= $buildFilterUrl(['category' => 'alerts']) ?>">
                        <i class="fas fa-exclamation-triangle me-1"></i>Alerts (<?= $stats['alerts'] ?? 0 ?>)
                    </a>
                    <a class="btn btn-sm <?= $curCategory === 'reviews' ? 'btn-purple text-white' : 'btn-outline-secondary' ?>" href="<?= $buildFilterUrl(['category' => 'reviews']) ?>" style="<?= $curCategory === 'reviews' ? 'background:#7e22ce; border-color:#7e22ce;' : '' ?>">
                        <i class="fas fa-clipboard-check me-1"></i>Reviews
                    </a>
                </div>
            </div>

            <!-- Search & Date Filter Form -->
            <form method="GET" action="<?= BASE_URL ?>/admin/notifications" class="row g-2 align-items-center">
                <?php if ($curStatus !== 'all'): ?><input type="hidden" name="status" value="<?= htmlspecialchars($curStatus) ?>"><?php endif; ?>
                <?php if ($curCategory !== 'all'): ?><input type="hidden" name="category" value="<?= htmlspecialchars($curCategory) ?>"><?php endif; ?>
                <div class="col-md-5 col-sm-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" id="notif_search_input" aria-label="Search notifications" class="form-control" placeholder="Search notification title or message..." value="<?= htmlspecialchars($curQuery) ?>">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                        <input type="date" name="date" id="notif_date_input" aria-label="Filter by date" class="form-control" value="<?= htmlspecialchars($curDate) ?>">
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
                    <?php if ($curQuery || $curDate || $curCategory !== 'all' || $curStatus !== 'all'): ?>
                        <a href="<?= BASE_URL ?>/admin/notifications" class="btn btn-sm btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification List Card -->
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                Notifications (<?= $totalItems ?>)
            </h6>
            <?php if ($totalItems > 0): ?>
                <small class="text-muted">Showing <?= $startItem ?> - <?= $endItem ?> of <?= $totalItems ?></small>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($notifications)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): ?>
                        <?php
                            $isUnread = ((int)($n['is_read'] ?? 0) === 0 && ($n['status'] ?? '') !== 'read');
                            $cat = $n['category'] ?? 'general';

                            $badgeClass = match ($cat) {
                                'leads'    => 'background: #dcfce7; color: #15803d;',
                                'payments' => 'background: #e0e7ff; color: #4338ca;',
                                'alerts'   => 'background: #fee2e2; color: #b91c1c;',
                                'reviews'  => 'background: #f3e8ff; color: #7e22ce;',
                                default    => 'background: #f1f5f9; color: #475569;',
                            };

                            $icon = match ($cat) {
                                'leads'    => 'fa-user-plus',
                                'payments' => 'fa-rupee-sign',
                                'alerts'   => 'fa-exclamation-triangle',
                                'reviews'  => 'fa-clipboard-check',
                                default    => 'fa-bell',
                            };

                            $actionUrl = resolveActionUrl($n['action_url'] ?? '');
                            $relTime = formatRelativeTime($n['created_at'] ?? '');
                        ?>
                        <div class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-start gap-3 <?= $isUnread ? 'bg-light border-start border-4 border-primary' : '' ?>" id="notif-row-<?= $n['id'] ?>">
                            <!-- Category Icon Wrap -->
                            <div class="flex-shrink-0 mt-1">
                                <span class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 16px; <?= $badgeClass ?>">
                                    <i class="fas <?= $icon ?>"></i>
                                </span>
                            </div>

                            <!-- Content Column -->
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <div>
                                        <h6 class="mb-1 text-dark <?= $isUnread ? 'fw-bold' : '' ?>" style="font-size: 14.5px;">
                                            <?= htmlspecialchars($n['title']) ?>
                                            <?php if ($isUnread): ?>
                                                <span class="badge bg-danger rounded-pill ms-1" style="font-size: 10px;">Unread</span>
                                            <?php endif; ?>
                                            <span class="badge rounded-pill ms-1 text-uppercase" style="font-size: 10px; <?= $badgeClass ?>"><?= htmlspecialchars($cat) ?></span>
                                        </h6>
                                    </div>
                                    <small class="text-muted text-nowrap" title="<?= htmlspecialchars($n['created_at'] ?? '') ?>">
                                        <i class="far fa-clock me-1"></i><?= $relTime ?>
                                    </small>
                                </div>
                                <p class="mb-2 text-secondary small" style="line-height: 1.5; white-space: pre-line;"><?= htmlspecialchars($n['message']) ?></p>

                                <!-- Action Buttons -->
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                    <?php if (!empty($actionUrl)): ?>
                                        <a href="<?= htmlspecialchars($actionUrl) ?>" class="btn btn-sm btn-outline-primary px-3 py-1" style="font-size: 12px;">
                                            <i class="fas fa-external-link-alt me-1"></i> View Action
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($isUnread): ?>
                                        <button class="btn btn-sm btn-outline-secondary px-2 py-1" style="font-size: 12px;" onclick="adminNotif.markRead(<?= $n['id'] ?>)" title="Mark as read">
                                            <i class="fas fa-check me-1"></i> Mark Read
                                        </button>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-link text-danger p-0 ms-2 text-decoration-none" style="font-size: 12px;" onclick="adminNotif.deleteNotif(<?= $n['id'] ?>)" title="Delete">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-muted">
                        <i class="fas fa-bell-slash fa-3x" style="opacity: 0.3;"></i>
                    </div>
                    <h6 class="text-dark fw-bold mb-1">No Notifications Found</h6>
                    <p class="text-muted small mb-0">There are no notifications matching your selected filters.</p>
                    <?php if ($curQuery || $curDate || $curCategory !== 'all' || $curStatus !== 'all'): ?>
                        <a href="<?= BASE_URL ?>/admin/notifications" class="btn btn-sm btn-outline-primary mt-3">Reset All Filters</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination Footer -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Page <?= $curPage ?> of <?= $totalPages ?> (<?= $totalItems ?> items)</small>
                <nav aria-label="Notification pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $curPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $buildFilterUrl(['page' => $curPage - 1]) ?>">Previous</a>
                        </li>
                        <?php
                            $startPage = max(1, $curPage - 2);
                            $endPage = min($totalPages, $curPage + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                        ?>
                            <li class="page-item <?= $i == $curPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $buildFilterUrl(['page' => $i]) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $curPage >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $buildFilterUrl(['page' => $curPage + 1]) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.adminNotif = {
    markRead: async function(id) {
        try {
            const resp = await fetch('<?= BASE_URL ?>/admin/notifications/mark-read/' + id, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            if (resp.ok) {
                const row = document.getElementById('notif-row-' + id);
                if (row) {
                    row.classList.remove('bg-light', 'border-start', 'border-4', 'border-primary');
                    const badge = row.querySelector('.badge.bg-danger');
                    if (badge) badge.remove();
                    const btn = row.querySelector('button[title="Mark as read"]');
                    if (btn) btn.remove();
                }
                // Reload to sync tab counters if needed
                setTimeout(() => window.location.reload(), 400);
            }
        } catch(e) { console.error('markRead error:', e); }
    },

    markAllRead: async function() {
        if (!confirm('Mark all unread notifications as read?')) return;
        try {
            const resp = await fetch('<?= BASE_URL ?>/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            if (resp.ok) {
                window.location.reload();
            }
        } catch(e) { console.error('markAllRead error:', e); }
    },

    deleteNotif: async function(id) {
        if (!confirm('Delete this notification?')) return;
        try {
            const resp = await fetch('<?= BASE_URL ?>/admin/notifications/delete/' + id, {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            if (resp.ok) {
                const row = document.getElementById('notif-row-' + id);
                if (row) row.remove();
                setTimeout(() => window.location.reload(), 300);
            }
        } catch(e) { console.error('deleteNotif error:', e); }
    },

    clearRead: async function() {
        if (!confirm('Delete all read notifications permanently?')) return;
        try {
            const resp = await fetch('<?= BASE_URL ?>/admin/notifications/clear-read', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            if (resp.ok) {
                window.location.reload();
            }
        } catch(e) { console.error('clearRead error:', e); }
    },

    archiveNow: async function() {
        if (!confirm('Archive all read notifications older than 90 days to notifications_archive?')) return;
        try {
            const resp = await fetch('<?= BASE_URL ?>/admin/notifications/archive-now', {
                method: 'POST',
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if (data && data.success) {
                alert(data.message || 'Archived successfully.');
                window.location.reload();
            } else {
                alert('Archiving failed: ' + ((data && data.error) || 'Unknown error'));
            }
        } catch(e) { console.error('archiveNow error:', e); alert('Archiving request failed.'); }
    }
};
</script>
