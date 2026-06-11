<?php $pageTitle = $pageTitle ?? ($page_title ?? 'MLM Analytics'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-sitemap me-2"></i><?= ($pageTitle ?? ($page_title ?? 'MLM Analytics')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">MLM Analytics</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $mlmData = $mlm_stats ?? $mlm_data ?? []; ?>
    <?php if (!empty($mlmData)): ?>
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><i class="fas fa-network-wired me-2"></i>Network Overview</h5>
                    <div class="row g-3 mt-2">
                        <?php $overview = $mlmData['network_overview'] ?? []; ?>
                        <div class="col-md-3">
                            <div class="border-start border-primary border-4 ps-3">
                                <small class="text-muted">Total users</small>
                                <h4><?= number_format($overview['total_associates'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-start border-success border-4 ps-3">
                                <small class="text-muted">Active users</small>
                                <h4><?= number_format($overview['active_associates'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-start border-warning border-4 ps-3">
                                <small class="text-muted">Total Earnings</small>
                                <h4>₹<?= number_format($overview['total_earnings'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-start border-info border-4 ps-3">
                                <small class="text-muted">Avg Level</small>
                                <h4><?= number_format($overview['avg_level'] ?? 0, 1) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-layer-group me-2"></i>Level Distribution</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Level</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php foreach (($mlmData['level_distribution'] ?? []) as $lv): ?>
                                <tr><td><?= ($lv['level'] ?? '') ?></td><td><?= ($lv['count'] ?? 0) ?></td></tr>
                                <?php endforeach; ?>
                                <?php if (empty($mlmData['level_distribution'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No level data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-arrow-trend-up me-2"></i>Commission Trends</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Month</th><th>Commission</th></tr></thead>
                            <tbody>
                                <?php foreach (($mlmData['commission_trends'] ?? []) as $ct): ?>
                                <tr><td><?= ($ct['month'] ?? '') ?></td><td>₹<?= number_format($ct['commission'] ?? 0) ?></td></tr>
                                <?php endforeach; ?>
                                <?php if (empty($mlmData['commission_trends'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No commission data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>#</th><th>Associate</th><th>Earnings</th></tr></thead>
                            <tbody>
                                <?php foreach (($mlmData['top_performers'] ?? []) as $i => $tp): ?>
                                <tr>
                                    <td><?= ($i + 1) ?></td>
                                    <td><?= ($tp['name'] ?? $tp['associate_name'] ?? '') ?></td>
                                    <td>₹<?= number_format($tp['earnings'] ?? $tp['total_commission'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($mlmData['top_performers'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No performer data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-robot me-2"></i>Chatbot Stats</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $chatbot = $mlmData['chatbot_stats'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Total Conversations</span><strong><?= number_format($chatbot['total_conversations'] ?? ($chatbot['conversations'] ?? 0)) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Avg Rating</span><strong><?= ($chatbot['avg_rating'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Resolution Rate</span><strong><?= ($chatbot['resolution_rate'] ?? 'N/A') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>
            <h5>No MLM Data</h5>
            <p class="text-muted mb-0">MLM analytics will appear once the network has activity.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
