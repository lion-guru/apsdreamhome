<?php
$page_title = $page_title ?? 'CRM Dashboard';
$stats = $stats ?? [];
$recent_tickets = $recent_tickets ?? [];
$recent_leads = $recent_leads ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

$totalCustomers = (int)($stats['total_customers'] ?? 0);
$activeLeads = (int)($stats['active_leads'] ?? 0);
$openTickets = (int)($stats['open_tickets'] ?? 0);
$totalInquiries = (int)($stats['total_inquiries'] ?? 0);
$convertedMonth = (int)($stats['converted_this_month'] ?? 0);
$pendingFollowups = (int)($stats['pending_followups'] ?? 0);

$kpis = [
    ['icon' => 'fas fa-users', 'val' => $totalCustomers, 'label' => 'Total Customers', 'color' => 'primary', 'bg' => 'primary-subtle'],
    ['icon' => 'fas fa-bullseye', 'val' => $activeLeads, 'label' => 'Active Leads', 'color' => 'warning', 'bg' => 'warning-subtle'],
    ['icon' => 'fas fa-ticket-alt', 'val' => $openTickets, 'label' => 'Open Tickets', 'color' => 'info', 'bg' => 'info-subtle'],
    ['icon' => 'fas fa-question-circle', 'val' => $totalInquiries, 'label' => 'Inquiries', 'color' => 'secondary', 'bg' => 'secondary-subtle'],
    ['icon' => 'fas fa-check-circle', 'val' => $convertedMonth, 'label' => 'Converted (Month)', 'color' => 'success', 'bg' => 'success-subtle'],
    ['icon' => 'fas fa-clock', 'val' => $pendingFollowups, 'label' => 'Pending Follow-ups', 'color' => 'danger', 'bg' => 'danger-subtle'],
];
?>

