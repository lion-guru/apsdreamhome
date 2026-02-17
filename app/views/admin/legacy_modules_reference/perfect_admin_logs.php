<?php
/**
 * System Logs - Perfect Admin
 */

$logs = $adminService->getSystemLogs();
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?php echo h($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo h($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">System Activity Logs</h5>
                    <div class="d-flex gap-2">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to clear all logs?');" style="display:inline;">
                            <?php echo getCsrfField(); ?>
                            <input type="hidden" name="action" value="clear_logs">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>Clear All Logs
                            </button>
                        </form>
                        <a href="admin.php?action=export_logs&csrf_token=<?php echo h(generateCSRFToken()); ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-export me-1"></i>Export CSV
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Level</th>
                                <th>Message</th>
                                <th>User</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo h($log['timestamp']); ?></td>
                                    <td>
                                        <?php
                                        $levelClass = 'info';
                                        if ($log['level'] === 'WARNING') $levelClass = 'warning';
                                        if ($log['level'] === 'ERROR') $levelClass = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo h($levelClass); ?>-subtle text-<?php echo h($levelClass); ?> px-2">
                                            <?php echo h($log['level']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo h($log['message']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-muted me-2"></i>
                                            <span><?php echo h($log['user']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-link text-muted">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
