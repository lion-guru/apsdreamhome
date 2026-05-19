<?php $pageTitle = $pageTitle ?? ($page_title ?? 'Property Analytics'); ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-building me-2"></i><?= ($pageTitle ?? ($page_title ?? 'Property Analytics')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Property Analytics</li>
                </ul>
            </div>
        </div>
    </div>
    <?php $propData = $property_stats ?? $property_data ?? []; ?>
    <?php if (!empty($propData)): ?>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Type Distribution</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Type</th><th>Count</th><th>Avg Price</th></tr></thead>
                            <tbody>
                                <?php foreach (($propData['type_distribution'] ?? []) as $row): ?>
                                <tr>
                                    <td><?= ($row['type'] ?? '') ?></td>
                                    <td><?= ($row['count'] ?? 0) ?></td>
                                    <td>₹<?= number_format($row['avg_price'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($propData['type_distribution'] ?? [])): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Price Distribution</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Price Range</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php foreach (($propData['price_distribution'] ?? []) as $row): ?>
                                <tr>
                                    <td><?= ($row['price_range'] ?? '') ?></td>
                                    <td><?= ($row['count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($propData['price_distribution'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-location-dot me-2"></i>Location Performance</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>City</th><th>State</th><th>Properties</th><th>Avg Price</th><th>Agents</th></tr></thead>
                            <tbody>
                                <?php foreach (($propData['location_performance'] ?? []) as $loc): ?>
                                <tr>
                                    <td><?= ($loc['city'] ?? '') ?></td>
                                    <td><?= ($loc['state'] ?? '') ?></td>
                                    <td><?= ($loc['properties'] ?? 0) ?></td>
                                    <td>₹<?= number_format($loc['avg_price'] ?? 0) ?></td>
                                    <td><?= ($loc['agents'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($propData['location_performance'] ?? [])): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No location data</td></tr>
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
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Property Age</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Age Group</th><th>Count</th></tr></thead>
                            <tbody>
                                <?php foreach (($propData['property_age'] ?? []) as $age): ?>
                                <tr>
                                    <td><?= ($age['age_group'] ?? '') ?></td>
                                    <td><?= ($age['count'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($propData['property_age'] ?? [])): ?>
                                <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
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
            <i class="fas fa-building fa-4x text-muted mb-3"></i>
            <h5>No Property Data</h5>
            <p class="text-muted mb-0">Property analytics will appear once data is available.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
