<?php
/**
 * APS Dream Home - Associate Dashboard
 */

$page_title = $page_title ?? 'Associate Dashboard - APS Dream Home';
$user = $user ?? [];
$recent_activities = $recent_activities ?? [];
$notifications = $notifications ?? [];
?>

<!-- Dashboard Header -->
<div class="container animate-fade-in">
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-2">Associate Dashboard</h1>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-user-circle me-2"></i>Welcome back, <?= htmlspecialchars($user['name'] ?? 'Associate') ?>!
                    <span class="mx-2">|</span> 
                    <i class="fas fa-calendar-alt me-2"></i>Member since <?= date('M Y', strtotime($user['join_date'] ?? '2024-01-01')) ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-medium shadow-sm">
                    <i class="fas fa-check-circle me-1"></i>Active Status
                </span>
                <button class="btn btn-white bg-white text-primary rounded-pill ms-2 px-4 py-2 fw-medium shadow-sm transition-hover">
                    <i class="fas fa-cog me-2"></i>Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Performance Stats -->
<div class="container mb-4">
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card glass-card bg-primary text-white border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-uppercase mb-2 small opacity-75">Team Size</h6>
                            <h3 class="mb-0 fw-bold"><?= number_format($user['performance']['total_sales'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <?php if (isset($rank_info['next_rank_info'])): ?>
                        <div class="mt-3 small opacity-75">
                            Next Target: <?= $rank_info['next_rank_info']['required_members'] ?> members
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card glass-card bg-success text-white border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-uppercase mb-2 small opacity-75">Team Revenue</h6>
                            <h3 class="mb-0 fw-bold">₹<?= number_format($user['performance']['total_revenue'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-50"></i>
                        </div>
                    </div>
                    <?php if (isset($rank_info['next_rank_info'])): ?>
                        <div class="mt-3 small opacity-75">
                            Target: ₹<?= number_format($rank_info['next_rank_info']['required_bv']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card glass-card bg-info text-white border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-uppercase mb-2 small opacity-75">Commission Paid</h6>
                            <h3 class="mb-0 fw-bold">₹<?= number_format($user['performance']['commission_earned'] ?? 0) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-wallet fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card glass-card bg-warning text-white border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-uppercase mb-2 small opacity-75">Personal Sales</h6>
                            <h3 class="mb-0 fw-bold"><?= $user['performance']['properties_sold'] ?? 0 ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rank Progress -->
<?php if (isset($rank_info)): ?>
<div class="container mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Current Rank: <span class="text-primary"><?= $rank_info['rank'] ?></span></h5>
                <span class="badge bg-light text-primary border"><?= $rank_info['performance'] ?>% Progress</span>
            </div>
            <div class="progress" style="height: 10px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                     style="width: <?= $rank_info['performance'] ?>%" 
                     aria-valuenow="<?= $rank_info['performance'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <?php if ($rank_info['next_rank_info']): ?>
                <div class="mt-2 small text-muted">
                    Need <?= $rank_info['next_rank_info']['members_needed'] ?> more members and 
                    ₹<?= number_format($rank_info['next_rank_info']['bv_needed']) ?> BV to reach 
                    <strong><?= $rank_info['next_rank_info']['next_rank'] ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="container">
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Recent Earnings</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($activity['date'])) ?></td>
                                            <td>
                                                <span class="text-capitalize"><?= $activity['subtype'] ?></span>
                                            </td>
                                            <td class="fw-bold text-success">₹<?= number_format($activity['amount']) ?></td>
                                            <td><span class="badge bg-success">Paid</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-light mb-3"></i>
                            <p class="text-muted">No recent commission records found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notifications</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($notifications)): ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item d-flex mb-3 p-2 border rounded">
                                    <div class="notification-icon me-2">
                                        <?php if ($notification['type'] === 'success'): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                        <?php elseif ($notification['type'] === 'info'): ?>
                                            <i class="fas fa-info-circle text-info"></i>
                                        <?php elseif ($notification['type'] === 'warning'): ?>
                                            <i class="fas fa-exclamation-triangle text-warning"></i>
                                        <?php else: ?>
                                            <i class="fas fa-bell text-secondary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notification-content flex-grow-1">
                                        <div class="small"><?= htmlspecialchars($notification['message']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($notification['time']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No new notifications.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Property
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>View Clients
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </button>
                        <button class="btn btn-outline-primary">
                            <i class="fas fa-calendar me-2"></i>Schedule Meeting
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: var(--glass-shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.25);
    }

    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 24px;
        padding: 2.5rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        margin-bottom: 1rem;
    }

    .bg-gradient-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .bg-gradient-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .bg-gradient-orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .bg-gradient-purple { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); }

    .card { border-radius: 20px; border: none; }
    .progress { height: 12px; border-radius: 20px; background: rgba(0,0,0,0.05); }
    .progress-bar { border-radius: 20px; }
</style>
