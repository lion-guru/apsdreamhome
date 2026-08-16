<?php
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$role = $role ?? 'admin';
$userName = $userName ?? ($_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'User');
$stats = $stats ?? [];
$recentItems = $recentItems ?? [];

$roleLabels = [
    'super_admin' => 'Super Admin', 'admin' => 'Admin', 'manager' => 'Manager',
    'employee' => 'Employee', 'associate' => 'Associate', 'agent' => 'Agent',
    'customer' => 'Customer', 'team_lead' => 'Team Lead', 'telecalling_lead' => 'Telecalling Lead',
    'sales_team_lead' => 'Sales Team Lead', 'support_lead' => 'Support Lead',
    'senior_accountant' => 'Senior Accountant', 'senior_developer' => 'Senior Developer',
    'legal_advisor' => 'Legal Advisor', 'chartered_accountant' => 'Chartered Accountant',
    'accountant' => 'Accountant', 'developer' => 'Developer', 'content_writer' => 'Content Writer',
    'graphic_designer' => 'Graphic Designer', 'data_entry_operator' => 'Data Entry Operator',
    'backoffice_staff' => 'Backoffice Staff', 'telecalling_executive' => 'Telecalling Executive',
    'support_executive' => 'Support Executive', 'senior_associate' => 'Senior Associate',
    'associate_team_lead' => 'Associate Team Lead', 'senior_agent' => 'Senior Agent',
    'franchise_owner' => 'Franchise Owner',
];
$roleLabel = $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role));

$statsCards = [
    ['key' => 'total_leads', 'label' => 'Total Leads', 'icon' => 'fas fa-user-plus', 'color' => '#3b82f6'],
    ['key' => 'new_leads_today', 'label' => 'Leads Today', 'icon' => 'fas fa-clock', 'color' => '#10b981'],
    ['key' => 'active_properties', 'label' => 'Active Properties', 'icon' => 'fas fa-home', 'color' => '#8b5cf6'],
    ['key' => 'total_bookings', 'label' => 'Total Bookings', 'icon' => 'fas fa-file-contract', 'color' => '#f59e0b'],
    ['key' => 'total_revenue', 'label' => 'Total Revenue', 'icon' => 'fas fa-rupee-sign', 'color' => '#10b981'],
    ['key' => 'monthly_revenue', 'label' => 'Monthly Revenue', 'icon' => 'fas fa-chart-line', 'color' => '#0ea5e9'],
    ['key' => 'total_associates', 'label' => 'Associates', 'icon' => 'fas fa-users', 'color' => '#a855f7'],
    ['key' => 'total_employees', 'label' => 'Employees', 'icon' => 'fas fa-user-tie', 'color' => '#f97316'],
    ['key' => 'open_tickets', 'label' => 'Open Tickets', 'icon' => 'fas fa-headset', 'color' => '#ef4444'],
    ['key' => 'available_plots', 'label' => 'Available Plots', 'icon' => 'fas fa-map', 'color' => '#06b6d4'],
    ['key' => 'booked_plots', 'label' => 'Booked Plots', 'icon' => 'fas fa-check-circle', 'color' => '#10b981'],
    ['key' => 'conversion_rate', 'label' => 'Conversion Rate', 'icon' => 'fas fa-percentage', 'color' => '#ec4899'],
];

