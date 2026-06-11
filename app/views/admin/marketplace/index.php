<?php
$page_title = $page_title ?? 'Marketplace - Resell Properties';
$activeListings = $activeListings ?? 0;
$pendingApprovals = $pendingApprovals ?? 0;
$soldCount = $soldCount ?? 0;
$avgPrice = $avgPrice ?? 0;
$totalViews = $totalViews ?? 0;
$topLocations = $topLocations ?? [];
$recentListings = $recentListings ?? [];
$typeDistribution = $typeDistribution ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-store me-2 text-primary"></i>Marketplace - Resell Properties</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/user-properties" class="btn btn-outline-primary btn-sm"><i class="fas fa-list me-1"></i>All Properties</a>
            <a href="<?= BASE_URL ?>/admin/resell-properties" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Resell Listings</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Active Listings</div><div class="aps-cp-stat-value text-success"><?= $activeListings ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Pending Approvals</div><div class="aps-cp-stat-value text-warning"><?= $pendingApprovals ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Sold</div><div class="aps-cp-stat-value text-info"><?= $soldCount ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Avg Price</div><div class="aps-cp-stat-value">₹<?= $avgPrice > 100000 ? number_format($avgPrice/100000,1).'L' : number_format($avgPrice) ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Views</div><div class="aps-cp-stat-value"><?= number_format($totalViews) ?></div></div></div></div>
        <div class="col-md-2"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Listings</div><div class="aps-cp-stat-value"><?= $activeListings + $pendingApprovals + $soldCount ?></div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-map-marker-alt me-2"></i>Top Locations</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($topLocations)): ?>
                        <div class="text-center text-muted py-3">No listings yet</div>
                    <?php else: ?>
                        <?php foreach ($topLocations as $loc): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div><strong class="small"><?= htmlspecialchars($loc['loc']) ?></strong></div>
                                <div class="text-end"><span class="badge bg-primary"><?= $loc['cnt'] ?></span><br><small class="text-muted">Avg ₹<?= $loc['avg_price'] > 100000 ? number_format($loc['avg_price']/100000,1).'L' : number_format($loc['avg_price']) ?></small></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-home me-2"></i>Property Types</div>
                <div class="aps-cp-card-body">
                    <?php foreach ($typeDistribution as $td): ?>
                        <?php $pct = $activeListings > 0 ? round($td['cnt']/$activeListings*100) : 0; ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between"><small class="text-capitalize"><?= htmlspecialchars($td['property_type']) ?></small><small><?= $td['cnt'] ?> (<?= $pct ?>%)</small></div>
                            <div class="progress" style="height:6px"><div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($typeDistribution)): ?><div class="text-center text-muted py-3">No listings</div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-pie me-2"></i>Status Breakdown</div>
                <div class="aps-cp-card-body">
                    <?php $total = $activeListings + $pendingApprovals + $soldCount; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small>Active Listings</small><small><?= $activeListings ?></small></div>
                        <div class="progress" style="height:10px"><div class="progress-bar bg-success" style="width:<?= $total > 0 ? round($activeListings/$total*100) : 0 ?>%"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between"><small>Pending Approval</small><small><?= $pendingApprovals ?></small></div>
                        <div class="progress" style="height:10px"><div class="progress-bar bg-warning" style="width:<?= $total > 0 ? round($pendingApprovals/$total*100) : 0 ?>%"></div></div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between"><small>Sold</small><small><?= $soldCount ?></small></div>
                        <div class="progress" style="height:10px"><div class="progress-bar bg-info" style="width:<?= $total > 0 ? round($soldCount/$total*100) : 0 ?>%"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Recent Listings</div>
        <div class="aps-cp-card-body">
            <?php if (empty($recentListings)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-store fa-2x mb-2"></i><p>No listings found</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Property</th><th>Type</th><th>Price</th><th>Location</th><th>Seller</th><th>Status</th><th>Views</th><th>Created</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentListings as $p): ?>
                            <tr>
                                <td>#<?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars(mb_substr($p['name'], 0, 40)) ?></strong></td>
                                <td><span class="text-capitalize"><?= htmlspecialchars($p['property_type']) ?></span></td>
                                <td>₹<?= $p['price'] > 100000 ? number_format($p['price']/100000,1).'L' : number_format($p['price']) ?></td>
                                <td class="small"><?= htmlspecialchars($p['location'] ?? $p['city_name'] ?? 'N/A') ?></td>
                                <td class="small"><?= htmlspecialchars($p['seller_name'] ?? 'N/A') ?></td>
                                <td><span class="aps-cp-badge badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'pending' ? 'warning' : ($p['status'] === 'sold' ? 'info' : 'danger')) ?>"><?= ucfirst(htmlspecialchars($p['status'])) ?></span></td>
                                <td><?= (int)$p['views'] ?></td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
