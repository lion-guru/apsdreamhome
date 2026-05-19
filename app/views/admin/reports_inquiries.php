<?php $pageTitle = $pageTitle ?? ($page_title ?? 'Inquiry Reports'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file-alt me-2"></i><?= ($pageTitle ?? ($page_title ?? 'Inquiry Reports')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Inquiries</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $inqStats = $inquiries ?? $inquiry_stats ?? []; ?>
    <?php if (!empty($inqStats)): ?>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-question-circle fa-2x text-primary mb-2"></i>
                    <h5><?= number_format($inqStats['total_inquiries'] ?? 0) ?></h5>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-2x text-info mb-2"></i>
                    <h5><?= number_format($inqStats['new_inquiries'] ?? 0) ?></h5>
                    <small class="text-muted">New (30d)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h5><?= ($inqStats['response_rate'] ?? 0) ?>%</h5>
                    <small class="text-muted">Response Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h5><?= ($inqStats['avg_response_time'] ?? '0h') ?></h5>
                    <small class="text-muted">Avg Response Time</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Inquiry Trends</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Date</th><th>Inquiries</th><th>Responses</th></tr></thead>
                            <tbody>
                                <?php foreach (($inquiry_trends ?? $inquiries['inquiry_trends'] ?? []) as $it): ?>
                                <tr>
                                    <td><?= ($it['date'] ?? '') ?></td>
                                    <td><?= ($it['inquiries'] ?? 0) ?></td>
                                    <td><?= ($it['responses'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($inquiry_trends ?? $inquiries['inquiry_trends'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No trend data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Response Times</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Time Range</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php foreach (($response_times ?? $inquiries['response_times'] ?? []) as $rt): ?>
                                <tr>
                                    <td><?= ($rt['range'] ?? '') ?></td>
                                    <td><?= ($rt['count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($response_times ?? $inquiries['response_times'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No response time data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-users-gear me-2"></i>Agent Performance</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Agent</th><th>Assigned</th><th>Responses</th><th>Avg Response Time</th></tr></thead>
                            <tbody>
                                <?php foreach (($agent_performance ?? $inquiries['agent_performance'] ?? []) as $ap): ?>
                                <tr>
                                    <td><?= ($ap['agent'] ?? '') ?></td>
                                    <td><?= ($ap['inquiries_assigned'] ?? 0) ?></td>
                                    <td><?= ($ap['responses'] ?? 0) ?></td>
                                    <td><?= ($ap['avg_response_time'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($agent_performance ?? $inquiries['agent_performance'] ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No agent performance data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
            <h5>No Inquiry Report Data</h5>
            <p class="text-muted mb-0">Inquiry reports will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
