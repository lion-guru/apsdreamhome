<?php $page_title = $page_title ?? 'Developer Portal';
$totalApps = $totalApps ?? 0;
$activeApps = $activeApps ?? 0;
$totalKeys = $totalKeys ?? 0;
$activeKeys = $activeKeys ?? 0;
$totalWebhooks = $totalWebhooks ?? 0;
$activeWebhooks = $activeWebhooks ?? 0;
$recentCalls = $recentCalls ?? [];
$apps = $apps ?? [];
$webhooks = $webhooks ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-code me-2 text-primary"></i>Developer Portal</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/api-keys" class="btn btn-outline-primary btn-sm"><i class="fas fa-key me-1"></i>API Keys</a>
            <a href="<?= BASE_URL ?>/admin/webhooks" class="btn btn-outline-primary btn-sm"><i class="fas fa-plug me-1"></i>Webhooks</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-mobile-alt"></i></span></div>
                    <div><div class="aps-cp-stat-label">Registered Apps</div><div class="aps-cp-stat-value"><?= $totalApps ?></div><div class="aps-cp-stat-meta">Active: <?= $activeApps ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-key"></i></span></div>
                    <div><div class="aps-cp-stat-label">API Keys</div><div class="aps-cp-stat-value"><?= $totalKeys ?></div><div class="aps-cp-stat-meta">Active: <?= $activeKeys ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-plug"></i></span></div>
                    <div><div class="aps-cp-stat-label">Webhook Endpoints</div><div class="aps-cp-stat-value"><?= $totalWebhooks ?></div><div class="aps-cp-stat-meta">Active: <?= $activeWebhooks ?></div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Registered Applications</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($apps)): ?>
                        <div class="text-center text-muted py-4"><i class="fas fa-mobile-alt fa-2x mb-2"></i><p>No registered applications</p></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>App Name</th><th>Email</th><th>Status</th><th>Created</th></tr></thead>
                                <tbody>
                                <?php foreach ($apps as $app): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($app['dev_name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($app['email'] ?? '') ?></td>
                                        <td><span class="aps-cp-badge badge bg-<?= $app['status'] === 'active' ? 'success' : ($app['status'] === 'suspended' ? 'danger' : 'secondary') ?>"><?= ucfirst(htmlspecialchars($app['status'] ?? '')) ?></span></td>
                                        <td class="text-muted small"><?= htmlspecialchars($app['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-key me-2"></i>Recent API Key Usage</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($recentCalls)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No API calls recorded</div>
                    <?php else: ?>
                        <?php foreach ($recentCalls as $call): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div><strong class="small"><?= htmlspecialchars($call['key_name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($call['service_name'] ?? '') ?></small></div>
                                <div class="text-end"><span class="badge bg-secondary"><?= number_format($call['usage_count']) ?> calls</span><br><small class="text-muted"><?= htmlspecialchars($call['last_used_at'] ?? '') ?></small></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-plug me-2"></i>Webhook Endpoints</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($webhooks)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No webhooks configured</div>
                    <?php else: ?>
                        <?php foreach ($webhooks as $wh): ?>
                            <div class="mb-2 p-2 bg-light rounded">
                                <strong class="small"><?= htmlspecialchars($wh['name'] ?? '') ?></strong><br>
                                <code class="small text-muted"><?= htmlspecialchars(mb_substr($wh['url'] ?? '', 0, 50)) ?>...</code><br>
                                <span class="aps-cp-badge badge bg-<?= $wh['is_active'] ? 'success' : 'secondary' ?> mt-1"><?= $wh['is_active'] ? 'Active' : 'Inactive' ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
