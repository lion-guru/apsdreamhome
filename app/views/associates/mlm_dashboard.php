<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'MLM Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .mlm-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .level-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.9em;
        }
        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#28a745 0deg, #ffc107 75%, #dc3545 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tree-node {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
            background: #f8f9fa;
        }
        .tree-node.active {
            background: #e3f2fd;
            border-color: #2196f3;
        }
        .commission-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layouts/associate_header.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="mlm-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2><i class="fas fa-network-wired me-2"></i>Welcome to MLM Dashboard</h2>
                            <p class="mb-0">Track your network, commissions, and achievements</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="level-badge">
                                <i class="fas fa-star me-1"></i>
                                <?php echo $dashboard_data['rank_info']['level_name'] ?? 'Associate'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4><?php echo $dashboard_data['mlm_stats']['overall']['total_downline'] ?? 0; ?></h4>
                        <p class="text-muted mb-0">Total Downline</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-2x text-success mb-2"></i>
                        <h4>₹<?php echo number_format($dashboard_data['rank_info']['total_earnings'] ?? 0); ?></h4>
                        <p class="text-muted mb-0">Total Earnings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                        <h4><?php echo $dashboard_data['rank_info']['current_level'] ?? 1; ?></h4>
                        <p class="text-muted mb-0">Current Level</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-2x text-danger mb-2"></i>
                        <h4><?php echo count($dashboard_data['rank_info']['achievements'] ?? []); ?></h4>
                        <p class="text-muted mb-0">Achievements</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Next Level Progress</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php $progress = $dashboard_data['rank_info']['next_level_progress'] ?? []; ?>
                        <div class="progress-circle mx-auto mb-3">
                            <div style="font-size: 1.5em; font-weight: bold;">
                                <?php echo $progress['percentage'] ?? 0; ?>%
                            </div>
                        </div>
                        <h6>Progress to <?php echo $progress['next_level_name'] ?? 'Next Level'; ?></h6>
                        <p class="text-muted">
                            <?php echo $progress['current_downline'] ?? 0; ?> / <?php echo $progress['required_downline'] ?? 0; ?> members
                        </p>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?php echo $progress['percentage'] ?? 0; ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="commission-card card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                        <h3>₹<?php echo number_format($dashboard_data['rank_info']['current_month_earnings'] ?? 0); ?></h3>
                        <p class="mb-0">This Month's Earnings</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Downline Preview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Your Downline (First 3 Levels)</h5>
                        <a href="<?php echo BASE_URL; ?>associate/downline" class="btn btn-outline-primary btn-sm">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dashboard_data['downline_preview'])): ?>
                            <div class="row">
                                <?php foreach ($dashboard_data['downline_preview'] as $level => $members): ?>
                                    <div class="col-md-4">
                                        <h6>Level <?php echo $level; ?> (<?php echo count($members); ?> members)</h6>
                                        <div class="list-group list-group-flush">
                                            <?php foreach (array_slice($members, 0, 3) as $member): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($member['name'] ?? 'Unknown'); ?></strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($member['city'] ?? ''); ?>
                                                            <?php if (!empty($member['city']) && !empty($member['state'])) echo ', '; ?>
                                                            <?php echo htmlspecialchars($member['state'] ?? ''); ?>
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-primary">Level <?php echo $member['level']; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                No downline members yet. Start building your network!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dashboard_data['recent_activities'])): ?>
                            <?php foreach ($dashboard_data['recent_activities'] as $activity): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-light rounded-circle p-2">
                                            <i class="fas fa-user-plus text-success"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($activity['member_name'] ?? 'New Member'); ?> joined</h6>
                                        <small class="text-muted">Level <?php echo $activity['level']; ?> • <?php echo date('M d, Y', strtotime($activity['activity_date'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">No recent activities</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Achievements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($dashboard_data['rank_info']['achievements'])): ?>
                            <?php foreach ($dashboard_data['rank_info']['achievements'] as $achievement): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning rounded-circle p-2">
                                            <i class="fas fa-medal text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($achievement['name']); ?></h6>
                                        <small class="text-muted">Earned on <?php echo date('M d, Y', strtotime($achievement['earned_date'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted py-3">No achievements yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>associate/genealogy" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-project-diagram me-2"></i>View Genealogy
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>associate/downline" class="btn btn-outline-success w-100">
                                    <i class="fas fa-users me-2"></i>Manage Downline
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>associate/commissions" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-coins me-2"></i>Commission History
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo BASE_URL; ?>associate/rank" class="btn btn-outline-info w-100">
                                    <i class="fas fa-trophy me-2"></i>View Rank
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
