<?php

/**
 * Integration Logs - APS Dream Home Admin
 */
$page_title = $page_title ?? 'Integration Logs';
$page_description = 'View integration activity logs';
$logs = $logs ?? [];

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Integration Logs</h1>
            <p class="text-muted">Track all integration activities including inbound and outbound requests</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo BASE_URL; ?>/admin/api/logs" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Request Logs
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/api/integrations" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-plug"></i> Manage Integrations
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Integration Activity Log</h5>
                    <span class="badge bg-primary"><?php echo count($logs); ?> entries</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Integration</th>
                                    <th>Action</th>
                                    <th>Direction</th>
                                    <th>Status</th>
                                    <th>Error Message</th>
                                    <th>Created</th>
                                    <th>Processed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No integration logs recorded yet.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id'] ?? ''; ?></td>
                                    <td><?php echo htmlspecialchars($log['service_name'] ?? 'N/A'); ?></td>
                                    <td><code class="small"><?php echo htmlspecialchars($log['action'] ?? ''); ?></code></td>
                                    <td>
                                        <?php if (($log['direction'] ?? '') === 'inbound'): ?>
                                            <span class="badge bg-info"><i class="fas fa-arrow-down"></i> Inbound</span>
                                        <?php elseif (($log['direction'] ?? '') === 'outbound'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-arrow-up"></i> Outbound</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($log['direction'] ?? ''); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($log['status'] ?? '') === 'success'): ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php elseif (($log['status'] ?? '') === 'error'): ?>
                                            <span class="badge bg-danger">Error</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-truncate" class="style-14387" title="<?php echo htmlspecialchars($log['error_message'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($log['error_message'] ?? '-'); ?>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($log['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <?php if (!empty($log['processed_at'])): ?>
                                            <?php echo date('d M Y H:i', strtotime($log['processed_at'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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
    </div>
</div>
