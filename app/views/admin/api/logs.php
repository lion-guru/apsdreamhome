<?php

/**
 * API Request Logs - APS Dream Home Admin
 */
$page_title = $page_title ?? 'API Logs';
$page_description = 'View API request and integration logs';
$logs = $logs ?? [];

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">API Logs</h1>
            <p class="text-muted">Monitor API requests and integration activity</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="logTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="request-logs-tab" data-bs-toggle="tab" data-bs-target="#request-logs" type="button" role="tab">
                        <i class="fas fa-list"></i> Request Logs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="integration-logs-tab" data-bs-toggle="tab" data-bs-target="#integration-logs" type="button" role="tab">
                        <i class="fas fa-exchange-alt"></i> Integration Logs
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="logTabsContent">
        <div class="tab-pane fade show active" id="request-logs" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">API Request Logs</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/api/integration-logs" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-exchange-alt"></i> View Integration Logs
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Endpoint</th>
                                    <th>Developer</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                    <th>Request Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                        No request logs recorded yet.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id'] ?? ''; ?></td>
                                    <td><code class="small"><?php echo htmlspecialchars($log['endpoint'] ?? ''); ?></code></td>
                                    <td><?php echo htmlspecialchars($log['dev_name'] ?? 'Unknown'); ?></td>
                                    <td><code class="small text-muted"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></code></td>
                                    <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                        <?php echo htmlspecialchars(substr($log['user_agent'] ?? '', 0, 60)); ?>
                                    </td>
                                    <td><?php echo date('d M Y H:i:s', strtotime($log['request_time'] ?? 'now')); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="integration-logs" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Integration Logs</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/api/integration-logs" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-expand"></i> Full View
                    </a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-arrow-right me-2"></i>
                        <a href="<?php echo BASE_URL; ?>/admin/api/integration-logs">View full integration logs</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