// Role-specific extra stats
if ($role === 'associate') {
    $statsCards = [
        ['key' => 'team_size', 'label' => 'My Team', 'icon' => 'fas fa-users', 'color' => '#3b82f6'],
        ['key' => 'total_commission', 'label' => 'Commission', 'icon' => 'fas fa-rupee-sign', 'color' => '#10b981'],
        ['key' => 'rank', 'label' => 'Rank', 'icon' => 'fas fa-trophy', 'color' => '#f59e0b'],
        ['key' => 'new_leads_today', 'label' => 'Leads Today', 'icon' => 'fas fa-clock', 'color' => '#8b5cf6'],
    ];
} elseif ($role === 'agent') {
    $statsCards = [
        ['key' => 'my_leads', 'label' => 'My Leads', 'icon' => 'fas fa-user-plus', 'color' => '#3b82f6'],
        ['key' => 'conversions', 'label' => 'Conversions', 'icon' => 'fas fa-check-circle', 'color' => '#10b981'],
        ['key' => 'earnings', 'label' => 'Earnings', 'icon' => 'fas fa-rupee-sign', 'color' => '#f59e0b'],
        ['key' => 'conversion_rate', 'label' => 'Conv. Rate', 'icon' => 'fas fa-percentage', 'color' => '#ec4899'],
    ];
} elseif (in_array($role, ['employee', 'telecaller', 'backoffice_staff'])) {
    $statsCards = [
        ['key' => 'my_tasks', 'label' => 'My Tasks', 'icon' => 'fas fa-tasks', 'color' => '#3b82f6'],
        ['key' => 'pending_tasks', 'label' => 'Pending', 'icon' => 'fas fa-clock', 'color' => '#f59e0b'],
        ['key' => 'completed_tasks', 'label' => 'Completed', 'icon' => 'fas fa-check-circle', 'color' => '#10b981'],
        ['key' => 'open_tickets', 'label' => 'Open Tickets', 'icon' => 'fas fa-headset', 'color' => '#ef4444'],
    ];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 fw-bold"><?= $roleLabel ?> Dashboard</h1>
        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($userName) ?>!</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/ai/executive-assistant" class="btn btn-sm btn-info text-white">
            <i class="fas fa-robot me-1"></i>Ask AI
        </a>
        <button class="btn btn-outline-secondary" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($statsCards as $card): ?>
        <?php $value = $stats[$card['key']] ?? 0; ?>
        <?php if ($value !== 0 || in_array($card['key'], ['total_leads', 'total_revenue', 'monthly_revenue'])): ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle p-3" class="style-16944">
                                <i class="<?= $card['icon'] ?> fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small"><?= $card['label'] ?></div>
                            <div class="fw-bold fs-5">
                                <?= is_numeric($value) ? number_format($value) : htmlspecialchars($value ?? '') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Quick Actions + Recent Activity -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php
                    $quickActions = match(true) {
                        $role === 'associate' => [
                            ['icon' => 'fas fa-user-plus', 'color' => '#3b82f6', 'label' => 'My Team', 'url' => '/associate/team'],
                            ['icon' => 'fas fa-list', 'color' => '#8b5cf6', 'label' => 'My Leads', 'url' => '/associate/leads'],
                            ['icon' => 'fas fa-rupee-sign', 'color' => '#10b981', 'label' => 'Commissions', 'url' => '/associate/commissions'],
                            ['icon' => 'fas fa-share-alt', 'color' => '#f59e0b', 'label' => 'Share & Earn', 'url' => '/associate/share'],
                        ],
                        $role === 'agent' => [
                            ['icon' => 'fas fa-user-plus', 'color' => '#3b82f6', 'label' => 'My Leads', 'url' => '/agent/leads'],
                            ['icon' => 'fas fa-phone', 'color' => '#10b981', 'label' => 'Follow-ups', 'url' => '/agent/followups'],
                            ['icon' => 'fas fa-home', 'color' => '#8b5cf6', 'label' => 'Properties', 'url' => '/agent/properties'],
                            ['icon' => 'fas fa-rupee-sign', 'color' => '#f59e0b', 'label' => 'Earnings', 'url' => '/agent/earnings'],
                        ],
                        in_array($role, ['employee', 'telecaller', 'backoffice_staff']) => [
                            ['icon' => 'fas fa-tasks', 'color' => '#3b82f6', 'label' => 'My Tasks', 'url' => '/employee/tasks'],
                            ['icon' => 'fas fa-calendar-check', 'color' => '#10b981', 'label' => 'Attendance', 'url' => '/employee/attendance'],
                            ['icon' => 'fas fa-calendar-times', 'color' => '#f59e0b', 'label' => 'Leaves', 'url' => '/employee/leaves'],
                            ['icon' => 'fas fa-user-circle', 'color' => '#8b5cf6', 'label' => 'Profile', 'url' => '/employee/profile'],
                        ],
                        in_array($role, ['senior_accountant', 'chartered_accountant', 'accountant', 'finance_manager']) => [
                            ['icon' => 'fas fa-calculator', 'color' => '#3b82f6', 'label' => 'Accounting', 'url' => '/admin/accounting'],
                            ['icon' => 'fas fa-money-bill', 'color' => '#10b981', 'label' => 'Payments', 'url' => '/admin/payments'],
                            ['icon' => 'fas fa-file-invoice-dollar', 'color' => '#f59e0b', 'label' => 'Invoices', 'url' => '/admin/invoices'],
                            ['icon' => 'fas fa-receipt', 'color' => '#8b5cf6', 'label' => 'Expenses', 'url' => '/admin/expenses'],
                        ],
                        in_array($role, ['senior_developer', 'developer', 'it_manager']) => [
                            ['icon' => 'fas fa-laptop-code', 'color' => '#3b82f6', 'label' => 'IT Dashboard', 'url' => '/admin/dashboard/it'],
                            ['icon' => 'fas fa-server', 'color' => '#10b981', 'label' => 'System Health', 'url' => '/admin/system-health'],
                            ['icon' => 'fas fa-key', 'color' => '#f59e0b', 'label' => 'API Keys', 'url' => '/admin/api-keys'],
                            ['icon' => 'fas fa-shield-alt', 'color' => '#8b5cf6', 'label' => 'Security', 'url' => '/admin/security'],
                        ],
                        in_array($role, ['content_writer', 'graphic_designer', 'marketing_manager']) => [
                            ['icon' => 'fas fa-bullhorn', 'color' => '#a855f7', 'label' => 'Campaigns', 'url' => '/admin/campaigns'],
                            ['icon' => 'fas fa-blog', 'color' => '#3b82f6', 'label' => 'Blog', 'url' => '/admin/blog'],
                            ['icon' => 'fas fa-ad', 'color' => '#f59e0b', 'label' => 'Ads', 'url' => '/admin/ads'],
                            ['icon' => 'fas fa-newspaper', 'color' => '#10b981', 'label' => 'News', 'url' => '/admin/news'],
                        ],
                        $role === 'legal_advisor' => [
                            ['icon' => 'fas fa-gavel', 'color' => '#8b5cf6', 'label' => 'Legal Docs', 'url' => '/admin/legal'],
                            ['icon' => 'fas fa-shield-alt', 'color' => '#3b82f6', 'label' => 'Compliance', 'url' => '/admin/compliance'],
                            ['icon' => 'fas fa-file-contract', 'color' => '#10b981', 'label' => 'KYC', 'url' => '/admin/kyc'],
                            ['icon' => 'fas fa-balance-scale', 'color' => '#f59e0b', 'label' => 'NOC Registry', 'url' => '/admin/noc-registry'],
                        ],
                        $role === 'franchise_owner' => [
                            ['icon' => 'fas fa-store', 'color' => '#3b82f6', 'label' => 'My Franchise', 'url' => '/admin/dashboard/sales'],
                            ['icon' => 'fas fa-users', 'color' => '#10b981', 'label' => 'Team', 'url' => '/admin/users'],
                            ['icon' => 'fas fa-rupee-sign', 'color' => '#f59e0b', 'label' => 'Earnings', 'url' => '/admin/commission'],
                            ['icon' => 'fas fa-chart-bar', 'color' => '#8b5cf6', 'label' => 'Reports', 'url' => '/admin/reports'],
                        ],
                        default => [
                            ['icon' => 'fas fa-tachometer-alt', 'color' => '#3b82f6', 'label' => 'Dashboard', 'url' => '/admin/erp'],
                            ['icon' => 'fas fa-users', 'color' => '#10b981', 'label' => 'Users', 'url' => '/admin/users'],
                            ['icon' => 'fas fa-home', 'color' => '#8b5cf6', 'label' => 'Properties', 'url' => '/admin/properties'],
                            ['icon' => 'fas fa-chart-bar', 'color' => '#f59e0b', 'label' => 'Reports', 'url' => '/admin/reports'],
                        ],
                    };
                    foreach ($quickActions as $action): ?>
                        <div class="col-6">
                            <a href="<?= $base ?><?= $action['url'] ?>" class="text-decoration-none">
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 border">
                                    <i class="<?= $action['icon'] ?>" class="style-83279"></i>
                                    <span class="small fw-medium"><?= $action['label'] ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clock text-info me-2"></i>Recent Activity</h6>
            </div>
            <div class="card-body">
                <?php if (empty($recentItems)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                        <p class="mb-0">No recent activity</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentItems as $item): ?>
                        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-<?= $item['badge_color'] ?? 'info' ?> bg-opacity-10 p-2">
                                    <i class="fas fa-circle fa-sm text-<?= $item['badge_color'] ?? 'info' ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium small"><?= htmlspecialchars($item['title'] ?? '') ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($item['description'] ?? '') ?></div>
                            </div>
                            <div class="text-muted small">
                                <?= $item['created_at'] ? date('M d', strtotime($item['created_at'])) : '' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
