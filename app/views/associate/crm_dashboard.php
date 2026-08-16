<?php
$page_title = $page_title ?? __('assoc_crm_title', [], 'CRM Dashboard');
$stats = $stats ?? [];
$recent_activity = $recent_activity ?? [];
$pending_tasks = $pending_tasks ?? [];
$upcoming_visits = $upcoming_visits ?? [];
$byStatus = $stats['by_status'] ?? [];
$bySource = $stats['by_source'] ?? [];

$statusLabels = [
    'new' => ['label' => __('assoc_status_new', [], 'New'), 'color' => 'primary', 'icon' => 'fa-star'],
    'contacted' => ['label' => __('assoc_status_contacted', [], 'Contacted'), 'color' => 'info', 'icon' => 'fa-phone'],
    'qualified' => ['label' => __('assoc_status_qualified', [], 'Qualified'), 'color' => 'warning', 'icon' => 'fa-check-circle'],
    'site_visit' => ['label' => __('assoc_status_site_visit', [], 'Site Visit'), 'color' => 'info', 'icon' => 'fa-map-marker-alt'],
    'proposal' => ['label' => __('assoc_status_proposal', [], 'Proposal'), 'color' => 'danger', 'icon' => 'fa-file-alt'],
    'negotiation' => ['label' => __('assoc_status_negotiation', [], 'Negotiation'), 'color' => 'warning', 'icon' => 'fa-handshake'],
    'booking' => ['label' => __('assoc_crm_booking', [], 'Booking'), 'color' => 'info', 'icon' => 'fa-calendar-check'],
    'won' => ['label' => __('assoc_status_won', [], 'Won'), 'color' => 'success', 'icon' => 'fa-trophy'],
    'lost' => ['label' => __('assoc_status_lost', [], 'Lost'), 'color' => 'secondary', 'icon' => 'fa-times-circle'],
    'nurture' => ['label' => __('assoc_status_nurture', [], 'Nurture'), 'color' => 'success', 'icon' => 'fa-seedling'],
    'closed_won' => ['label' => __('assoc_status_won', [], 'Won'), 'color' => 'success', 'icon' => 'fa-trophy'],
    'closed_lost' => ['label' => __('assoc_status_lost', [], 'Lost'), 'color' => 'secondary', 'icon' => 'fa-times-circle'],
];
$today = date('Y-m-d');
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-tachometer-alt text-primary me-2"></i><?= __('assoc_crm_title', [], 'CRM Dashboard') ?></h4>
            <small class="text-muted"><?= __('assoc_crm_welcome', [], 'Welcome back — here\'s your pipeline at a glance') ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-map-marker-alt me-1"></i> <?= __('assoc_crm_site_visits', [], 'Site Visits') ?>
            </a>
            <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-primary btn-sm">
                <i class="fas fa-list me-1"></i> <?= __('assoc_crm_all_leads', [], 'All Leads') ?>
            </a>
            <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i> <?= __('assoc_crm_add_lead', [], 'Add Lead') ?>
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" class="style-99679">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-59043"><i class="fas fa-bullseye"></i></div>
                    <div>
                        <div class="style-58622"><?= number_format($stats['total_leads'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_total_leads', [], 'Total Leads') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" class="style-99679">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-93235"><i class="fas fa-fire"></i></div>
                    <div>
                        <div class="style-58622"><?= number_format($stats['hot_leads'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_hot_leads', [], 'Hot Leads') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" class="style-99679">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-4512"><i class="fas fa-trophy"></i></div>
                    <div>
                        <div class="style-58622"><?= number_format($stats['converted'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_converted', [], 'Converted') ?></div>
                        <div class="style-70110"><?= ($stats['conversion_rate'] ?? 0) ?>% <?= __('assoc_crm_rate', [], 'rate') ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" class="style-99679">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-35781"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="style-58622"><?= number_format($stats['pending_tasks'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_pending_tasks', [], 'Pending Tasks') ?></div>
                        <?php if (($stats['overdue_tasks'] ?? 0) > 0): ?>
                            <div class="style-5352"><?= $stats['overdue_tasks'] ?> <?= __('assoc_crm_overdue', [], 'overdue') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="<?= BASE_URL ?>/associate/site-visits" class="card border-0 shadow-sm text-decoration-none" class="style-90419">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-26894"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <div class="style-9074"><?= number_format($stats['total_visits'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_total_visits', [], 'Total Site Visits') ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=today" class="card border-0 shadow-sm text-decoration-none" class="style-40875">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-99370"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="style-69225"><?= number_format($stats['today_visits'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_today_visits', [], "Today's Visits") ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <a href="<?= BASE_URL ?>/associate/site-visits?tab=upcoming" class="card border-0 shadow-sm text-decoration-none" class="style-25028">
                <div class="d-flex align-items-center gap-3">
                    <div class="style-74581"><i class="fas fa-calendar-alt"></i></div>
                    <div>
                        <div class="style-72404"><?= number_format($stats['upcoming_visits'] ?? 0) ?></div>
                        <div class="style-99734"><?= __('assoc_crm_upcoming_visits', [], 'Upcoming Visits') ?></div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" class="style-24235">
        <h6 class="style-954"><i class="fas fa-filter text-primary me-2"></i><?= __('assoc_crm_pipeline_by_status', [], 'Pipeline by Status') ?></h6>
        <div class="style-88908">
            <?php foreach ($statusLabels as $key => $s):
                $count = $byStatus[$key] ?? 0;
                if ($count === 0 && !in_array($key, ['new','contacted','qualified','closed_won'])) continue;
            ?>
            <a href="<?= BASE_URL ?>/associate/leads?status=<?= $key ?>" class="text-decoration-none" class="style-61015">
                <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
                <span class="style-24086"><?= $count ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" class="style-63019">
                <h6 class="style-954"><i class="fas fa-tasks text-warning me-2"></i><?= __('assoc_crm_upcoming_tasks', [], 'Upcoming Tasks') ?></h6>
                <?php if (empty($pending_tasks)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i><?= __('assoc_crm_no_tasks', [], 'No pending tasks') ?></p>
                <?php else: ?>
                    <?php foreach ($pending_tasks as $task):
                        $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < time();
                    ?>
                    <div class="style-46800">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="style-30934"><?= htmlspecialchars($task['title'] ?? '') ?></div>
                                <div class="style-74061">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($task['lead_name'] ?? __('assoc_crm_unknown', [], 'Unknown')) ?>
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

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" class="style-63019">
                <h6 class="d-flex justify-content-between align-items-center" class="style-954">
                    <span><i class="fas fa-map-marker-alt text-warning me-2"></i><?= __('assoc_crm_upcoming_visits_title', [], 'Upcoming Visits') ?></span>
                    <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-sm btn-outline-warning"><?= __('assoc_crm_view_all', [], 'View All') ?></a>
                </h6>
                <?php if (empty($upcoming_visits)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-calendar-check fa-2x d-block mb-2" class="style-82835"></i><?= __('assoc_crm_no_visits', [], 'No upcoming visits') ?></p>
                <?php else: ?>
                    <?php foreach ($upcoming_visits as $v):
                        $isTodayVisit = ($v['visit_date'] === $today);
                    ?>
                    <div class="style-75803">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="style-64847">
                                    <?= htmlspecialchars($v['visitor_name'] ?? '') ?>
                                    <?php if ($isTodayVisit): ?><span class="badge bg-warning text-dark ms-1"><?= __('assoc_crm_today', [], 'Today') ?></span><?php endif; ?>
                                </div>
                                <div class="style-76464">
                                    <i class="fas fa-calendar me-1"></i><?= date('D, d M', strtotime($v['visit_date'])) ?>
                                    &bull; <i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($v['visit_time'])) ?>
                                </div>
                                <?php if (!empty($v['lead_name'])): ?>
                                    <div class="style-50608"><i class="fas fa-user me-1"></i><?= htmlspecialchars($v['lead_name'] ?? '') ?></div>
                                <?php endif; ?>
                            </div>
                            <a href="tel:<?= htmlspecialchars($v['visitor_phone'] ?? '') ?>" class="btn btn-sm btn-outline-success" class="style-52516"><i class="fas fa-phone"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card border-0 shadow-sm" class="style-63019">
                <h6 class="style-954"><i class="fas fa-history text-info me-2"></i><?= __('assoc_crm_recent_activity', [], 'Recent Activity') ?></h6>
                <?php if (empty($recent_activity)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2" class="style-82835"></i><?= __('assoc_crm_no_activity', [], 'No activity') ?></p>
                <?php else: ?>
                    <?php foreach (array_slice($recent_activity, 0, 6) as $act): ?>
                    <div class="style-12119">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong class="style-62847">
                                    <i class="fas fa-<?= $act['interaction_type'] === 'call' ? 'phone' : ($act['interaction_type'] === 'email' ? 'envelope' : ($act['interaction_type'] === 'meeting' ? 'users' : 'comment')) ?> text-muted me-1"></i>
                                    <?= ucfirst(str_replace('_', ' ', $act['interaction_type'] ?? __('assoc_crm_note', [], 'note'))) ?>
                                </strong>
                                <span class="text-muted ms-1" class="style-20558">— <?= htmlspecialchars($act['lead_name'] ?? '') ?></span>
                            </div>
                            <small class="text-muted" class="style-68658"><?= date('M d, g:i A', strtotime($act['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($bySource)): ?>
    <div class="card border-0 shadow-sm mt-4" class="style-24235">
        <h6 class="style-954"><i class="fas fa-chart-pie text-purple me-2"></i><?= __('assoc_crm_leads_by_source', [], 'Leads by Source') ?></h6>
        <div class="d-flex gap-3 flex-wrap">
            <?php foreach ($bySource as $src):
                $total = array_sum(array_column($bySource, 'cnt'));
                $pct = $total > 0 ? round(($src['cnt'] / $total) * 100) : 0;
            ?>
            <div class="text-center">
                <div class="style-94742">
                    <?= $src['cnt'] ?>
                </div>
                <small class="text-muted d-block mt-1"><?= ucfirst(str_replace('_', ' ', $src['source'] ?? __('assoc_crm_unknown', [], 'unknown'))) ?></small>
                <small class="text-muted"><?= $pct ?>%</small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
