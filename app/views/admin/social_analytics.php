<?php $pageTitle = $pageTitle ?? 'Social Analytics'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Analytics</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-primary mb-2"><i class="fas fa-thumbs-up"></i></div>
                    <h5 class="mb-1"><?= number_format($social_stats['total_followers'] ?? 0) ?></h5>
                    <small class="text-muted">Total Followers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-retweet"></i></div>
                    <h5 class="mb-1"><?= number_format($social_stats['total_engagement'] ?? 0) ?></h5>
                    <small class="text-muted">Engagement</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-chart-line"></i></div>
                    <h5 class="mb-1"><?= number_format($social_stats['growth_rate'] ?? 0, 1) ?>%</h5>
                    <small class="text-muted">Growth Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-newspaper"></i></div>
                    <h5 class="mb-1"><?= number_format($social_stats['posts_count'] ?? 0) ?></h5>
                    <small class="text-muted">Posts</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Platform Breakdown</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Platform</th><th>Followers</th><th>Engagement</th><th>Posts</th><th>Growth</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($social_stats['platforms'] ?? [])): ?>
                            <?php foreach ($social_stats['platforms'] as $p): ?>
                                <tr>
                                    <td><i class="fab fa-<?= strtolower($p['name'] ?? 'globe') ?> me-1"></i><?= htmlspecialchars($p['name'] ?? '-') ?></td>
                                    <td><?= number_format($p['followers'] ?? 0) ?></td>
                                    <td><?= number_format($p['engagement'] ?? 0) ?></td>
                                    <td><?= number_format($p['posts'] ?? 0) ?></td>
                                    <td><span class="badge bg-<?= (($p['growth'] ?? 0) >= 0) ? 'success' : 'danger' ?>"><?= ($p['growth'] ?? 0) >= 0 ? '+' : '' ?><?= htmlspecialchars($p['growth'] ?? 0) ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No platform data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
