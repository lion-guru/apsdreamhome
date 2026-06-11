<?php
$page_title = $page_title ?? 'KYC Verification Logs';
$logs = $logs ?? [];
ob_start();
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>KYC Verification Logs</h4>
        <a href="/admin/kyc" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to KYC</a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-header aps-cp-card-header">
            <span><i class="fas fa-list me-2"></i>Recent Verification Attempts (Last 100)</span>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-shield-alt fa-3x mb-3 d-block"></i>
                    <p>No verification attempts logged yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Identifier</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>IP Address</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= (int)$log['id'] ?></td>
                                    <td>
                                        <?php if ($log['type'] === 'pan'): ?>
                                            <span class="badge bg-primary">PAN</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Aadhaar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($log['identifier']) ?></code></td>
                                    <td>
                                        <?php if ($log['success']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Success</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($log['message'] ?? '') ?></td>
                                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '') ?></code></td>
                                    <td><?= htmlspecialchars($log['created_at'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
?>
