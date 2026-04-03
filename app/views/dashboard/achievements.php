<?php
/**
 * User Achievements Dashboard
 */

$page_title = 'My Achievements - APS Dream Home';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-4">
    <div class="text-center mb-5">
        <h1 class="h2 mb-2"><i class="fas fa-trophy text-warning me-2"></i>My Achievements</h1>
        <p class="text-muted">Earn points, unlock badges, and climb the leaderboard</p>
    </div>

    <!-- Points Stats -->
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-3x mb-3 opacity-75"></i>
                    <h3 class="mb-1"><?= number_format($user_points['total_points'] ?? 0) ?></h3>
                    <p class="mb-0">Total Points</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x mb-3 opacity-75"></i>
                    <h3 class="mb-1"><?= number_format($user_points['month_points'] ?? 0) ?></h3>
                    <p class="mb-0">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="fas fa-medal fa-3x mb-3 opacity-75"></i>
                    <h3 class="mb-1"><?= count($user_badges) ?></h3>
                    <p class="mb-0">Badges Earned</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- My Badges -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>My Badges</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($user_badges)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No badges yet. Start exploring properties to earn your first badge!</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($user_badges as $badge): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border-<?= $badge['color'] ?> h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-<?= $badge['icon'] ?> fa-2x text-<?= $badge['color'] ?> mb-2"></i>
                                        <h6 class="mb-1"><?= htmlspecialchars($badge['name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($badge['description']) ?></small>
                                        <br><small class="text-muted">Awarded <?= date('M d, Y', strtotime($badge['awarded_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available Badges -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Badges to Unlock</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        $userBadgeIds = array_column($user_badges, 'badge_id');
                        foreach ($all_badges as $badge):
                            if (in_array($badge['id'], $userBadgeIds)) continue;
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-light h-100 opacity-75">
                                <div class="card-body text-center">
                                    <i class="fas fa-<?= $badge['icon'] ?> fa-2x text-muted mb-2"></i>
                                    <h6 class="mb-1 text-muted"><?= htmlspecialchars($badge['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($badge['description']) ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard & Recent Activity -->
        <div class="col-md-4">
            <!-- Leaderboard -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Leaderboard</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($leaderboard as $index => $user): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?= $index < 3 ? 'warning' : 'secondary' ?> me-2">#<?= $index + 1 ?></span>
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($user['name']) ?></h6>
                                    <small class="text-muted"><?= $user['badge_count'] ?> badges</small>
                                </div>
                            </div>
                            <span class="badge bg-primary"><?= number_format($user['total_points']) ?> pts</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Points -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($recent_points as $point): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?= ucfirst(str_replace('_', ' ', $point['action'])) ?></h6>
                                    <small class="text-muted"><?= date('M d, h:i A', strtotime($point['created_at'])) ?></small>
                                </div>
                                <span class="badge bg-success">+<?= $point['points'] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How to Earn Points -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>How to Earn Points</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-plus fa-2x text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0">Registration</h6>
                            <small class="text-muted">+100 points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-home fa-2x text-info me-3"></i>
                        <div>
                            <h6 class="mb-0">View Property</h6>
                            <small class="text-muted">+10 points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-envelope fa-2x text-warning me-3"></i>
                        <div>
                            <h6 class="mb-0">Submit Enquiry</h6>
                            <small class="text-muted">+25 points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-car fa-2x text-success me-3"></i>
                        <div>
                            <h6 class="mb-0">Site Visit</h6>
                            <small class="text-muted">+50 points</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
