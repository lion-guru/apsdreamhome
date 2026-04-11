<?php

/**
 * Associate Dashboard View
 * Content only - layout handled by controller
 */

// Set page variables for layout
$page_title = $page_title ?? 'Associate Dashboard';
$current_page = 'dashboard';

// Dashboard data
$stats = $stats ?? [
    'total_leads' => 24,
    'active_leads' => 8,
    'properties_sold' => 12,
    'total_commission' => 125000,
    'pending_commission' => 45000,
    'network_size' => 156,
    'conversion_rate' => 68,
    'monthly_growth' => 15
];

$recent_leads = $recent_leads ?? [
    ['name' => 'Rajesh Kumar', 'phone' => '98765xxxxx', 'type' => 'Residential Plot', 'status' => 'hot', 'date' => '2026-04-10'],
    ['name' => 'Priya Sharma', 'phone' => '98765xxxxx', 'type' => 'Commercial Shop', 'status' => 'warm', 'date' => '2026-04-09'],
    ['name' => 'Amit Singh', 'phone' => '98765xxxxx', 'type' => 'Apartment', 'status' => 'cold', 'date' => '2026-04-08'],
];

$recent_commissions = $recent_commissions ?? [
    ['property' => 'Suryoday Heights - Plot 45', 'amount' => 25000, 'status' => 'paid', 'date' => '2026-04-05'],
    ['property' => 'Raghunath City - Shop 12', 'amount' => 18000, 'status' => 'pending', 'date' => '2026-04-03'],
    ['property' => 'Braj Radha Enclave - House 8', 'amount' => 32000, 'status' => 'paid', 'date' => '2026-03-28'],
];

$activities = $activities ?? [
    ['icon' => 'fa-user-plus', 'text' => 'New lead registered: Rajesh Kumar', 'time' => '2 hours ago', 'color' => 'blue'],
    ['icon' => 'fa-money-bill-wave', 'text' => 'Commission credited: ₹25,000', 'time' => '5 hours ago', 'color' => 'green'],
    ['icon' => 'fa-sitemap', 'text' => 'New associate joined your network', 'time' => '1 day ago', 'color' => 'purple'],
    ['icon' => 'fa-building', 'text' => 'Property viewed: Suryoday Heights', 'time' => '2 days ago', 'color' => 'orange'],
];
?>

<!-- Quick Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/associate/leads" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_leads']; ?></div>
                <div class="stat-label">Total Leads</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i> 12% this month
                </div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> View All</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/associate/properties" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon green">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-value"><?php echo $stats['properties_sold']; ?></div>
                <div class="stat-label">Properties Sold</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i> 8% this month
                </div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> View All</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/associate/commissions" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon orange">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_commission']); ?></div>
                <div class="stat-label">Total Commission</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i> 15% this month
                </div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> View Details</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="stat-card-link">
            <div class="stat-card clickable">
                <div class="stat-icon purple">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="stat-value"><?php echo $stats['network_size']; ?></div>
                <div class="stat-label">Network Size</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i> 5 new this week
                </div>
                <div class="click-hint"><i class="fas fa-external-link-alt"></i> View Network</div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Performance Chart Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Performance Overview</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active">This Month</button>
                        <button class="btn btn-outline-secondary">Last Month</button>
                        <button class="btn btn-outline-secondary">This Year</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <h4 class="text-primary mb-1"><?php echo $stats['conversion_rate']; ?>%</h4>
                        <small class="text-muted">Conversion Rate</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success mb-1">₹<?php echo number_format($stats['pending_commission']); ?></h4>
                        <small class="text-muted">Pending Commission</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning mb-1"><?php echo $stats['active_leads']; ?></h4>
                        <small class="text-muted">Active Leads</small>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info mb-1"><?php echo $stats['monthly_growth']; ?>%</h4>
                        <small class="text-muted">Monthly Growth</small>
                    </div>
                </div>
                <!-- Chart Placeholder -->
                <div style="height: 250px; background: linear-gradient(90deg, #f8fafc 0%, #e2e8f0 50%, #f8fafc 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-muted">
                        <i class="fas fa-chart-area fa-3x mb-3"></i>
                        <p>Performance Chart Will Load Here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Leads -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-users text-success me-2"></i>Recent Leads</h5>
                    <a href="<?php echo BASE_URL; ?>/associate/leads" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Interest</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_leads as $lead): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lead['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($lead['type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $lead['status'] === 'hot' ? 'danger' : ($lead['status'] === 'warm' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($lead['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d', strtotime($lead['date'])); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Commissions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-money-bill-wave text-warning me-2"></i>Recent Commissions</h5>
                    <a href="<?php echo BASE_URL; ?>/associate/commissions" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_commissions as $commission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($commission['property']); ?></td>
                                    <td><strong>₹<?php echo number_format($commission['amount']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $commission['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($commission['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($commission['date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/associate/leads/add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Lead
                    </a>
                    <a href="<?php echo BASE_URL; ?>/associate/genealogy" class="btn btn-outline-primary">
                        <i class="fas fa-sitemap me-2"></i>View Network
                    </a>
                    <a href="<?php echo BASE_URL; ?>/associate/wallet/withdraw" class="btn btn-outline-success">
                        <i class="fas fa-wallet me-2"></i>Withdraw Commission
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-clock text-info me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <?php foreach ($activities as $activity): ?>
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-<?php echo $activity['color']; ?> bg-opacity-10 text-<?php echo $activity['color']; ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas <?php echo $activity['icon']; ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1"><?php echo $activity['text']; ?></p>
                                <small class="text-muted"><?php echo $activity['time']; ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Network Summary -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0"><i class="fas fa-network-wired text-purple me-2"></i>Network Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Direct Referrals</span>
                    <strong>24</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Level 2</span>
                    <strong>68</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Level 3</span>
                    <strong>64</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Network</span>
                    <strong class="text-primary">156</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        height: 100%;
        transition: all 0.3s ease;
    }

    .stat-card.clickable:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }

    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-card-link:hover {
        color: inherit;
    }

    .click-hint {
        font-size: 0.75rem;
        color: #3b82f6;
        margin-top: 10px;
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat-card.clickable:hover .click-hint {
        opacity: 1;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-icon.blue {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .stat-icon.green {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-icon.orange {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .stat-icon.purple {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
    }

    .stat-trend {
        font-size: 0.8rem;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .stat-trend.up {
        color: #10b981;
    }

    .activity-list .border-bottom:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>