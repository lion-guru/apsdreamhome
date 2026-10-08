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
$premiumStats = $premiumStats ?? ['featured' => 0, 'urgent' => 0, 'premium' => 0, 'packages_active' => 0, 'package_revenue' => 0];
$featuredListings = $featuredListings ?? [];
$urgentListings = $urgentListings ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';

// Transaction & Revenue
$boostTransactions = $boostTransactions ?? 0;
$boostRevenue = $boostRevenue ?? 0;
$transactionFees = $transactionFees ?? 0;
$totalPlatformRevenue = $totalPlatformRevenue ?? 0;
$recentBoostTransactions = $recentBoostTransactions ?? [];

// Builder Stats
$builderStats = $builderStats ?? ['total_builders' => 0, 'active_subscriptions' => 0, 'total_listings_by_builders' => 0, 'subscription_revenue' => 0, 'top_builders' => []];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-store me-2 text-primary"></i>Marketplace - Resell Properties</h2>
        <div>
            <a href="<?= $base ?>/admin/user-properties" class="btn btn-outline-primary btn-sm"><i class="fas fa-list me-1"></i>All Properties</a>
            <a href="<?= $base ?>/admin/premium-packages" class="btn btn-outline-warning btn-sm"><i class="fas fa-crown me-1"></i>Premium Packages</a>
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

    <!-- Transaction & Revenue Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Boost Transactions</div><div class="aps-cp-stat-value text-primary"><?= $boostTransactions ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Boost Revenue</div><div class="aps-cp-stat-value text-success">₹<?= number_format($boostRevenue) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Transaction Fees</div><div class="aps-cp-stat-value text-warning">₹<?= number_format($transactionFees) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Revenue</div><div class="aps-cp-stat-value text-primary">₹<?= number_format($totalPlatformRevenue) ?></div></div></div></div>
    </div>

    <!-- Builder Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Builders</div><div class="aps-cp-stat-value text-primary"><?= $builderStats['total_builders'] ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Active Subscriptions</div><div class="aps-cp-stat-value text-success"><?= $builderStats['active_subscriptions'] ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Builder Listings</div><div class="aps-cp-stat-value text-info"><?= $builderStats['total_listings_by_builders'] ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Subscription Revenue</div><div class="aps-cp-stat-value text-success">₹<?= number_format($builderStats['subscription_revenue']) ?></div></div></div></div>
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
                                <div><strong class="small"><?= htmlspecialchars($loc['loc'] ?? '') ?></strong></div>
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
                            <div class="d-flex justify-content-between"><small class="text-capitalize"><?= htmlspecialchars($td['property_type'] ?? '') ?></small><small><?= $td['cnt'] ?> (<?= $pct ?>%)</small></div>
                            <div class="progress"><div class="progress-bar bg-primary"></div></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($typeDistribution)): ?><div class="text-center text-muted py-3">No listings</div><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-crown me-2 text-warning"></i>Premium Stats</div>
                <div class="aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><small><i class="fas fa-star text-warning me-1"></i>Featured</small><span class="badge bg-warning"><?= $premiumStats['featured'] ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><small><i class="fas fa-bolt text-danger me-1"></i>Urgent</small><span class="badge bg-danger"><?= $premiumStats['urgent'] ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><small><i class="fas fa-gem text-primary me-1"></i>Premium</small><span class="badge bg-primary"><?= $premiumStats['premium'] ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><small><i class="fas fa-box me-1"></i>Active Packages</small><span class="badge bg-success"><?= $premiumStats['packages_active'] ?></span></div>
                    <div class="d-flex justify-content-between"><small><i class="fas fa-money-bill me-1"></i>Package Revenue</small><span class="badge bg-info">₹<?= number_format($premiumStats['package_revenue']) ?></span></div>
                </div>
            </div>
            <?php if (!empty($featuredListings)): ?>
            <div class="aps-cp-card mt-3">
                <div class="aps-cp-card-header"><i class="fas fa-star text-warning me-2"></i>Featured Properties</div>
                <div class="aps-cp-card-body p-2">
                    <?php foreach ($featuredListings as $f): ?>
                        <div class="d-flex justify-content-between align-items-center p-1 border-bottom small">
                            <span><?= htmlspecialchars(mb_substr($f['name'] ?? '', 0, 25)) ?></span>
                            <form method="post" action="<?= $base ?>/admin/marketplace/toggle-featured" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Remove featured" aria-label="Close"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($urgentListings)): ?>
            <div class="aps-cp-card mt-3">
                <div class="aps-cp-card-header"><i class="fas fa-bolt text-danger me-2"></i>Urgent Properties</div>
                <div class="aps-cp-card-body p-2">
                    <?php foreach ($urgentListings as $u): ?>
                        <div class="d-flex justify-content-between align-items-center p-1 border-bottom small">
                            <span><?= htmlspecialchars(mb_substr($u['name'] ?? '', 0, 25)) ?></span>
                            <form method="post" action="<?= $base ?>/admin/marketplace/toggle-urgent" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Remove urgent" aria-label="Close"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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
                        <thead><tr><th>ID</th><th>Property</th><th>Type</th><th>Price</th><th>Location</th><th>Seller</th><th>Premium</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentListings as $p): ?>
                            <tr>
                                <td>#<?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars(mb_substr($p['name'] ?? '', 0, 40)) ?></strong></td>
                                <td><span class="text-capitalize"><?= htmlspecialchars($p['property_type'] ?? '') ?></span></td>
                                <td>₹<?= $p['price'] > 100000 ? number_format($p['price']/100000,1).'L' : number_format($p['price']) ?></td>
                                <td class="small"><?= htmlspecialchars($p['location'] ?? $p['city_name'] ?? 'N/A') ?></td>
                                <td class="small"><?= htmlspecialchars($p['seller_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($p['is_premium'])): ?><span class="badge bg-primary" title="Premium">P</span><?php endif; ?>
                                    <?php if (!empty($p['is_featured'])): ?><span class="badge bg-warning text-dark" title="Featured"><i class="fas fa-star"></i></span><?php endif; ?>
                                    <?php if (!empty($p['is_urgent'])): ?><span class="badge bg-danger" title="Urgent"><i class="fas fa-bolt"></i></span><?php endif; ?>
                                    <?php if (empty($p['is_premium']) && empty($p['is_featured']) && empty($p['is_urgent'])): ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="aps-cp-badge badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'pending' ? 'warning' : ($p['status'] === 'sold' ? 'info' : 'danger')) ?>"><?= ucfirst(htmlspecialchars($p['status'] ?? '')) ?></span></td>
                                <td><?= (int)$p['views'] ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <form method="post" action="<?= $base ?>/admin/marketplace/toggle-featured" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-outline-warning py-0 px-1" title="<?= !empty($p['is_featured']) ? 'Unfeature' : 'Feature' ?>"><i class="fas fa-star<?= !empty($p['is_featured']) ? '' : '-o' ?>"></i></button>
                                        </form>
                                        <form method="post" action="<?= $base ?>/admin/marketplace/toggle-urgent" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger py-0 px-1" title="<?= !empty($p['is_urgent']) ? 'Remove urgent' : 'Mark urgent' ?>"><i class="fas fa-bolt<?= !empty($p['is_urgent']) ? '' : '' ?>"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

    <!-- Recent Boost Transactions -->
    <div class="aps-cp-card mt-3">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bolt me-2 text-warning"></i>Recent Boost Transactions</span>
            <small class="text-muted">Latest 10 boost payments</small>
        </div>
        <div class="aps-cp-card-body">
            <?php if (empty($recentBoostTransactions)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-bolt fa-2x mb-2 text-muted"></i>
                    <p>No boost transactions yet</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Seller</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBoostTransactions as $txn): ?>
                                <tr>
                                    <td>#<?= $txn['id'] ?></td>
                                    <td><strong><?= htmlspecialchars(mb_substr($txn['property_name'] ?? '', 0, 30)) ?></strong></td>
                                    <td class="small"><?= htmlspecialchars($txn['seller_name'] ?? 'N/A') ?></td>
                                    <td>₹<?= number_format($txn['amount']) ?></td>
                                    <td class="small"><?= date('d M Y H:i', strtotime($txn['recorded_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Builders -->
    <div class="aps-cp-card mt-3">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-building me-2 text-primary"></i>Top Builders</span>
            <small class="text-muted">By listing count</small>
        </div>
        <div class="aps-cp-card-body">
            <?php if (empty($builderStats['top_builders'])): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-building fa-2x mb-2 text-muted"></i>
                    <p>No builder data available</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Builder</th>
                                <th>Email</th>
                                <th>Listings</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($builderStats['top_builders'] as $builder): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($builder['name'] ?? 'N/A') ?></strong></td>
                                    <td class="small"><?= htmlspecialchars($builder['email'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-primary"><?= (int)$builder['listing_count'] ?></span></td>
                                    <td>₹<?= number_format($builder['total_value']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
