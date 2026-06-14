<?php
$page_title = $page_title ?? 'Live Chat';
$page_heading = $page_heading ?? 'Live Chat Support';
$content = $content ?? '';
$sessions = $sessions ?? [];
$stats = $stats ?? [];
$quick_replies = $quick_replies ?? [];
$current_status = $current_status ?? 'open';
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Live Chat Support</h2>
            <p class="text-muted mb-0">Real-time visitor conversations</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/live-chat/quick-replies" class="btn btn-outline-secondary"><i class="fas fa-comment-dots me-1"></i> Quick Replies</a>
            <a href="<?= BASE_URL ?>/admin/live-chat/settings" class="btn btn-outline-primary"><i class="fas fa-cog me-1"></i> Widget Settings</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Open</p>
                    <h3 class="text-primary"><?= number_format($stats['open_sessions'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Active</p>
                    <h3 class="text-success"><?= number_format($stats['active_sessions'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Unread</p>
                    <h3 class="text-danger"><?= number_format($stats['unread_admin'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Closed Today</p>
                    <h3><?= number_format($stats['closed_today'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Avg Response</p>
                    <h3 class="text-info">
                        <?php
                        $sec = $stats['avg_response_seconds'] ?? 0;
                        echo $sec < 60 ? $sec . 's' : round($sec/60, 1) . 'm';
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Satisfaction</p>
                    <h3 class="text-warning"><?= number_format($stats['satisfaction_pct'] ?? 0, 1) ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-3">
        <?php foreach (['open' => 'Open', 'active' => 'Active', 'closed' => 'Closed', 'missed' => 'Missed'] as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_status === $key ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/live-chat?status=<?= $key ?>">
                    <?= $label ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Visitor</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Agent</th>
                            <th>Messages</th>
                            <th>Unread</th>
                            <th>Last Activity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr><td colspan="10" class="text-center text-muted py-5">
                                <i class="fas fa-comments fa-3x mb-3 d-block text-secondary"></i>
                                No <?= htmlspecialchars($current_status) ?> chat sessions
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $s): ?>
                                <tr class="<?= ($s['unread_admin_count'] ?? 0) > 0 ? 'table-warning' : '' ?>">
                                    <td>#<?= $s['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($s['visitor_name'] ?: $s['user_name'] ?: 'Anonymous') ?></strong>
                                        <?php if ($s['visitor_email']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($s['visitor_email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($s['subject'] ?: '—') ?>
                                        <?php if ($s['category']): ?>
                                            <br><span class="badge bg-light text-dark"><?= htmlspecialchars($s['category']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ['open'=>'primary','assigned'=>'info','active'=>'success','on_hold'=>'warning','closed'=>'secondary','missed'=>'danger'][$s['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($s['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ['low'=>'light text-dark','normal'=>'secondary','high'=>'warning','urgent'=>'danger'][$s['priority']] ?? 'secondary' ?>">
                                            <?= ucfirst($s['priority']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= htmlspecialchars($s['agent_name'] ?? '—') ?></small></td>
                                    <td><?= $s['message_count'] ?></td>
                                    <td>
                                        <?php if (($s['unread_admin_count'] ?? 0) > 0): ?>
                                            <span class="badge bg-danger"><?= $s['unread_admin_count'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= $s['last_message_at'] ? date('M j, H:i', strtotime($s['last_message_at'])) : '—' ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/live-chat/open/<?= $s['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-comments"></i> Open
                                        </a>
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
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
