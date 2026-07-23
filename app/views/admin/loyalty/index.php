<?php
// Initialize default values if not set
$tiers = $tiers ?? [
    'bronze' => ['min_points' => 0, 'discount' => 5, 'multiplier' => 1, 'benefits' => []],
    'silver' => ['min_points' => 500, 'discount' => 10, 'multiplier' => 1.5, 'benefits' => []],
    'gold' => ['min_points' => 2000, 'discount' => 15, 'multiplier' => 2, 'benefits' => []],
    'platinum' => ['min_points' => 5000, 'discount' => 20, 'multiplier' => 2.5, 'benefits' => []],
    'diamond' => ['min_points' => 10000, 'discount' => 25, 'multiplier' => 3, 'benefits' => []]
];
$stats = $stats ?? [
    'members_by_tier' => [],
    'total_active_points' => 0,
    'redemptions_30d' => 0,
    'points_redeemed_30d' => 0
];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">🏆 Loyalty Program Dashboard</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/loyalty/members" class="btn btn-primary me-2">
                <i class="fas fa-users"></i> Members
            </a>
            <a href="<?= BASE_URL ?>/admin/loyalty/rewards" class="btn btn-success me-2">
                <i class="fas fa-gift"></i> Rewards
            </a>
            <a href="<?= BASE_URL ?>/admin/loyalty/redemptions" class="btn btn-info">
                <i class="fas fa-ticket-alt"></i> Redemptions
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Members
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format(array_sum(array_column($stats['members_by_tier'] ?? [], 'count'))) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Points in Circulation
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_active_points'] ?? 0) ?> pts
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Redemptions (30d)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['redemptions_30d'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Points Redeemed (30d)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['points_redeemed_30d'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-fire fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tiers Overview -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tier Overview</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <?php foreach ($tiers as $tierName => $tierData): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-uppercase"><?= ucfirst($tierName) ?></h5>
                                    <div class="mb-3">
                                        <span class="badge bg-<?= $tierName === 'diamond' ? 'dark' : ($tierName === 'platinum' ? 'primary' : ($tierName === 'gold' ? 'warning' : ($tierName === 'silver' ? 'secondary' : 'light'))) ?> p-2">
                                            <?= $tierData['min_points'] ?>+ Points
                                        </span>
                                    </div>
                                    <ul class="list-unstyled text-start small">
                                        <li><i class="fas fa-check text-success"></i> <?= $tierData['discount'] ?>% Discount</li>
                                        <li><i class="fas fa-check text-success"></i> <?= $tierData['multiplier'] ?>x Points Multiplier</li>
                                        <?php foreach (array_slice($tierData['benefits'], 0, 3) as $benefit): ?>
                                        <li><i class="fas fa-star text-warning"></i> <?= $benefit['benefit_name'] ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <a href="<?= BASE_URL ?>/admin/loyalty/members" class="btn btn-outline-primary btn-block mb-3">
                        <i class="fas fa-users"></i> View All Members
                    </a>
                    <a href="<?= BASE_URL ?>/admin/loyalty/rewards" class="btn btn-outline-success btn-block mb-3">
                        <i class="fas fa-gift"></i> Manage Rewards
                    </a>
                    <a href="<?= BASE_URL ?>/admin/loyalty/rules" class="btn btn-outline-info btn-block mb-3">
                        <i class="fas fa-cog"></i> Points Rules
                    </a>
                    <a href="<?= BASE_URL ?>/admin/loyalty/tier-benefits" class="btn btn-outline-warning btn-block">
                        <i class="fas fa-crown"></i> Tier Benefits
                    </a>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted text-center">No recent activity to display</p>
                </div>
            </div>
        </div>
    </div>
</div>


