<?php
$page_title = $page_title ?? 'CRM Dashboard';
$stats = $stats ?? [];
$recent_activity = $recent_activity ?? [];
$pending_tasks = $pending_tasks ?? [];
$upcoming_visits = $upcoming_visits ?? [];
$byStatus = $stats['by_status'] ?? [];
$bySource = $stats['by_source'] ?? [];

$statusLabels = [
    'new' => ['label' => 'New', 'color' => 'primary', 'icon' => 'fa-star'],
    'contacted' => ['label' => 'Contacted', 'color' => 'info', 'icon' => 'fa-phone'],
    'qualified' => ['label' => 'Qualified', 'color' => 'warning', 'icon' => 'fa-check-circle'],
    'site_visit' => ['label' => 'Site Visit', 'color' => 'purple', 'icon' => 'fa-map-marker-alt'],
    'proposal' => ['label' => 'Proposal', 'color' => 'pink', 'icon' => 'fa-file-alt'],
    'negotiation' => ['label' => 'Negotiation', 'color' => 'orange', 'icon' => 'fa-handshake'],
    'booking' => ['label' => 'Booking', 'color' => 'cyan', 'icon' => 'fa-calendar-check'],
    'won' => ['label' => 'Won', 'color' => 'success', 'icon' => 'fa-trophy'],
    'lost' => ['label' => 'Lost', 'color' => 'secondary', 'icon' => 'fa-times-circle'],
    'nurture' => ['label' => 'Nurture', 'color' => 'teal', 'icon' => 'fa-seedling'],
    'closed_won' => ['label' => 'Won', 'color' => 'success', 'icon' => 'fa-trophy'],
    'closed_lost' => ['label' => 'Lost', 'color' => 'secondary', 'icon' => 'fa-times-circle'],
];
$today = date('Y-m-d');
?>