<style>
.crm-dashboard-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px}
.crm-kpi-card{background:#fff;border-radius:14px;border:1px solid #f0f0f5;transition:.3s;overflow:hidden;position:relative}
.crm-kpi-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.1)}
.crm-kpi-card .kpi-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.crm-kpi-card .kpi-value{font-size:28px;font-weight:800;margin:0;line-height:1}
.crm-kpi-card .kpi-label{font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px;margin:4px 0 0}
.crm-kpi-card .kpi-glow{position:absolute;top:-20px;right:-20px;width:80px;height:80px;border-radius:50%;opacity:.06}
.crm-quick-action{border-radius:12px;text-align:center;border:1px solid #e9ecef;background:#fff;transition:.3s;padding:16px 8px;text-decoration:none;color:inherit}
.crm-quick-action:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.1);text-decoration:none;color:inherit;background:#f8f9ff}
.crm-quick-action i{font-size:28px;margin-bottom:8px;display:block}
.crm-quick-action span{font-size:12px;font-weight:600;display:block}
.crm-section-title{font-size:16px;font-weight:700;color:#333;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.crm-section-title i{color:#667eea}
.recent-item{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #f5f5f5;transition:.2s}
.recent-item:last-child{border:none}
.recent-item:hover{background:#fafafe;border-radius:8px;padding-left:8px;padding-right:8px}
.recent-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px;flex-shrink:0}
</style>

<!-- Header -->
<div class="crm-dashboard-header">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1 fw-bold"><i class="fas fa-tachometer-alt me-2"></i>CRM Dashboard</h2>
                <p class="mb-0 opacity-75" class="style-42715">Lead management, pipeline, and performance overview</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= $base ?>/admin/leads/create" class="btn btn-warning fw-bold"><i class="fas fa-plus me-1"></i> New Lead</a>
                <a href="<?= $base ?>/admin/crm/analytics" class="btn btn-light"><i class="fas fa-chart-line me-1"></i> Analytics</a>
                <a href="<?= $base ?>/admin/lead-kanban" class="btn btn-light"><i class="fas fa-columns me-1"></i> Pipeline</a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4" class="style-71772">
    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($kpis as $kpi): ?>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="crm-kpi-card p-3">
                    <div class="kpi-glow bg-<?= $kpi['color'] ?>"></div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-<?= $kpi['bg'] ?> text-<?= $kpi['color'] ?>">
                            <i class="<?= $kpi['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="kpi-value"><?= number_format($kpi['val']) ?></div>
                            <div class="kpi-label"><?= $kpi['label'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/leads" class="crm-quick-action">
                <i class="fas fa-list text-primary"></i>
                <span>All Leads</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/lead-kanban" class="crm-quick-action">
                <i class="fas fa-columns text-success"></i>
                <span>Kanban Board</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/leads/followups" class="crm-quick-action">
                <i class="fas fa-clock text-warning"></i>
                <span>Follow-ups</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/leads/scoring" class="crm-quick-action">
                <i class="fas fa-star text-info"></i>
                <span>Lead Scoring</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/leads/sources" class="crm-quick-action">
                <i class="fas fa-map-marker-alt text-danger"></i>
                <span>Sources</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/crm/analytics" class="crm-quick-action">
                <i class="fas fa-chart-bar text-secondary"></i>
                <span>Analytics</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/crm/outreach" class="crm-quick-action">
                <i class="fas fa-paper-plane text-purple" class="style-57602"></i>
                <span>Bulk Outreach</span>
            </a>
        </div>
        <div class="col-6 col-md-3 col-lg">
            <a href="<?= $base ?>/admin/leads/import" class="crm-quick-action">
                <i class="fas fa-file-import text-dark"></i>
                <span>Import Leads</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Leads -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h6 class="crm-section-title mb-0"><i class="fas fa-bullseye"></i>Recent Leads</h6>
                    <a href="<?= $base ?>/admin/leads" class="btn btn-sm btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_leads)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No leads yet. Create your first lead!</p>
                            <a href="<?= $base ?>/admin/leads/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Lead</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Assigned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $avatarColors = ['#667eea','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];
                                    $ci = 0;
                                    foreach ($recent_leads as $l):
                                        $color = $avatarColors[$ci % count($avatarColors)];
                                        $ci++;
                                        $statusBadge = match($l['status'] ?? 'new') {
                                            'new' => 'primary',
                                            'contacted' => 'info',
                                            'qualified' => 'info',
                                            'site_visit' => 'warning',
                                            'proposal' => 'danger',
                                            'negotiation' => 'danger',
                                            'won' => 'success',
                                            'converted' => 'success',
                                            'lost' => 'secondary',
                                            'dead' => 'dark',
                                            default => 'secondary'
                                        };
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="recent-avatar" class="style-37549"><?= strtoupper(substr($l['name'] ?? 'N', 0, 1)) ?></div>
                                                    <a href="<?= $base ?>/admin/leads/<?= $l['id'] ?>" class="text-decoration-none fw-bold text-dark"><?= htmlspecialchars($l['name'] ?? '') ?></a>
                                                </div>
                                            </td>
                                            <td><small class="text-muted"><?= htmlspecialchars($l['phone'] ?? '') ?></small></td>
                                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars($l['source'] ?? 'N/A') ?></span></td>
                                            <td><span class="badge bg-<?= $statusBadge ?>"><?= ucfirst(str_replace('_',' ',$l['status'] ?? '')) ?></span></td>
                                            <td>
                                                <?php $score = (int)($l['lead_score'] ?? 0); ?>
                                                <span class="badge bg-<?= $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger') ?>"><?= $score ?></span>
                                            </td>
                                            <td><small class="text-muted"><?= htmlspecialchars($l['assignee_name'] ?? 'Unassigned') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Tickets + Pending Follow-ups -->
        <div class="col-lg-5">
            <!-- Support Tickets -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h6 class="crm-section-title mb-0"><i class="fas fa-ticket-alt"></i>Support Tickets</h6>
                    <a href="<?= $base ?>/admin/crm/support" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_tickets)): ?>
                        <p class="text-muted text-center py-4 mb-0">No tickets</p>
                    <?php else: ?>
                        <?php foreach (array_slice($recent_tickets, 0, 6) as $t): ?>
                            <div class="recent-item px-3">
                                <div class="recent-avatar" class="style-4018">
                                    <i class="fas fa-<?= ($t['status'] ?? '') === 'open' ? 'exclamation' : (($t['status'] ?? '') === 'resolved' ? 'check' : 'clock') ?>"></i>
                                </div>
                                <div class="flex-grow-1" class="style-62036">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold text-truncate" class="style-94021"><?= htmlspecialchars($t['subject'] ?? 'Ticket') ?></span>
                                        <small class="text-muted text-nowrap ms-2"><?= date('d M', strtotime($t['created_at'])) ?></small>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($t['user_name'] ?? 'Unknown') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Follow-ups -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h6 class="crm-section-title mb-0"><i class="fas fa-clock"></i>Pending Follow-ups</h6>
                    <a href="<?= $base ?>/admin/leads/followups" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($pendingFollowups > 0): ?>
                        <div class="p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-danger"><?= $pendingFollowups ?></span>
                                <small class="text-muted">leads need follow-up</small>
                            </div>
                            <div class="progress mb-3" class="style-32124">
                                <div class="progress-bar bg-danger" class="style-75914"></div>
                            </div>
                            <a href="<?= $base ?>/admin/leads/followups" class="btn btn-sm btn-warning w-100 fw-bold">
                                <i class="fas fa-arrow-right me-1"></i> Handle Follow-ups
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <p class="text-muted mb-0">All caught up!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Pipeline Summary + Conversion Funnel -->
    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="crm-section-title mb-0"><i class="fas fa-project-diagram"></i>Pipeline Summary</h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $db = \App\Core\Database\Database::getInstance()->getConnection();
                        $pipeline = $db->query("SELECT status, COUNT(*) as cnt, SUM(COALESCE(budget, 0)) as total_val FROM leads WHERE deleted_at IS NULL AND status NOT IN ('converted','closed','dead') GROUP BY status ORDER BY FIELD(status, 'new','contacted','qualified','site_visit','proposal','negotiation','booking','won')")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                    } catch (\Throwable $e) { $pipeline = []; }
                    ?>
                    <?php if (!empty($pipeline)): ?>
                        <?php
                        $cnts = array_map(function($p) { return (int)$p['cnt']; }, $pipeline);
                        $maxPipeline = max($cnts);
                        if ($maxPipeline < 1) $maxPipeline = 1;
                        $stageColors = ['new'=>'#667eea','contacted'=>'#3b82f6','qualified'=>'#8b5cf6','site_visit'=>'#f59e0b','proposal'=>'#ec4899','negotiation'=>'#ef4444','booking'=>'#06b6d4','won'=>'#10b981'];
                        foreach ($pipeline as $p):
                            $width = max(8, ((int)$p['cnt'] / $maxPipeline) * 100);
                            $color = $stageColors[$p['status']] ?? '#6b7280';
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold" class="style-87981"><?= ucfirst(str_replace('_',' ',$p['status'])) ?></span>
                                    <span class="fw-bold"><?= (int)$p['cnt'] ?> leads &middot; ₹<?= number_format((float)$p['total_val'] / 100000, 1) ?>L</span>
                                </div>
                                <div class="progress" class="style-28392">
                                    <div class="progress-bar" class="style-25298"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No pipeline data</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="crm-section-title mb-0"><i class="fas fa-filter"></i>Lead Status Distribution</h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $db = \App\Core\Database\Database::getInstance()->getConnection();
                        $statusDist = $db->query("SELECT status, COUNT(*) as cnt FROM leads WHERE deleted_at IS NULL GROUP BY status ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                    } catch (\Throwable $e) { $statusDist = []; }
                    $totalAll = array_sum(array_map(fn($s) => (int)$s['cnt'], $statusDist)) ?: 1;
                    ?>
                    <?php if (!empty($statusDist)): ?>
                        <!-- Donut Chart (CSS-based) -->
                        <?php
                        $donutDeg = 0;
                        $donutColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997'];
                        $donutGradientParts = [];
                        foreach (array_slice($statusDist, 0, 8) as $di => $ds) {
                            $dpct = ((int)$ds['cnt'] / $totalAll) * 360;
                            $dcolor = $donutColors[$di % count($donutColors)];
                            $donutGradientParts[] = $dcolor . ' ' . $donutDeg . 'deg ' . ($donutDeg + $dpct) . 'deg';
                            $donutDeg += $dpct;
                        }
                        $donutGradient = implode(', ', $donutGradientParts);
                        ?>
                        <div class="d-flex align-items-center gap-4">
                            <div style="width:120px;height:120px;border-radius:50%;background:conic-gradient(<?= $donutGradient ?>);position:relative;flex-shrink:0">
                                <div class="style-36412">
                                    <strong class="style-92777"><?= number_format(array_sum(array_map(fn($s) => (int)$s['cnt'], $statusDist))) ?></strong>
                                </div>
                            </div>
                            <div class="style-47240">
                                <?php foreach (array_slice($statusDist, 0, 8) as $i => $s): ?>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="style-23855"></div>
                                        <span class="style-25759"><?= ucfirst(str_replace('_',' ',$s['status'])) ?></span>
                                        <span class="fw-bold" class="style-86354"><?= (int)$s['cnt'] ?></span>
                                        <small class="text-muted" class="style-26285"><?= round(((int)$s['cnt'] / $totalAll) * 100) ?>%</small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center mb-0">No data</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
