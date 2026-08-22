<?php $pageTitle = 'Marketing Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Marketing Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Marketing Dashboard</h4>
        <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?= date('l, F j, Y') ?></span>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="fas fa-bullhorn"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($activeCampaigns ?? 0) ?></h3>
                    <p class="text-muted mb-0">Active Campaigns</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-users"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($totalLeads ?? 0) ?></h3>
                    <p class="text-muted mb-0">Total Leads</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-newspaper"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($blogPosts ?? 0) ?></h3>
                    <p class="text-muted mb-0">Blog Posts</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-share-alt"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($socialShares ?? 0) ?></h3>
                    <p class="text-muted mb-0">Social Shares</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Campaign Performance</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($campaigns)): ?>
                        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Campaign</th><th>Type</th><th>Status</th><th class="text-end">Leads</th></tr></thead>
                            <tbody>
                                <?php foreach ($campaigns as $cmp): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($cmp['name'] ?? '') ?></td>
                                    <td><span class="badge bg-info"><?= ucfirst($cmp['type'] ?? '') ?></span></td>
                                    <td><span class="badge bg-<?= ($cmp['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($cmp['status'] ?? '') ?></span></td>
                                    <td class="text-end small"><?= e($cmp['leads_count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-bullhorn fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No active campaigns</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Lead Sources</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($leadSources)): ?>
                        <?php foreach ($leadSources as $src): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?= htmlspecialchars($src['source'] ?? '') ?></span>
                            <span class="badge bg-primary"><?= e($src['count'] ?? 0) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-chart-bar fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No lead source data</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