<style>
    .stat-card { border-radius: 14px; padding: 20px; border: none; transition: transform 0.2s, box-shadow 0.2s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .stat-card .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    .stat-card .stat-value { font-size: 1.8rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
    .stat-card .stat-label { font-size: 0.8rem; color: #64748b; font-weight: 500; }
    .pipeline-mini { display: flex; gap: 6px; flex-wrap: wrap; }
    .pipeline-mini .pm-item { padding: 8px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; text-align: center; min-width: 80px; }
    .pipeline-mini .pm-item .pm-count { font-size: 1.2rem; display: block; margin-top: 2px; }
    .task-item { padding: 12px 16px; border-radius: 10px; margin-bottom: 8px; background: #f8fafc; border-left: 4px solid #6366f1; }
    .task-item.overdue { border-left-color: #ef4444; background: #fef2f2; }
    .task-item .task-title { font-weight: 600; font-size: 0.9rem; color: #1e293b; }
    .task-item .task-meta { font-size: 0.78rem; color: #64748b; }
    .activity-item { padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .activity-item:last-child { border-bottom: none; }
    .section-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; }
    .section-card h6 { font-weight: 700; color: #1e293b; margin-bottom: 14px; }
    .sv-mini { padding: 10px 14px; border-radius: 10px; margin-bottom: 8px; border: 1px solid #f1f5f9; background: #fff; }
    .sv-mini.is-today { border-left: 4px solid #f59e0b; background: #fffbeb; }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-tachometer-alt text-primary me-2"></i>CRM Dashboard</h4>
            <small class="text-muted">Welcome back — here's your pipeline at a glance</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-map-marker-alt me-1"></i> Site Visits
            </a>
            <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-primary btn-sm">
                <i class="fas fa-list me-1"></i> All Leads
            </a>
            <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> Add Lead
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-bullseye"></i></div>
                    <div>
                        <div class="stat-value"><?= number_format($stats['total_leads'] ?? 0) ?></div>
                        <div class="stat-label">Total Leads</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-fire"></i></div>
                    <div>
                        <div class="stat-value"><?= number_format($stats['hot_leads'] ?? 0) ?></div>
                        <div class="stat-label">Hot Leads</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-trophy"></i></div>
                    <div>
                        <div class="stat-value"><?= number_format($stats['converted'] ?? 0) ?></div>
                        <div class="stat-label">Converted</div>
                        <small class="text-muted"><?= ($stats['conversion_rate'] ?? 0) ?>% rate</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="stat-value"><?= number_format($stats['pending_tasks'] ?? 0) ?></div>
                        <div class="stat-label">Pending Tasks</div>
                        <?php if (($stats['overdue_tasks'] ?? 0) > 0): ?>
                            <small class="text-danger fw-bold"><?= $stats['overdue_tasks'] ?> overdue</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Site Visit Stats -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="<?= BASE_URL ?>/associate/site-visits" class="card stat-card shadow-sm text-decoration-none" style="border: 2px solid #f59e0b;">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#fef3c7;color:#b45309;"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="stat-value" style="color:#b45309;"><?= number_format($stats['total_visits'] ?? 0) ?></div>
                        <div class="stat-label">Total Site Visits</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=today" class="card stat-card shadow-sm text-decoration-none" style="border: 2px solid #14b8a6;">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#ede9fe;color:#0f766e;"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="stat-value" style="color:#0f766e;"><?= number_format($stats['today_visits'] ?? 0) ?></div>
                        <div class="stat-label">Today's Visits</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=upcoming" class="card stat-card shadow-sm text-decoration-none" style="border: 2px solid #06b6d4;">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="fas fa-calendar-alt"></i></div>
                    <div>
                        <div class="stat-value" style="color:#0891b2;"><?= number_format($stats['upcoming_visits'] ?? 0) ?></div>
                        <div class="stat-label">Upcoming Visits</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Pipeline Mini View -->
    <div class="card section-card mb-4">
        <h6><i class="fas fa-filter text-primary me-2"></i>Pipeline by Status</h6>
        <div class="pipeline-mini">
            <?php foreach ($statusLabels as $key => $s):
                $count = $byStatus[$key] ?? 0;
                if ($count === 0 && !in_array($key, ['new','contacted','qualified','closed_won'])) continue;
            ?>
            <a href="<?= BASE_URL ?>/associate/leads?status=<?= $key ?>" class="pm-item text-decoration-none" style="background: rgba(var(--bs-<?= $s['color'] ?>-rgb), 0.1); color: var(--bs-<?= $s['color'] ?>);">
                <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                <span class="pm-count"><?= $count ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Tasks -->
        <div class="col-lg-5">
            <div class="card section-card h-100">
                <h6><i class="fas fa-tasks text-warning me-2"></i>Upcoming Tasks</h6>
                <?php if (empty($pending_tasks)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i>No pending tasks</p>
                <?php else: ?>
                    <?php foreach ($pending_tasks as $task):
                        $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < time();
                    ?>
                    <div class="task-item <?= $isOverdue ? 'overdue' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="task-title"><?= htmlspecialchars($task['title']) ?></div>
                                <div class="task-meta">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($task['lead_name'] ?? 'Unknown') ?>
                                    <?php if ($task['due_date']): ?>
                                        &nbsp;&bull;&nbsp;<i class="fas fa-calendar me-1"></i><?= date('M d', strtotime($task['due_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="badge bg-<?= $isOverdue ? 'danger' : ($task['priority'] === 'high' ? 'warning' : 'info') ?>"><?= ucfirst($task['priority'] ?? 'medium') ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Site Visits -->
        <div class="col-lg-4">
            <div class="card section-card h-100">
                <h6 class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-map-marker-alt text-warning me-2"></i>Upcoming Visits</span>
                    <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-sm btn-outline-warning">View All</a>
                </h6>
                <?php if (empty($upcoming_visits)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-calendar-check fa-2x d-block mb-2" style="opacity:0.2"></i>No upcoming visits</p>
                <?php else: ?>
                    <?php foreach ($upcoming_visits as $v):
                        $isTodayVisit = ($v['visit_date'] === $today);
                    ?>
                    <div class="sv-mini <?= $isTodayVisit ? 'is-today' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div style="font-weight:700;font-size:0.85rem;">
                                    <?= htmlspecialchars($v['visitor_name']) ?>
                                    <?php if ($isTodayVisit): ?><span class="badge bg-warning text-dark ms-1">Today</span><?php endif; ?>
                                </div>
                                <div style="font-size:0.78rem;color:#64748b;">
                                    <i class="fas fa-calendar me-1"></i><?= date('D, d M', strtotime($v['visit_date'])) ?>
                                    &bull; <i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($v['visit_time'])) ?>
                                </div>
                                <?php if (!empty($v['lead_name'])): ?>
                                    <div style="font-size:0.75rem;color:#94a3b8;"><i class="fas fa-user me-1"></i><?= htmlspecialchars($v['lead_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            <a href="tel:<?= htmlspecialchars($v['visitor_phone']) ?>" class="btn btn-sm btn-outline-success" style="padding:2px 8px;"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-3">
            <div class="card section-card h-100">
                <h6><i class="fas fa-history text-info me-2"></i>Recent Activity</h6>
                <?php if (empty($recent_activity)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:0.2"></i>No activity</p>
                <?php else: ?>
                    <?php foreach (array_slice($recent_activity, 0, 6) as $act): ?>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong style="font-size: 0.8rem;">
                                    <i class="fas fa-<?= $act['interaction_type'] === 'call' ? 'phone' : ($act['interaction_type'] === 'email' ? 'envelope' : ($act['interaction_type'] === 'meeting' ? 'users' : 'comment')) ?> text-muted me-1"></i>
                                    <?= ucfirst(str_replace('_', ' ', $act['interaction_type'] ?? 'note')) ?>
                                </strong>
                                <span class="text-muted ms-1" style="font-size:0.75rem;">— <?= htmlspecialchars($act['lead_name'] ?? '') ?></span>
                            </div>
                            <small class="text-muted" style="font-size:0.7rem;"><?= date('M d, g:i A', strtotime($act['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Source Breakdown -->
    <?php if (!empty($bySource)): ?>
    <div class="card section-card mt-4">
        <h6><i class="fas fa-chart-pie text-purple me-2"></i>Leads by Source</h6>
        <div class="d-flex gap-3 flex-wrap">
            <?php foreach ($bySource as $src):
                $total = array_sum(array_column($bySource, 'cnt'));
                $pct = $total > 0 ? round(($src['cnt'] / $total) * 100) : 0;
            ?>
            <div class="text-center">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #0d9488, #0f766e); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.85rem;">
                    <?= $src['cnt'] ?>
                </div>
                <small class="text-muted d-block mt-1"><?= ucfirst(str_replace('_', ' ', $src['source'] ?? 'unknown')) ?></small>
                <small class="text-muted"><?= $pct ?>%</small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
