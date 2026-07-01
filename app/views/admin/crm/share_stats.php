<?php
$page_title = $page_title ?? 'Share Analytics';
$base = defined('BASE_URL') ? BASE_URL : '';
$total_shares = $total_shares ?? 0;
$shares_by_platform = $shares_by_platform ?? [];
$shares_by_user = $shares_by_user ?? [];
$recent_shares = $recent_shares ?? [];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-share-alt me-2 text-primary"></i>Share Analytics</h4>
        <a href="<?= $base ?>/admin/crm" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to CRM</a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#6366f1;"><?= number_format($total_shares) ?></div>
                <div class="text-muted small">Total Shares</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#10b981;"><?= count($shares_by_user) ?></div>
                <div class="text-muted small">Active Sharers</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#f59e0b;"><?= count($shares_by_platform) ?></div>
                <div class="text-muted small">Platforms Used</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#ef4444;"><?= count($recent_shares) ?></div>
                <div class="text-muted small">Recent Shares</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- By Platform -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-chart-pie me-2"></i>Shares by Platform</h5></div>
                <div class="card-body">
                    <?php if (empty($shares_by_platform)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-share-alt fa-3x mb-3 opacity-25"></i>
                            <p>No shares recorded yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($shares_by_platform as $p): ?>
                            <?php
                            $pct = $total_shares > 0 ? round(($p['cnt'] / $total_shares) * 100) : 0;
                            $colors = ['whatsapp' => '#25D366', 'facebook' => '#1877F2', 'twitter' => '#1DA1F2', 'telegram' => '#0088CC', 'linkedin' => '#0A66C2', 'email' => '#EA4335', 'sms' => '#FF9800', 'copy' => '#6366F1'];
                            $color = $colors[$p['platform']] ?? '#94a3b8';
                            ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3" style="width:40px;height:40px;border-radius:10px;background:<?= $color ?>;color:#fff;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-<?= $p['platform'] === 'whatsapp' ? 'whatsapp' : ($p['platform'] === 'copy' ? 'copy' : 'share-alt') ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold small"><?= ucfirst($p['platform']) ?></span>
                                        <span class="text-muted small"><?= $p['cnt'] ?> shares (<?= $pct ?>%)</span>
                                    </div>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Sharers -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-trophy me-2"></i>Top Sharers</h5></div>
                <div class="card-body">
                    <?php if (empty($shares_by_user)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                            <p>No sharers yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>#</th><th>User</th><th class="text-end">Shares</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shares_by_user as $i => $u): ?>
                                        <tr>
                                            <td><span class="badge bg-<?= $i === 0 ? 'warning' : ($i === 1 ? 'secondary' : 'light text-dark') ?>"><?= $i + 1 ?></span></td>
                                            <td class="fw-bold"><?= htmlspecialchars($u['name'] ?? 'Unknown') ?></td>
                                            <td class="text-end"><?= $u['share_count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Shares -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-clock me-2"></i>Recent Shares</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr><th>Date</th><th>Sharer</th><th>Lead</th><th>Platform</th><th>Phone</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_shares)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No shares yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_shares as $s): ?>
                                <tr>
                                    <td class="small"><?= date('M d, H:i', strtotime($s['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($s['sharer_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($s['lead_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($s['share_method'] ?? '-') ?></span></td>
                                    <td class="small"><?= htmlspecialchars($s['shared_to_phone'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($s['status'] ?? '') === 'replied' ? 'success' : 'secondary' ?>"><?= ucfirst($s['status'] ?? '-') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
