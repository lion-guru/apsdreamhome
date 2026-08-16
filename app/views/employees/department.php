<?php
/**
 * Employee Department Page — shared view for 16 department routes
 * Data: $dept_title, $dept_icon, $dept_desc, $dept_color, $dept_slug, $employee_name, $stats
 */
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$stats = $stats ?? ['total' => 0, 'active' => 0, 'pending' => 0, 'completed' => 0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dept_title ?? '') ?> — APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f0f2f5; }
        .dept-hero {
            background: linear-gradient(135deg, <?= $dept_color ?>22 0%, <?= $dept_color ?>11 100%);
            border-bottom: 1px solid <?= $dept_color ?>30;
            padding: 32px 0 28px;
        }
        .dept-icon-wrap {
            width: 64px; height: 64px; border-radius: 18px;
            background: <?= $dept_color ?>18; color: <?= $dept_color ?>;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; flex-shrink: 0;
        }
        .stat-card {
            background: #fff; border-radius: 14px; border: 1px solid #e9ecef;
            padding: 20px; transition: .2s; height: 100%;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); transform: translateY(-2px); }
        .stat-card .stat-icon {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .stat-card .stat-value { font-size: 26px; font-weight: 700; color: #1e293b; }
        .stat-card .stat-label { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
        .section-card {
            background: #fff; border-radius: 14px; border: 1px solid #e9ecef;
            padding: 24px; margin-bottom: 20px;
        }
        .section-card h6 { font-weight: 600; color: #1e293b; margin-bottom: 16px; }
        .quick-action {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            background: #f8fafc; border-radius: 10px; border: 1px solid #e9ecef;
            cursor: pointer; transition: .2s; text-decoration: none; color: inherit;
        }
        .quick-action:hover { background: #f0f4ff; border-color: #c7d2fe; color: inherit; text-decoration: none; }
        .quick-action i { font-size: 18px; width: 20px; text-align: center; }
        .empty-state {
            text-align: center; padding: 48px 20px; color: #94a3b8;
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: <?= $dept_color ?>40; }
        .empty-state h5 { color: #475569; font-weight: 600; margin-bottom: 8px; }
    </style>
</head>
<body>

<!-- Hero -->
<div class="dept-hero">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-4">
            <div class="dept-icon-wrap">
                <i class="<?= $dept_icon ?>"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0" class="style-10134"><?= htmlspecialchars($dept_title ?? '') ?></h3>
                <p class="mb-0 mt-1" class="style-62698"><?= htmlspecialchars($dept_desc ?? '') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4 py-4">

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Total Items</div>
                        <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon" class="style-64797">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Active</div>
                        <div class="stat-value"><?= number_format($stats['active'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon" class="style-48798">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value"><?= number_format($stats['pending'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon" class="style-45343">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-value"><?= number_format($stats['completed'] ?? 0) ?></div>
                    </div>
                    <div class="stat-icon" class="style-29521">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="section-card">
                <h6><i class="fas fa-list me-2" class="style-94187"></i>Recent Activity</h6>
                <div class="empty-state">
                    <i class="<?= $dept_icon ?>"></i>
                    <h5><?= htmlspecialchars($dept_title ?? '') ?></h5>
                    <p>Use the quick actions on the right to navigate to the relevant admin modules for this department.</p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="section-card mb-3">
                <h6><i class="fas fa-bolt me-2" class="style-94187"></i>Quick Actions</h6>
                <div class="d-flex flex-column gap-2">
                    <?php
                    $quickActions = match($dept_slug) {
                        'leads' => [
                            ['icon' => 'fas fa-user-plus', 'color' => '#06b6d4', 'label' => 'All Leads', 'url' => '/admin/leads'],
                            ['icon' => 'fas fa-columns', 'color' => '#8b5cf6', 'label' => 'Lead Kanban', 'url' => '/admin/lead-kanban'],
                            ['icon' => 'fas fa-chart-line', 'color' => '#10b981', 'label' => 'Lead Scoring', 'url' => '/admin/leads/scoring'],
                            ['icon' => 'fas fa-phone', 'color' => '#f59e0b', 'label' => 'Telecalling', 'url' => '/admin/leads'],
                        ],
                        'deals' => [
                            ['icon' => 'fas fa-handshake', 'color' => '#10b981', 'label' => 'Deals Pipeline', 'url' => '/admin/deals'],
                            ['icon' => 'fas fa-coins', 'color' => '#f59e0b', 'label' => 'Payments', 'url' => '/admin/payments'],
                            ['icon' => 'fas fa-file-invoice-dollar', 'color' => '#3b82f6', 'label' => 'Invoices', 'url' => '/admin/invoices'],
                        ],
                        'employees' => [
                            ['icon' => 'fas fa-users', 'color' => '#f59e0b', 'label' => 'HR Management', 'url' => '/admin/hrm/employees'],
                            ['icon' => 'fas fa-user-tag', 'color' => '#8b5cf6', 'label' => 'Roles', 'url' => '/admin/roles'],
                            ['icon' => 'fas fa-calendar-check', 'color' => '#10b981', 'label' => 'Attendance', 'url' => '/employee/attendance'],
                        ],
                        'campaigns' => [
                            ['icon' => 'fas fa-bullhorn', 'color' => '#a855f7', 'label' => 'Campaigns', 'url' => '/admin/campaigns'],
                            ['icon' => 'fas fa-newspaper', 'color' => '#3b82f6', 'label' => 'Blog', 'url' => '/admin/blog'],
                            ['icon' => 'fas fa-ad', 'color' => '#f59e0b', 'label' => 'Ad Manager', 'url' => '/admin/ads'],
                        ],
                        'complaints' => [
                            ['icon' => 'fas fa-headset', 'color' => '#ef4444', 'label' => 'Support Tickets', 'url' => '/admin/support_tickets'],
                            ['icon' => 'fas fa-star', 'color' => '#f59e0b', 'label' => 'Testimonials', 'url' => '/admin/testimonials'],
                        ],
                        'compliance' => [
                            ['icon' => 'fas fa-shield-alt', 'color' => '#14b8a6', 'label' => 'KYC Management', 'url' => '/admin/kyc'],
                            ['icon' => 'fas fa-gavel', 'color' => '#8b5cf6', 'label' => 'Legal Documents', 'url' => '/admin/legal'],
                            ['icon' => 'fas fa-clipboard-check', 'color' => '#3b82f6', 'label' => 'Compliance', 'url' => '/admin/compliance'],
                        ],
                        'reports' => [
                            ['icon' => 'fas fa-chart-bar', 'color' => '#3b82f6', 'label' => 'Analytics', 'url' => '/admin/analytics'],
                            ['icon' => 'fas fa-file-alt', 'color' => '#10b981', 'label' => 'Reports', 'url' => '/admin/reports'],
                            ['icon' => 'fas fa-download', 'color' => '#f59e0b', 'label' => 'Export Data', 'url' => '/admin/analytics'],
                        ],
                        'vendors' => [
                            ['icon' => 'fas fa-truck', 'color' => '#0ea5e9', 'label' => 'Vendors', 'url' => '/admin/vendors'],
                            ['icon' => 'fas fa-receipt', 'color' => '#f59e0b', 'label' => 'Expenses', 'url' => '/admin/expenses'],
                            ['icon' => 'fas fa-file-invoice', 'color' => '#8b5cf6', 'label' => 'Accounting', 'url' => '/admin/accounting'],
                        ],
                        'projects' => [
                            ['icon' => 'fas fa-project-diagram', 'color' => '#ec4899', 'label' => 'Projects', 'url' => '/admin/projects'],
                            ['icon' => 'fas fa-map', 'color' => '#10b981', 'label' => 'Colonies', 'url' => '/admin/plots'],
                            ['icon' => 'fas fa-hard-hat', 'color' => '#f59e0b', 'label' => 'Construction', 'url' => '/admin/dashboard/operations'],
                        ],
                        'recruitment' => [
                            ['icon' => 'fas fa-briefcase', 'color' => '#ef4444', 'label' => 'Careers', 'url' => '/admin/careers'],
                            ['icon' => 'fas fa-users', 'color' => '#f59e0b', 'label' => 'Employees', 'url' => '/admin/hrm/employees'],
                        ],
                        default => [
                            ['icon' => 'fas fa-tachometer-alt', 'color' => '#6366f1', 'label' => 'Dashboard', 'url' => '/employee/dashboard'],
                            ['icon' => 'fas fa-tasks', 'color' => '#3b82f6', 'label' => 'My Tasks', 'url' => '/employee/tasks'],
                        ],
                    };
                    foreach ($quickActions as $action): ?>
                        <a href="<?= $base ?><?= $action['url'] ?>" class="quick-action">
                            <i class="<?= $action['icon'] ?>" class="style-83279"></i>
                            <span><?= $action['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= $base ?>/employee/dashboard" class="quick-action">
                        <i class="fas fa-arrow-left" class="style-81715"></i>
                        <span>Back to Dashboard</span>
                    </a>
                    <a href="<?= $base ?>/employee/attendance" class="quick-action">
                        <i class="fas fa-calendar-check" class="style-19115"></i>
                        <span>Attendance</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
