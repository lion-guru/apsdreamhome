<?php $pageTitle = $pageTitle ?? ($page_title ?? 'Advanced Analytics'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-chart-pie me-2"></i><?= ($pageTitle ?? ($page_title ?? 'Advanced Analytics')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Advanced Analytics</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $analyticsData = $analytics ?? $analytics_data ?? ['overview' => [], 'revenue' => [], 'properties' => [], 'users' => []]; ?>
    <?php if (!empty($analyticsData)): ?>
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title"><i class="fas fa-chart-simple me-2"></i>Overview</h5>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <div class="border-start border-primary border-4 ps-3">
                                <small class="text-muted">Total Properties</small>
                                <h4><?= number_format($analyticsData['overview']['total_properties'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-start border-success border-4 ps-3">
                                <small class="text-muted">Total Users</small>
                                <h4><?= number_format($analyticsData['overview']['total_users'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-start border-warning border-4 ps-3">
                                <small class="text-muted">Total Inquiries</small>
                                <h4><?= number_format($analyticsData['overview']['total_inquiries'] ?? 0) ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-start border-info border-4 ps-3">
                                <small class="text-muted">Total Revenue</small>
                                <h4>₹<?= number_format($analyticsData['overview']['total_revenue'] ?? 0) ?></h4>
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
                    <h6 class="mb-0"><i class="fas fa-arrow-trend-up me-2"></i>Growth Trends</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Month</th><th>Properties</th><th>Users</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php foreach (($analyticsData['growth_trends'] ?? []) as $trend): ?>
                                <tr>
                                    <td><?= ($trend['month'] ?? '') ?></td>
                                    <td><?= ($trend['properties'] ?? 0) ?></td>
                                    <td><?= ($trend['users'] ?? 0) ?></td>
                                    <td>₹<?= number_format($trend['revenue'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($analyticsData['growth_trends'] ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No growth trend data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-location-dot me-2"></i>Top Locations</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>City</th><th>State</th><th>Properties</th><th>Avg Price</th></tr></thead>
                            <tbody>
                                <?php foreach (($analyticsData['top_locations'] ?? []) as $loc): ?>
                                <tr>
                                    <td><?= ($loc['city'] ?? '') ?></td>
                                    <td><?= ($loc['state'] ?? '') ?></td>
                                    <td><?= ($loc['property_count'] ?? 0) ?></td>
                                    <td>₹<?= number_format($loc['avg_price'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($analyticsData['top_locations'] ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No location data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-user-group me-2"></i>User Engagement</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $engagement = $analyticsData['user_engagement'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Avg Session</span><strong><?= $engagement['avg_session_duration'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Pages/Session</span><strong><?= $engagement['pages_per_session'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Bounce Rate</span><strong><?= $engagement['bounce_rate'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Return Visitors</span><strong><?= $engagement['return_visitor_rate'] ?? 'N/A' ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>Property Performance</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php $perf = $analyticsData['property_performance'] ?? []; ?>
                    <div class="d-flex justify-content-between mb-2"><span>Avg Days on Market</span><strong><?= $perf['avg_days_on_market'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Price Reduction Rate</span><strong><?= $perf['price_reduction_rate'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>View to Inquiry Ratio</span><strong><?= $perf['view_to_inquiry_ratio'] ?? 'N/A' ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Inquiry to Sale Ratio</span><strong><?= $perf['inquiry_to_sale_ratio'] ?? 'N/A' ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                    <h5>No Analytics Data</h5>
                    <p class="text-muted mb-0">Analytics data will appear once the system collects enough information.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
