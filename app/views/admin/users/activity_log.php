<?php
$user = $user ?? [];
$logs = $logs ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

$actionIcons = [
    'user_created' => ['fas fa-user-plus', 'success'],
    'user_updated' => ['fas fa-user-edit', 'primary'],
    'user_deleted' => ['fas fa-user-minus', 'danger'],
    'user_soft_deleted' => ['fas fa-user-clock', 'warning'],
    'user_approved' => ['fas fa-check-circle', 'success'],
    'user_rejected' => ['fas fa-times-circle', 'danger'],
    'wallet_credit' => ['fas fa-plus-circle', 'success'],
    'wallet_debit' => ['fas fa-minus-circle', 'danger'],
    'sponsor_changed' => ['fas fa-exchange-alt', 'warning'],
    'referral_code_changed' => ['fas fa-code', 'info'],
    'bulk_activate' => ['fas fa-users', 'success'],
    'bulk_deactivate' => ['fas fa-users', 'warning'],
    'bulk_suspend' => ['fas fa-users', 'danger'],
    'bulk_user_approved' => ['fas fa-check-double', 'success'],
];
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none text-muted me-3"><i class="fas fa-arrow-left fa-lg"></i></a>
        <div>
            <h4 class="mb-0">Activity Log: <?= htmlspecialchars($user['name'] ?? 'Unknown') ?></h4>
            <small class="text-muted"><?= number_format($total) ?> total activities recorded</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to User</a>
    </div>
</div>

<!-- Activity Timeline -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if (empty($logs)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-history fa-3x mb-3 d-block"></i>
            <h5>No Activity Recorded</h5>
            <p>Admin actions on this user will appear here.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px"></th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Admin</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log):
                        $action = $log['action'] ?? 'unknown';
                        $icon = $actionIcons[$action] ?? ['fas fa-circle', 'secondary'];
                        $context = json_decode($log['context'] ?? '{}', true) ?? [];
                    ?>
                    <tr>
                        <td><i class="<?= $icon[0] ?> text-<?= $icon[1] ?>"></i></td>
                        <td>
                            <span class="badge bg-<?= $icon[1] ?>"><?= str_replace('_', ' ', ucfirst($action)) ?></span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php
                                $details = [];
                                if (isset($context['user_id']) && $context['user_id'] != ($user['id'] ?? 0)) {
                                    $details[] = 'User #' . $context['user_id'];
                                }
                                if (isset($context['amount'])) {
                                    $details[] = '₹' . number_format($context['amount']);
                                }
                                if (isset($context['reason'])) {
                                    $details[] = $context['reason'];
                                }
                                if (isset($context['new_sponsor_id'])) {
                                    $details[] = 'Sponsor → #' . $context['new_sponsor_id'];
                                }
                                if (isset($context['new_code'])) {
                                    $details[] = 'Code: ' . $context['new_code'];
                                }
                                if (isset($context['count'])) {
                                    $details[] = $context['count'] . ' users';
                                }
                                if (isset($context['changes'])) {
                                    $details[] = 'Fields: ' . implode(', ', array_keys($context['changes']));
                                }
                                echo htmlspecialchars(implode(' · ', $details) ?: '—');
                                ?>
                            </small>
                        </td>
                        <td><small><?= htmlspecialchars($log['admin_name'] ?? 'System') ?></small></td>
                        <td><code class="small"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></code></td>
                        <td><small class="text-muted"><?= isset($log['created_at']) ? date('M d, Y h:i A', strtotime($log['created_at'])) : '—' ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php if ($page > 1): ?>
    <li class="page-item"><a class="page-link" href="<?= $base ?>/admin/users/<?= $user['id'] ?>/activity-log?page=<?= $page - 1 ?>">Prev</a></li>
    <?php endif; ?>
    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
        <a class="page-link" href="<?= $base ?>/admin/users/<?= $user['id'] ?>/activity-log?page=<?= $i ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
    <li class="page-item"><a class="page-link" href="<?= $base ?>/admin/users/<?= $user['id'] ?>/activity-log?page=<?= $page + 1 ?>">Next</a></li>
    <?php endif; ?>
</ul></nav>
<?php endif; ?>
