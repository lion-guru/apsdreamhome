<?php $pageTitle = $pageTitle ?? 'Compliance Auditing'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Compliance Auditing</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Audit Logs</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Action</th><th>User</th><th>Resource</th><th>Status</th><th>Timestamp</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($audit_logs)): ?>
                            <?php foreach ($audit_logs as $log): ?>
                                <tr>
                                    <td><?= $log['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($log['action'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['user'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['resource'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($log['status'] ?? 'info') === 'success' ? 'success' : (($log['status'] ?? 'info') === 'failed' ? 'danger' : 'secondary') ?>"><?= ucfirst($log['status'] ?? 'info') ?></span></td>
                                    <td><?= htmlspecialchars($log['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No audit logs available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
