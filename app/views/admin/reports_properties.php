<?php $pageTitle = $pageTitle ?? ($page_title ?? 'Property Reports'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-file-alt me-2"></i><?= ($pageTitle ?? ($page_title ?? 'Property Reports')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/reports">Reports</a></li>
                    <li class="breadcrumb-item active">Properties</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $propStats = $properties ?? $property_stats ?? []; ?>
    <?php if (!empty($propStats)): ?>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-2x text-primary mb-2"></i>
                    <h5><?= number_format($propStats['total_views'] ?? 0) ?></h5>
                    <small class="text-muted">Total Views</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-heart fa-2x text-danger mb-2"></i>
                    <h5><?= number_format($propStats['total_favorites'] ?? 0) ?></h5>
                    <small class="text-muted">Total Favorites</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-question-circle fa-2x text-warning mb-2"></i>
                    <h5><?= number_format($propStats['total_inquiries'] ?? 0) ?></h5>
                    <small class="text-muted">Total Inquiries</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-2x text-success mb-2"></i>
                    <h5><?= ($propStats['conversion_rate'] ?? 0) ?>%</h5>
                    <small class="text-muted">Conversion Rate</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Property Trends</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Date</th><th>Views</th><th>Favorites</th><th>Inquiries</th></tr></thead>
                            <tbody>
                                <?php foreach (($properties['property_trends'] ?? $property_trends ?? []) as $pt): ?>
                                <tr>
                                    <td><?= ($pt['date'] ?? '') ?></td>
                                    <td><?= ($pt['views'] ?? 0) ?></td>
                                    <td><?= ($pt['favorites'] ?? 0) ?></td>
                                    <td><?= ($pt['inquiries'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($properties['property_trends'] ?? $property_trends ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No trend data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Performers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Property</th><th>Views</th><th>Favorites</th><th>Inquiries</th></tr></thead>
                            <tbody>
                                <?php foreach (($top_performers ?? $properties['top_performers'] ?? []) as $tp): ?>
                                <tr>
                                    <td><?= ($tp['title'] ?? '') ?></td>
                                    <td><?= ($tp['views'] ?? 0) ?></td>
                                    <td><?= ($tp['favorites'] ?? 0) ?></td>
                                    <td><?= ($tp['inquiries'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($top_performers ?? $properties['top_performers'] ?? [])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>
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
            <h5>No Property Report Data</h5>
            <p class="text-muted mb-0">Property reports will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
