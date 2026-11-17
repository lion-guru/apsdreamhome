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

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 text-dark fw-bold"><i class="fas fa-tachometer-alt text-teal me-2" style="color: #0d9488;"></i><?= __('assoc_crm_title', [], 'CRM Dashboard') ?></h4>
        <small class="text-muted"><?= __('assoc_crm_welcome', [], "Welcome back — here's your pipeline at a glance") ?></small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-map-marker-alt me-1 text-warning"></i> <?= __('assoc_crm_site_visits', [], 'Site Visits') ?>
        </a>
        <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="fas fa-list me-1"></i> <?= __('assoc_crm_all_leads', [], 'All Leads') ?>
        </a>
        <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold text-white shadow-sm" style="background-color: #0d9488; border-color: #0d9488;">
            <i class="fas fa-plus me-1"></i> <?= __('assoc_crm_add_lead', [], 'Add Lead') ?>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($stats['total_leads'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_total_leads', [], 'Total Leads') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-danger-subtle text-danger flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($stats['hot_leads'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_hot_leads', [], 'Hot Leads') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($stats['converted'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_converted', [], 'Converted') ?> &bull; <span class="text-success fw-semibold"><?= ($stats['conversion_rate'] ?? 0) ?>%</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format($stats['pending_tasks'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_pending_tasks', [], 'Pending Tasks') ?>
                            <?php if (($stats['overdue_tasks'] ?? 0) > 0): ?>
                                &bull; <span class="badge bg-danger rounded-pill"><?= e($stats['overdue_tasks']) ?> <?= __('assoc_crm_overdue', [], 'overdue') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <a href="<?= BASE_URL ?>/associate/site-visits" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div class="fs-5 fw-bold text-dark"><?= number_format($stats['total_visits'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_total_visits', [], 'Total Site Visits') ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-4 col-md-6">
        <a href="<?= BASE_URL ?>/associate/site-visits?tab=today" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="fs-5 fw-bold text-dark"><?= number_format($stats['today_visits'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_today_visits', [], "Today's Visits") ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-4 col-md-6">
        <a href="<?= BASE_URL ?>/associate/site-visits?tab=upcoming" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.15rem;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="fs-5 fw-bold text-dark"><?= number_format($stats['upcoming_visits'] ?? 0) ?></div>
                        <div class="text-muted small"><?= __('assoc_crm_upcoming_visits', [], 'Upcoming Visits') ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-3"><i class="fas fa-filter text-primary me-2"></i><?= __('assoc_crm_pipeline_by_status', [], 'Pipeline by Status') ?></h6>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($statusLabels as $key => $s):
                $count = $byStatus[$key] ?? 0;
                if ($count === 0 && !in_array($key, ['new','contacted','qualified','closed_won'])) continue;
            ?>
            <a href="<?= BASE_URL ?>/associate/leads?status=<?= e($key) ?>" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2 rounded-pill px-3 py-1">
                <i class="fas <?= e($s['icon']) ?>"></i>
                <span><?= e($s['label']) ?></span>
                <span class="badge bg-<?= e($s['color']) ?> rounded-pill"><?= e($count) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Pipeline Funnel (S348: funnel analytics) -->
<?php if (!empty($funnel)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-3"><i class="fas fa-funnel-dollar text-primary me-2"></i><?= __('assoc_crm_funnel_title', [], 'Conversion Funnel') ?></h6>
        <?php
        $funnelLabels = [
            'new' => ['New', 'bg-primary'],
            'contacted' => ['Contacted', 'bg-info'],
            'qualified' => ['Qualified', 'bg-warning'],
            'site_visit' => ['Site Visit', 'bg-info'],
            'proposal' => ['Proposal', 'bg-danger'],
            'negotiation' => ['Negotiation', 'bg-warning'],
            'closed_won' => ['Closed Won', 'bg-success'],
        ];
        $maxFunnel = max(array_values($funnel));
        $maxFunnel = max($maxFunnel, 1);
        ?>
        <div class="funnel-container">
            <?php foreach ($funnel as $stage => $count):
                $label = $funnelLabels[$stage] ?? [$stage, 'bg-secondary'];
                $width = max(($count / $maxFunnel) * 100, 2);
                $rate = $funnel_rates[$stage] ?? null;
            ?>
            <div class="funnel-stage mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-semibold"><?= e($label[0]) ?></small>
                    <small class="text-muted">
                        <?= e($count) ?> leads
                        <?php if ($rate !== null): ?>
                            <span class="badge bg-<?= $rate >= 50 ? 'success' : ($rate >= 25 ? 'warning' : 'danger') ?> ms-1 rounded-pill"><?= e($rate) ?>%</span>
                        <?php endif; ?>
                    </small>
                </div>
                <div style="height: 18px; background: #e2e8f0; border-radius: 6px; overflow: hidden;">
                    <div class="<?= e($label[1]) ?>" style="height: 100%; width: <?= e($width) ?>%; border-radius: 6px; transition: width 0.5s; min-width: 4px;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Percentage shows conversion from previous stage.</small>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-tasks text-warning me-2"></i><?= __('assoc_crm_upcoming_tasks', [], 'Upcoming Tasks') ?></h6>
                <?php if (empty($pending_tasks)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i><?= __('assoc_crm_no_tasks', [], 'No pending tasks') ?></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                    <?php foreach ($pending_tasks as $task):
                        $isOverdue = !empty($task['due_date']) && strtotime($task['due_date']) < time();
                    ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark small"><?= htmlspecialchars($task['title'] ?? '') ?></div>
                                <div class="small text-muted">
                                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($task['lead_name'] ?? __('assoc_crm_unknown', [], 'Unknown')) ?>
                                    <?php if (!empty($task['due_date'])): ?>
                                        &nbsp;&bull;&nbsp;<i class="fas fa-calendar me-1"></i><?= date('M d', strtotime($task['due_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="badge bg-<?= e($isOverdue ? 'danger' : ($task['priority'] === 'high' ? 'warning' : 'info')) ?> rounded-pill"><?= e(ucfirst($task['priority'] ?? 'medium')) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-map-marker-alt text-warning me-2"></i><?= __('assoc_crm_upcoming_visits_title', [], 'Upcoming Visits') ?></span>
                    <a href="<?= BASE_URL ?>/associate/site-visits" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-0" style="font-size: 0.75rem;"><?= __('assoc_crm_view_all', [], 'View All') ?></a>
                </h6>
                <?php if (empty($upcoming_visits)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-calendar-check fa-2x d-block mb-2 text-muted opacity-50"></i><?= __('assoc_crm_no_visits', [], 'No upcoming visits') ?></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                    <?php foreach ($upcoming_visits as $v):
                        $isTodayVisit = (($v['visit_date'] ?? '') === $today);
                    ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark small">
                                    <?= htmlspecialchars($v['visitor_name'] ?? '') ?>
                                    <?php if ($isTodayVisit): ?><span class="badge bg-warning text-dark ms-1"><?= __('assoc_crm_today', [], 'Today') ?></span><?php endif; ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-calendar me-1"></i><?= !empty($v['visit_date']) ? date('D, d M', strtotime($v['visit_date'])) : '—' ?>
                                    <?php if (!empty($v['visit_time'])): ?>
                                        &bull; <i class="fas fa-clock me-1"></i><?= date('h:i A', strtotime($v['visit_time'])) ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($v['lead_name'])): ?>
                                    <div class="small text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($v['lead_name'] ?? '') ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($v['visitor_phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($v['visitor_phone'] ?? '') ?>" class="btn btn-sm btn-outline-success rounded-circle" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Call"><i class="fas fa-phone"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-history text-info me-2"></i><?= __('assoc_crm_recent_activity', [], 'Recent Activity') ?></h6>
                <?php if (empty($recent_activity)): ?>
                    <p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted opacity-50"></i><?= __('assoc_crm_no_activity', [], 'No activity') ?></p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                    <?php foreach (array_slice($recent_activity, 0, 6) as $act): ?>
                    <div class="list-group-item px-0 py-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="small text-dark">
                                    <i class="fas fa-<?= e(($act['interaction_type'] ?? '') === 'call' ? 'phone' : (($act['interaction_type'] ?? '') === 'email' ? 'envelope' : (($act['interaction_type'] ?? '') === 'meeting' ? 'users' : 'comment'))) ?> text-muted me-1"></i>
                                    <?= e(ucfirst(str_replace('_', ' ', $act['interaction_type'] ?? __('assoc_crm_note', [], 'note')))) ?>
                                </strong>
                                <span class="text-muted small ms-1">— <?= htmlspecialchars($act['lead_name'] ?? '') ?></span>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;"><?= !empty($act['created_at']) ? date('M d, g:i A', strtotime($act['created_at'])) : '' ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($bySource)): ?>
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body p-3">
        <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie text-primary me-2"></i><?= __('assoc_crm_leads_by_source', [], 'Leads by Source') ?></h6>
        <div class="d-flex gap-4 flex-wrap">
            <?php foreach ($bySource as $src):
                $total = array_sum(array_column($bySource, 'cnt'));
                $pct = $total > 0 ? round(($src['cnt'] / $total) * 100) : 0;
            ?>
            <div class="text-center p-2 rounded bg-light" style="min-width: 90px;">
                <div class="fs-4 fw-bold text-dark">
                    <?= e($src['cnt']) ?>
                </div>
                <small class="text-muted d-block mt-1"><?= e(ucfirst(str_replace('_', ' ', $src['source'] ?? __('assoc_crm_unknown', [], 'unknown')))) ?></small>
                <small class="badge bg-secondary rounded-pill mt-1"><?= e($pct) ?>%</small>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
