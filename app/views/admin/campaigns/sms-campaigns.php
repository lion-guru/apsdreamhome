<?php
$page_title = $page_title ?? 'SMS Campaigns';
$campaigns = $campaigns ?? [];
$stats = $stats ?? ['total' => 0, 'sent' => 0, 'draft' => 0];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-1"><i class="fas fa-sms me-2 text-success"></i>SMS Campaigns</h2>
        <a href="<?php echo e($base); ?>/admin/campaigns" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body text-center">
                <h4 class="text-primary"><?php echo e($stats['total']); ?></h4><p class="text-muted mb-0">Total</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body text-center">
                <h4 class="text-success"><?php echo e($stats['sent']); ?></h4><p class="text-muted mb-0">Sent</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm"><div class="card-body text-center">
                <h4 class="text-warning"><?php echo e($stats['draft']); ?></h4><p class="text-muted mb-0">Drafts</p>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($campaigns)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Campaign</th><th>Channel</th><th>Status</th><th>Recipients</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $c): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($c['name'] ?? $c['campaign_name'] ?? '-'); ?></strong></td>
                                    <td><span class="badge bg-info"><?php echo strtoupper(htmlspecialchars($c['channel'] ?? 'sms')); ?></span></td>
                                    <td><span class="badge bg-<?php echo ($c['status'] ?? '') === 'sent' ? 'success' : (($c['status'] ?? '') === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo ucfirst($c['status'] ?? 'unknown'); ?></span></td>
                                    <td><?php echo number_format($c['total_recipients'] ?? $c['recipient_count'] ?? 0); ?></td>
                                    <td><?php echo isset($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-sms fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No SMS campaigns found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
