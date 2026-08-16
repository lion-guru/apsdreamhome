<?php
$page_title = $page_title ?? 'Lead Details';
$lead = $lead ?? [];
$timeline = $timeline ?? [];
$interactions = $interactions ?? [];
$tasks = $tasks ?? [];
$deals = $deals ?? [];
$score_breakdown = $score_breakdown ?? [];
$commission = $commission ?? [];
$source_details = $source_details ?? [];
$assignments = $assignments ?? [];
$notes = $notes ?? [];
$agents = $agents ?? [];

$statusColors = [
    'new'=>'danger','contacted'=>'info','qualified'=>'primary','site_visit'=>'warning',
    'proposal'=>'danger','negotiation'=>'dark','booking'=>'success','won'=>'success',
    'lost'=>'secondary','dead'=>'dark','nurture'=>'warning','converted'=>'success','closed'=>'secondary'
];
$statusColor = $statusColors[$lead['status'] ?? 'new'] ?? 'secondary';
$leadScore = (int)($lead['lead_score'] ?? 0);
$scoreColor = $leadScore >= 80 ? 'success' : ($leadScore >= 50 ? 'warning' : ($leadScore >= 30 ? 'info' : 'danger'));
$priorityBadge = ['high'=>'danger','medium'=>'warning','low'=>'info'];
$pBadge = $priorityBadge[$lead['priority'] ?? 'medium'] ?? 'secondary';
$phone = preg_replace('/[^0-9]/', '', $lead['phone'] ?? '');
$budgetFormatted = isset($lead['budget']) ? '₹' . number_format((float)$lead['budget'], 0) : 'N/A';

function timeAgo($dt) {
    if (!$dt) return '';
    $now = new \DateTime();
    $diff = $now->diff(new \DateTime($dt));
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
?>

<style>
.lead-detail-header{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);color:#fff;padding:24px 0;border-radius:0 0 20px 20px;margin-bottom:-20px;position:relative;z-index:1}
.lead-detail-header .lead-name{font-size:28px;font-weight:700;margin:0}
.lead-detail-header .lead-meta{font-size:13px;opacity:.8;margin-top:4px}
.lead-detail-header .lead-score-ring{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;border:4px solid rgba(255,255,255,.3);flex-shrink:0}
.lead-detail-header .lead-score-ring.score-high{border-color:#10b981;background:rgba(16,185,129,.15)}
.lead-detail-header .lead-score-ring.score-med{border-color:#f59e0b;background:rgba(245,158,11,.15)}
.lead-detail-header .lead-score-ring.score-low{border-color:#ef4444;background:rgba(239,68,68,.15)}
.nav-pills-custom .nav-link{border-radius:10px;padding:10px 18px;font-weight:500;color:#555;border:none;transition:.2s}
.nav-pills-custom .nav-link.active{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;box-shadow:0 4px 15px rgba(102,126,234,.4)}
.nav-pills-custom .nav-link:not(.active):hover{background:#f0f0ff}
.stat-card-mini{background:#fff;border-radius:12px;padding:16px;text-align:center;border:1px solid #f0f0f5;transition:.3s}
.stat-card-mini:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.08)}
.stat-card-mini .stat-icon{width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:8px}
.stat-card-mini .stat-val{font-size:22px;font-weight:700;margin:0}
.stat-card-mini .stat-label{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#888;margin:0}
.timeline-item{position:relative;padding-left:30px;margin-bottom:20px;border-left:2px solid #e9ecef}
.timeline-item:last-child{border-left-color:transparent}
.timeline-item::before{content:'';position:absolute;left:-7px;top:4px;width:12px;height:12px;border-radius:50%;background:#667eea;border:2px solid #fff}
.timeline-item.type-call::before{background:#3b82f6}
.timeline-item.type-email::before{background:#8b5cf6}
.timeline-item.type-whatsapp::before{background:#25d366}
.timeline-item.type-meeting::before{background:#f59e0b}
.timeline-item.type-note::before{background:#6b7280}
.timeline-item.type-status::before{background:#10b981}
.timeline-item.type-deal::before{background:#ec4899}
.deal-card{border-radius:12px;border:1px solid #e9ecef;overflow:hidden;transition:.3s}
.deal-card:hover{box-shadow:0 6px 20px rgba(0,0,0,.1);transform:translateY(-2px)}
.deal-card .deal-stage{padding:8px 16px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:1px}
.task-item{border-left:3px solid #e9ecef;padding:12px 16px;margin-bottom:8px;border-radius:0 8px 8px 0;background:#fafafa;transition:.2s}
.task-item:hover{background:#f0f0ff}
.task-item.priority-high{border-left-color:#ef4444}
.task-item.priority-medium{border-left-color:#f59e0b}
.task-item.priority-low{border-left-color:#10b981}
.score-bar{height:8px;border-radius:4px;background:#e9ecef;overflow:hidden;margin-top:4px}
.score-bar .score-fill{height:100%;border-radius:4px;transition:width .8s ease}
.action-btn{border-radius:12px;padding:14px 10px;text-align:center;border:1px solid #e9ecef;background:#fff;transition:.3s;cursor:pointer;text-decoration:none;color:inherit;display:block}
.action-btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.08);text-decoration:none;color:inherit}
.action-btn i{font-size:24px;margin-bottom:6px;display:block}
.action-btn span{font-size:11px;font-weight:600;display:block}
.pipeline-visual{display:flex;gap:4px;margin:16px 0}
.pipeline-visual .pipe-step{flex:1;height:8px;border-radius:4px;background:#e9ecef;position:relative}
.pipeline-visual .pipe-step.active{background:linear-gradient(90deg,#667eea,#764ba2)}
.pipeline-visual .pipe-step.done{background:#10b981}
.pipeline-visual .pipe-step.lost{background:#ef4444}
.interaction-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600}
.note-card{background:#fffef5;border:1px solid #fef3c7;border-radius:10px;padding:14px;margin-bottom:10px}
.note-card .note-text{font-size:14px;line-height:1.6}
.note-card .note-meta{font-size:11px;color:#999;margin-top:8px}
.commission-card{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:12px;padding:20px}
.commission-card h6{color:#166534;font-weight:700}
.commission-card .amount{font-size:28px;font-weight:800;color:#15803d}
@media print{.lead-detail-header{background:#1a1a2e!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.no-print{display:none!important}}
</style>

<!-- Lead Header -->
<div class="lead-detail-header">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div>
                    <div class="d-flex align-items-center gap-3">
                        <h1 class="lead-name"><?= htmlspecialchars($lead['name'] ?? 'N/A') ?></h1>
                        <span class="badge bg-<?= $statusColor ?> text-uppercase" class="style-79379"><?= ucfirst(str_replace('_',' ',$lead['status'] ?? 'new')) ?></span>
                        <span class="badge bg-<?= $pBadge ?>" class="style-37495"><?= ucfirst($lead['priority'] ?? 'Medium') ?></span>
                    </div>
                    <div class="lead-meta">
                        <?php if (!empty($lead['lead_number'])): ?><span><i class="fas fa-hashtag"></i> <?= $lead['lead_number'] ?></span> &middot;<?php endif; ?>
                        <span><i class="fas fa-clock"></i> Created <?= timeAgo($lead['created_at'] ?? '') ?></span>
                        <?php if (!empty($lead['last_activity_date'])): ?> &middot; <span><i class="fas fa-sync"></i> Last activity <?= timeAgo($lead['last_activity_date']) ?></span><?php endif; ?>
                        <?php if (!empty($source_details)): ?> &middot; <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($source_details[0]['source_name'] ?? $lead['source'] ?? '') ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="lead-score-ring <?= $leadScore >= 80 ? 'score-high' : ($leadScore >= 50 ? 'score-med' : 'score-low') ?>">
                    <div>
                        <div class="style-93877"><?= $leadScore ?></div>
                        <div class="style-32890">Score</div>
                    </div>
                </div>
                <div class="no-print">
                    <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left me-1"></i> Leads</a>
                    <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/edit" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i> Edit</a>
                    <button class="btn btn-sm btn-success" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4" class="style-28820">

    <!-- Pipeline Visual -->
    <?php
    $pipelineStages = ['new','contacted','qualified','site_visit','proposal','negotiation','booking','won'];
    $currentIdx = array_search($lead['status'] ?? 'new', $pipelineStages);
    if ($currentIdx === false) $currentIdx = 0;
    ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted fw-bold text-uppercase" class="style-34238">Pipeline Progress</small>
                <small class="text-muted"><?= ($currentIdx + 1) . ' / ' . count($pipelineStages) ?> stages</small>
            </div>
            <div class="pipeline-visual">
                <?php foreach ($pipelineStages as $i => $stage): ?>
                    <div class="pipe-step <?= $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'active' : '') ?>" title="<?= ucfirst(str_replace('_',' ',$stage)) ?>"></div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between" class="style-93284">
                <?php foreach ($pipelineStages as $s): ?><span><?= ucfirst(str_replace('_',' ',$s)) ?></span><?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Smart Insights Banner -->
    <?php
    $lastActivity = $lead['last_activity_date'] ?? null;
    $daysSinceActivity = $lastActivity ? (int)(new \DateTime())->diff(new \DateTime($lastActivity))->d : null;
    $createdAt = $lead['created_at'] ?? null;
    $daysSinceCreated = $createdAt ? (int)(new \DateTime())->diff(new \DateTime($createdAt))->d : null;
    $interactionCount = count($interactions);
    $pendingTasks = count(array_filter($tasks, fn($t) => ($t['status'] ?? '') === 'pending'));
    $overdueTasks = count(array_filter($tasks, fn($t) => ($t['status'] ?? '') !== 'completed' && !empty($t['due_date']) && strtotime($t['due_date']) < time()));
    ?>
    <div class="row g-2 mb-3">
        <?php if ($daysSinceActivity === null): ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-67216">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        <div><small class="fw-bold text-warning">No Activity</small><br><small class="text-muted" class="style-26285">Never contacted</small></div>
                    </div>
                </div>
            </div>
        <?php elseif ($daysSinceActivity >= 7): ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-21976">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-clock text-danger"></i>
                        <div><small class="fw-bold text-danger">Stale Lead</small><br><small class="text-muted" class="style-26285">No activity for <?= $daysSinceActivity ?>d</small></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-86513">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success"></i>
                        <div><small class="fw-bold text-success">Active</small><br><small class="text-muted" class="style-26285">Last: <?= $daysSinceActivity ?>d ago</small></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm" class="style-54821">
                <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                    <i class="fas fa-comments text-primary"></i>
                    <div><small class="fw-bold text-primary"><?= $interactionCount ?> Interactions</small><br><small class="text-muted" class="style-26285"><?= $daysSinceCreated ?>d since creation</small></div>
                </div>
            </div>
        </div>

        <?php if ($overdueTasks > 0): ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-21976">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle text-danger"></i>
                        <div><small class="fw-bold text-danger"><?= $overdueTasks ?> Overdue</small><br><small class="text-muted" class="style-26285"><?= $pendingTasks ?> tasks pending</small></div>
                    </div>
                </div>
            </div>
        <?php elseif ($pendingTasks > 0): ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-24469">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-tasks text-warning"></i>
                        <div><small class="fw-bold text-warning"><?= $pendingTasks ?> Tasks Due</small><br><small class="text-muted" class="style-26285">Pending follow-ups</small></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($leadScore >= 80): ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-86513">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-fire text-success"></i>
                        <div><small class="fw-bold text-success">Hot Lead</small><br><small class="text-muted" class="style-26285">Score <?= $leadScore ?>/100</small></div>
                    </div>
                </div>
            </div>
        <?php elseif ($leadScore <= 20 && $daysSinceCreated > 30): ?>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm" class="style-58405">
                    <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                        <i class="fas fa-snowflake text-info"></i>
                        <div><small class="fw-bold text-info">Cold Lead</small><br><small class="text-muted" class="style-26285">Consider nurture</small></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-xl">
            <div class="stat-card-mini">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-phone"></i></div>
                <p class="stat-val"><?= count($interactions) ?></p>
                <p class="stat-label">Interactions</p>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="stat-card-mini">
                <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-handshake"></i></div>
                <p class="stat-val"><?= count($deals) ?></p>
                <p class="stat-label">Deals</p>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="stat-card-mini">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-tasks"></i></div>
                <p class="stat-val"><?= count($tasks) ?></p>
                <p class="stat-label">Tasks</p>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="stat-card-mini">
                <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-sticky-note"></i></div>
                <p class="stat-val"><?= count($notes) ?></p>
                <p class="stat-label">Notes</p>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="stat-card-mini">
                <div class="stat-icon bg-danger-subtle text-danger"><i class="fas fa-rupee-sign"></i></div>
                <p class="stat-val"><?= $budgetFormatted ?></p>
                <p class="stat-label">Budget</p>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills nav-pills-custom mb-4 flex-nowrap overflow-auto" id="leadTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#tab-overview"><i class="fas fa-user me-1"></i> Overview</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-timeline"><i class="fas fa-history me-1"></i> Timeline</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-interactions"><i class="fas fa-comments me-1"></i> Interactions</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-deals"><i class="fas fa-handshake me-1"></i> Deals</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-tasks"><i class="fas fa-tasks me-1"></i> Tasks</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-notes"><i class="fas fa-sticky-note me-1"></i> Notes</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-score"><i class="fas fa-chart-bar me-1"></i> Score</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-commission"><i class="fas fa-rupee-sign me-1"></i> Commission</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-actions"><i class="fas fa-bolt me-1"></i> Quick Actions</a></li>
    </ul>

    <div class="tab-content">
        <!-- TAB: Overview -->
        <div class="tab-pane fade show active" id="tab-overview">
            <div class="row g-4">
                <!-- Left Column: Lead Info -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="style-24984">
                                    <?= strtoupper(substr($lead['name'] ?? 'N', 0, 1)) ?>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($lead['name'] ?? '') ?></h5>
                                    <?php if (!empty($lead['company'])): ?><small class="text-muted"><?= htmlspecialchars($lead['company']) ?></small><?php endif; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Email</small>
                                    <a href="mailto:<?= htmlspecialchars($lead['email'] ?? '') ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($lead['email'] ?: 'N/A') ?></a>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Phone</small>
                                    <a href="tel:<?= $phone ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($lead['phone'] ?: 'N/A') ?></a>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">City</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($lead['city'] ?: 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">State</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($lead['state'] ?: 'N/A') ?></span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Address</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($lead['address'] ?: 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Property Interest</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($lead['property_interest'] ?: 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Location Pref</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($lead['location_preference'] ?: 'N/A') ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Category</small>
                                    <span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$lead['lead_category'] ?? 'general')) ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Assigned To</small>
                                    <span class="fw-semibold"><?= htmlspecialchars($lead['assigned_to_name'] ?? 'Unassigned') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment History -->
                    <?php if (!empty($assignments)): ?>
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-light py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-user-tag me-2"></i>Assignment History</h6></div>
                        <div class="card-body" class="style-68333">
                            <?php foreach ($assignments as $a): ?>
                                <div class="d-flex justify-content-between align-items-center py-1" class="style-95886">
                                    <div>
                                        <span class="fw-semibold" class="style-87981"><?= htmlspecialchars($a['assigned_to_name'] ?? 'Unknown') ?></span>
                                        <br><small class="text-muted"><?= htmlspecialchars($a['assigned_by_name'] ?? 'System') ?></small>
                                    </div>
                                    <small class="text-muted"><?= timeAgo($a['assigned_at'] ?? $a['created_at'] ?? '') ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Source + Activity Summary -->
                <div class="col-lg-8">
                    <!-- Source Details -->
                    <?php if (!empty($source_details)): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-light py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Lead Source Details</h6></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($source_details as $sd): ?>
                                    <div class="col-md-4">
                                        <div class="bg-light rounded p-3 text-center">
                                            <i class="fas fa-<?= ($sd['source_type'] ?? '') === 'digital' ? 'globe' : (($sd['source_type'] ?? '') === 'referral' ? 'users' : (($sd['source_type'] ?? '') === 'event' ? 'calendar' : 'bullseye')) ?> fa-2x text-primary mb-2"></i>
                                            <div class="fw-bold"><?= htmlspecialchars($sd['source_name'] ?? $lead['source'] ?? '') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($sd['utm_source'] ?? $sd['medium'] ?? '') ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent Timeline (last 5) -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Recent Activity</h6>
                            <a href="#tab-timeline" class="text-decoration-none" onclick="$('#tab-timeline-tab').tab('show')">View All <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                        <div class="card-body" class="style-23214">
                            <?php if (!empty($timeline)): ?>
                                <?php foreach (array_slice($timeline, 0, 8) as $t): ?>
                                    <?php
                                    $tType = strtolower($t['activity_type'] ?? $t['type'] ?? 'note');
                                    $tColor = match($tType) { 'call'=>'primary', 'email'=>'info', 'whatsapp'=>'success', 'meeting'=>'warning', default=>'secondary' };
                                    $tIcon = match($tType) { 'call'=>'phone', 'email'=>'envelope', 'whatsapp'=>'whatsapp', 'meeting'=>'handshake', default=>'circle' };
                                    ?>
                                    <div class="timeline-item type-<?= $tType ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="interaction-badge bg-<?= $tColor ?>-subtle text-<?= $tColor ?>-emphasis">
                                                    <i class="fas fa-<?= $tIcon ?>"></i>
                                                    <?= ucfirst(str_replace('_',' ',$tType)) ?>
                                                </span>
                                                <span class="ms-2 fw-semibold" class="style-42715"><?= htmlspecialchars($t['subject'] ?? $t['title'] ?? $t['description'] ?? '') ?></span>
                                                <?php if (!empty($t['body']) && ($t['body'] ?? '') !== ($t['subject'] ?? '')): ?>
                                                    <p class="mb-0 mt-1 text-muted" class="style-87981"><?= htmlspecialchars(mb_strimwidth($t['body'], 0, 120, '...')) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted text-nowrap ms-2"><?= timeAgo($t['created_at'] ?? $t['activity_date'] ?? '') ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-4 mb-0"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No activities yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: Timeline -->
        <div class="tab-pane fade" id="tab-timeline">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Full Activity Timeline</h6>
                    <span class="badge bg-primary"><?= count($timeline) ?> activities</span>
                </div>
                <div class="card-body" class="style-28677">
                    <?php if (!empty($timeline)): ?>
                        <?php foreach ($timeline as $t): ?>
                            <?php
                            $tType2 = strtolower($t['activity_type'] ?? $t['type'] ?? 'note');
                            $tColor2 = match($tType2) { 'call'=>'primary', 'email'=>'info', 'whatsapp'=>'success', 'meeting'=>'warning', default=>'secondary' };
                            ?>
                            <div class="timeline-item type-<?= $tType2 ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="interaction-badge bg-<?= $tColor2 ?>-subtle text-<?= $tColor2 ?>-emphasis">
                                                <?= ucfirst(str_replace('_',' ',$tType2)) ?>
                                            </span>
                                            <strong class="style-42715"><?= htmlspecialchars($t['subject'] ?? $t['title'] ?? $t['description'] ?? '') ?></strong>
                                        </div>
                                        <?php if (!empty($t['body'])): ?>
                                            <p class="mb-1 text-muted" class="style-87981"><?= nl2br(htmlspecialchars($t['body'])) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($t['outcome'])): ?>
                                            <span class="badge bg-light text-dark"><i class="fas fa-flag me-1"></i><?= htmlspecialchars($t['outcome']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($t['next_action'])): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis ms-1"><i class="fas fa-arrow-right me-1"></i><?= htmlspecialchars($t['next_action']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block"><?= timeAgo($t['created_at'] ?? $t['activity_date'] ?? '') ?></small>
                                        <small class="text-muted"><?= htmlspecialchars($t['user_name'] ?? $t['created_by_name'] ?? 'System') ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-5 mb-0"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No activities recorded yet.<br>Log your first call, email, or meeting above.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB: Interactions -->
        <div class="tab-pane fade" id="tab-interactions">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-comments me-2"></i>Interactions</h6>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-primary" onclick="new bootstrap.Modal(document.getElementById('interactionModal')).show()"><i class="fas fa-plus me-1"></i> Log Interaction</button>
                        <span class="badge bg-primary"><?= count($interactions) ?> total</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($interactions)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="style-89354"><i class="fas fa-hashtag"></i></th>
                                        <th>Type</th>
                                        <th>Direction</th>
                                        <th>Subject</th>
                                        <th>Outcome</th>
                                        <th>Duration</th>
                                        <th>Date</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($interactions as $i => $int): ?>
                                        <tr>
                                            <td class="text-muted"><?= $i + 1 ?></td>
                                            <td><span class="interaction-badge bg-<?= ($int['interaction_type'] ?? '') === 'call' ? 'primary' : (($int['interaction_type'] ?? '') === 'email' ? 'info' : (($int['interaction_type'] ?? '') === 'whatsapp' ? 'success' : 'secondary')) ?>-subtle text-<?= ($int['interaction_type'] ?? '') === 'call' ? 'primary' : (($int['interaction_type'] ?? '') === 'email' ? 'info' : (($int['interaction_type'] ?? '') === 'whatsapp' ? 'success' : 'secondary')) ?>-emphasis"><?= ucfirst($int['interaction_type'] ?? '') ?></span></td>
                                            <td><span class="badge bg-<?= ($int['direction'] ?? '') === 'inbound' ? 'success' : 'primary' ?>-subtle text-<?= ($int['direction'] ?? '') === 'inbound' ? 'success' : 'primary' ?>-emphasis"><?= ucfirst($int['direction'] ?? '') ?></span></td>
                                            <td class="fw-semibold" class="style-86200"><?= htmlspecialchars(mb_strimwidth($int['subject'] ?? '', 0, 50, '...')) ?></td>
                                            <td><?= htmlspecialchars($int['outcome'] ?? '—') ?></td>
                                            <td><?= !empty($int['duration_seconds']) ? gmdate('i:s', $int['duration_seconds']) : '—' ?></td>
                                            <td><small class="text-muted"><?= timeAgo($int['created_at'] ?? '') ?></small></td>
                                            <td><small><?= htmlspecialchars($int['user_name'] ?? 'System') ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 mb-0">No interactions logged yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB: Deals -->
        <div class="tab-pane fade" id="tab-deals">
            <div class="row g-3">
                <?php if (!empty($deals)): ?>
                    <?php foreach ($deals as $deal): ?>
                        <?php
                        $dStage = $deal['stage'] ?? 'prospect';
                        $dColor = match($dStage) { 'won'=>'success', 'lost'=>'danger', 'negotiation'=>'warning', 'proposal'=>'info', default=>'primary' };
                        $dIcon = match($dStage) { 'won'=>'trophy', 'lost'=>'times-circle', default=>'spinner' };
                        ?>
                        <div class="col-md-6">
                            <div class="deal-card">
                                <div class="deal-stage bg-<?= $dColor ?>-subtle text-<?= $dColor ?>-emphasis">
                                    <i class="fas fa-<?= $dIcon ?> me-1"></i>
                                    <?= ucfirst(str_replace('_',' ',$dStage)) ?>
                                </div>
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2"><?= htmlspecialchars($deal['deal_name'] ?? 'Deal #' . $deal['id']) ?></h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Value</small>
                                            <strong class="text-success fs-5">₹<?= number_format((float)($deal['deal_value'] ?? 0)) ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Weighted</small>
                                            <strong class="text-primary">₹<?= number_format((float)($deal['deal_value'] ?? 0) * ((float)($deal['probability'] ?? 50) / 100)) ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Probability</small>
                                            <span class="badge bg-<?= ($deal['probability'] ?? 0) >= 70 ? 'success' : (($deal['probability'] ?? 0) >= 40 ? 'warning' : 'danger') ?>"><?= $deal['probability'] ?? 0 ?>%</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Expected Close</small>
                                            <span class="fw-semibold"><?= $deal['expected_close_date'] ? date('d M Y', strtotime($deal['expected_close_date'])) : 'TBD' ?></span>
                                        </div>
                                        <?php if (!empty($deal['property_type'])): ?>
                                        <div class="col-12">
                                            <small class="text-muted d-block">Property</small>
                                            <span class="badge bg-light text-dark"><?= htmlspecialchars($deal['property_type']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($deal['notes'])): ?>
                                        <p class="mt-2 mb-0 text-muted" class="style-87981"><?= htmlspecialchars(mb_strimwidth($deal['notes'], 0, 100, '...')) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No deals yet</h5>
                            <p class="text-muted">Create a deal to track this lead's opportunity</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TAB: Tasks -->
        <div class="tab-pane fade" id="tab-tasks">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-tasks me-2"></i>Tasks</h6>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-success" onclick="new bootstrap.Modal(document.getElementById('taskModal')).show()"><i class="fas fa-plus me-1"></i> Add Task</button>
                        <span class="badge bg-warning text-dark"><?= count(array_filter($tasks, fn($t) => ($t['status'] ?? '') === 'pending')) ?> pending</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($tasks)): ?>
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-item priority-<?= $task['priority'] ?? 'medium' ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-start gap-2">
                                        <input type="checkbox" class="form-check-input mt-1" <?= ($task['status'] ?? '') === 'completed' ? 'checked' : '' ?> onchange="toggleTask(<?= $task['id'] ?>, this.checked)">
                                        <div>
                                            <strong class="style-20444"><?= htmlspecialchars($task['title'] ?? '') ?></strong>
                                            <?php if (!empty($task['description'])): ?>
                                                <p class="mb-0 text-muted" class="style-86354"><?= htmlspecialchars(mb_strimwidth($task['description'], 0, 80, '...')) ?></p>
                                            <?php endif; ?>
                                            <div class="d-flex gap-2 mt-1">
                                                <span class="badge bg-<?= ($task['task_type'] ?? '') === 'call' ? 'primary' : (($task['task_type'] ?? '') === 'email' ? 'info' : 'secondary') ?>-subtle text-<?= ($task['task_type'] ?? '') === 'call' ? 'primary' : (($task['task_type'] ?? '') === 'email' ? 'info' : 'secondary') ?>-emphasis"><?= ucfirst(str_replace('_',' ',$task['task_type'] ?? 'task')) ?></span>
                                                <?php if (!empty($task['due_date'])): ?>
                                                    <span class="badge bg-<?= (strtotime($task['due_date']) < time() && ($task['status'] ?? '') !== 'completed') ? 'danger' : 'light text-dark' ?>">
                                                        <i class="fas fa-calendar me-1"></i><?= date('d M', strtotime($task['due_date'])) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= timeAgo($task['created_at'] ?? '') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 mb-0">No tasks assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB: Notes -->
        <div class="tab-pane fade" id="tab-notes">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                    <button class="btn btn-sm btn-primary" onclick="addNote()"><i class="fas fa-plus me-1"></i> Add Note</button>
                </div>
                <div class="card-body">
                    <?php if (!empty($notes)): ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="note-card">
                                <div class="note-text"><?= nl2br(htmlspecialchars($note['note'] ?? '')) ?></div>
                                <div class="note-meta">
                                    <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($note['created_by_name'] ?? 'System') ?>
                                    &middot; <i class="fas fa-clock me-1"></i><?= timeAgo($note['created_at'] ?? '') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4 mb-0">No notes yet. Click "Add Note" to get started.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB: Score Breakdown -->
        <div class="tab-pane fade" id="tab-score">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i>Score Breakdown</h6></div>
                        <div class="card-body">
                            <?php if (!empty($score_breakdown) && is_array($score_breakdown)): ?>
                                <?php foreach ($score_breakdown as $factor => $data): ?>
                                    <?php
                                    $val = is_array($data) ? ($data['score'] ?? $data['value'] ?? 0) : $data;
                                    $max = is_array($data) ? ($data['max'] ?? 100) : 100;
                                    $pct = $max > 0 ? min(100, ($val / $max) * 100) : 0;
                                    $barColor = $pct >= 70 ? 'success' : ($pct >= 40 ? 'warning' : 'danger');
                                    ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold text-capitalize"><?= str_replace('_', ' ', $factor) ?></span>
                                            <span class="fw-bold"><?= $val ?>/<?= $max ?></span>
                                        </div>
                                        <div class="score-bar">
                                            <div class="score-fill bg-<?= $barColor ?>" class="style-21859"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-3">No score data available. Score is calculated automatically based on lead activity and engagement.</p>
                            <?php endif; ?>
                            <div class="text-center mt-3 p-3 bg-light rounded">
                                <div class="style-29315"><?= $leadScore ?></div>
                                <small class="text-muted text-uppercase fw-bold">Overall Score</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light py-2"><h6 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2"></i>Score Factors</h6></div>
                        <div class="card-body">
                            <div class="mb-3 p-3 bg-light rounded">
                                <h6 class="fw-bold text-primary"><i class="fas fa-clock me-1"></i> Recency</h6>
                                <p class="mb-0 text-muted" class="style-87981">How recently was the lead contacted or active. Leads contacted in the last 7 days score highest.</p>
                            </div>
                            <div class="mb-3 p-3 bg-light rounded">
                                <h6 class="fw-bold text-success"><i class="fas fa-comments me-1"></i> Engagement</h6>
                                <p class="mb-0 text-muted" class="style-87981">Number of interactions, emails opened, calls answered. More engagement = higher score.</p>
                            </div>
                            <div class="mb-3 p-3 bg-light rounded">
                                <h6 class="fw-bold text-warning"><i class="fas fa-rupee-sign me-1"></i> Budget Fit</h6>
                                <p class="mb-0 text-muted" class="style-87981">How well the lead's budget matches available inventory. Higher match = higher conversion probability.</p>
                            </div>
                            <div class="p-3 bg-light rounded">
                                <h6 class="fw-bold text-info"><i class="fas fa-user-check me-1"></i> Lead Quality</h6>
                                <p class="mb-0 text-muted" class="style-87981">Verified contact info, complete profile, and valid requirements improve the score.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: Commission Estimate -->
        <div class="tab-pane fade" id="tab-commission">
            <?php if (!empty($commission)): ?>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="commission-card">
                        <h6><i class="fas fa-calculator me-2"></i>Estimated Commission</h6>
                        <hr>
                        <div class="row g-3">
                            <?php if (!empty($commission['track_a'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Track A — Direct Sale</small>
                                <div class="amount">₹<?= number_format($commission['track_a']['amount'] ?? 0) ?></div>
                                <small class="text-muted"><?= $commission['track_a']['rate'] ?? 0 ?>% slab rate</small>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($commission['track_b'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Track B — Override</small>
                                <div class="amount">₹<?= number_format($commission['track_b']['amount'] ?? 0) ?></div>
                                <small class="text-muted"><?= $commission['track_b']['rate'] ?? 0 ?>% differential</small>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($commission['track_c'])): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Track C — Performance</small>
                                <div class="amount">₹<?= number_format($commission['track_c']['amount'] ?? 0) ?></div>
                                <small class="text-muted"><?= $commission['track_c']['rate'] ?? 0 ?>% rollup</small>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($commission['total'])): ?>
                            <div class="col-12 text-center mt-3 p-3 bg-white rounded">
                                <small class="text-muted text-uppercase fw-bold">Total Estimated</small>
                                <div class="style-43168">₹<?= number_format($commission['total']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-info-circle me-1"></i>How Commission Works</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><span class="badge bg-primary me-2">A</span> <strong>Direct Sale:</strong> Your rank-based rate on the deal value (5%â€“20%)</li>
                                <li class="mb-2"><span class="badge bg-success me-2">B</span> <strong>Override:</strong> Differential between your rank and downline's rank</li>
                                <li class="mb-2"><span class="badge bg-warning me-2">C</span> <strong>Performance:</strong> Team rollup bonus on qualifying deals</li>
                                <li class="mb-2"><span class="badge bg-info me-2">â˜…</span> <strong>Milestone:</strong> Bonus on achieving rank thresholds</li>
                                <li class="mb-2"><span class="badge bg-danger me-2">!</span> <strong>20% Cap:</strong> Per-transaction commission capped at 20% of deal value</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No commission data</h5>
                    <p class="text-muted">Commission is estimated when a deal is created for this lead</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- TAB: Quick Actions -->
        <div class="tab-pane fade" id="tab-actions">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="https://wa.me/91<?= $phone ?>" target="_blank" class="action-btn">
                        <i class="fab fa-whatsapp text-success"></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="tel:<?= $phone ?>" class="action-btn">
                        <i class="fas fa-phone text-primary"></i>
                        <span>Call Now</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="mailto:<?= htmlspecialchars($lead['email'] ?? '') ?>" class="action-btn">
                        <i class="fas fa-envelope text-info"></i>
                        <span>Send Email</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <button class="action-btn" onclick="addNote()">
                        <i class="fas fa-sticky-note text-warning"></i>
                        <span>Add Note</span>
                    </button>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/edit" class="action-btn">
                        <i class="fas fa-edit text-secondary"></i>
                        <span>Edit Lead</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <button class="action-btn" onclick="changeStatus()">
                        <i class="fas fa-sync text-warning"></i>
                        <span>Change Status</span>
                    </button>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <button class="action-btn" onclick="assignAgent()">
                        <i class="fas fa-user-plus text-success"></i>
                        <span>Reassign</span>
                    </button>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="https://maps.google.com/?q=<?= urlencode($lead['address'] ?? $lead['city'] ?? '') ?>" target="_blank" class="action-btn">
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        <span>View Map</span>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <button class="action-btn" onclick="window.print()">
                        <i class="fas fa-print text-dark"></i>
                        <span>Print Lead</span>
                    </button>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <form method="POST" action="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/destroy" onsubmit="return confirm('Are you sure you want to delete this lead? This cannot be undone.')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="action-btn w-100">
                            <i class="fas fa-trash text-danger"></i>
                            <span>Delete Lead</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Add Note</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/note">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <textarea class="form-control" name="note" rows="5" placeholder="Type your note here..." required class="style-46740"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-sync me-2"></i>Change Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/status">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <label class="form-label fw-bold">New Status</label>
                    <select class="form-select" name="status" required>
                        <?php foreach ($pipelineStages as $s): ?>
                            <option value="<?= $s ?>" <?= ($lead['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                        <option value="nurture" <?= ($lead['status'] ?? '') === 'nurture' ? 'selected' : '' ?>>Nurture</option>
                        <option value="lost" <?= ($lead['status'] ?? '') === 'lost' ? 'selected' : '' ?>>Lost</option>
                        <option value="dead" <?= ($lead['status'] ?? '') === 'dead' ? 'selected' : '' ?>>Dead</option>
                    </select>
                    <label class="form-label fw-bold mt-3">Note (optional)</label>
                    <textarea class="form-control" name="note" rows="3" placeholder="Why is this status changing?"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Agent Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Reassign Lead</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/leads/<?= $lead['id'] ?>/update">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <label class="form-label fw-bold">Assign To</label>
                    <select class="form-select" name="assigned_to" required>
                        <option value="">-- Select Agent --</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= $agent['id'] ?>" <?= ($lead['assigned_to'] ?? '') == $agent['id'] ? 'selected' : '' ?>><?= htmlspecialchars($agent['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label fw-bold mt-3">Note (optional)</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="Why reassigning?"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Reassign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log Interaction Modal -->
<div class="modal fade" id="interactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" class="style-17604">
                <h5 class="modal-title"><i class="fas fa-comments me-2"></i>Log Interaction</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="interactionForm" onsubmit="return submitInteraction(event)">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="call">ðŸ“ž Phone Call</option>
                                <option value="email">ðŸ“§ Email</option>
                                <option value="whatsapp">ðŸ’¬ WhatsApp</option>
                                <option value="meeting">ðŸ¤� Meeting</option>
                                <option value="note">ðŸ“� Note</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Direction</label>
                            <select class="form-select" name="direction">
                                <option value="outbound">Outbound (We contacted)</option>
                                <option value="inbound">Inbound (They contacted)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subject *</label>
                            <input type="text" class="form-control" name="subject" placeholder="Brief summary of interaction..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Details</label>
                            <textarea class="form-control" name="body" rows="3" placeholder="What was discussed? Key points..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Outcome</label>
                            <select class="form-select" name="outcome">
                                <option value="">-- Select --</option>
                                <option value="interested">Interested</option>
                                <option value="not_interested">Not Interested</option>
                                <option value="callback">Callback Requested</option>
                                <option value="meeting_set">Meeting Scheduled</option>
                                <option value="site_visit">Site Visit Planned</option>
                                <option value="no_answer">No Answer</option>
                                <option value="voicemail">Voicemail Left</option>
                                <option value="wrong_number">Wrong Number</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Duration (seconds)</label>
                            <input type="number" class="form-control" name="duration_seconds" placeholder="e.g. 120">
                        </div>
                        <div class="col-12"><hr></div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Follow-up Action</label>
                            <input type="text" class="form-control" name="next_action" placeholder="e.g. Call back to confirm site visit">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Follow-up Date</label>
                            <input type="date" class="form-control" name="next_action_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="interactionSubmitBtn"><i class="fas fa-save me-1"></i> Save Interaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" class="style-57022">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm" onsubmit="return submitTask(event)">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Task Title *</label>
                            <input type="text" class="form-control" name="title" placeholder="e.g. Follow up on pricing query" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type</label>
                            <select class="form-select" name="task_type">
                                <option value="follow_up">Follow-up</option>
                                <option value="call">Call</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting</option>
                                <option value="site_visit">Site Visit</option>
                                <option value="document">Document</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low">ðŸŸ¢ Low</option>
                                <option value="medium" selected>ðŸŸ¡ Medium</option>
                                <option value="high">ðŸ”´ High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Due Date *</label>
                            <input type="date" class="form-control" name="due_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Due Time</label>
                            <input type="time" class="form-control" name="due_time">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Task details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="taskSubmitBtn"><i class="fas fa-save me-1"></i> Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Quick-Log Activity Bar -->
<div id="quickLogBar" class="style-49227">
    <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
        <span class="text-white fw-bold" class="style-69987"><i class="fas fa-bolt me-1"></i>Quick Log:</span>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-light quick-type active" data-type="call" onclick="setQuickType(this)"><i class="fas fa-phone me-1"></i>Call</button>
            <button type="button" class="btn btn-outline-light quick-type" data-type="email" onclick="setQuickType(this)"><i class="fas fa-envelope me-1"></i>Email</button>
            <button type="button" class="btn btn-outline-light quick-type" data-type="whatsapp" onclick="setQuickType(this)"><i class="fab fa-whatsapp me-1"></i>WhatsApp</button>
            <button type="button" class="btn btn-outline-light quick-type" data-type="meeting" onclick="setQuickType(this)"><i class="fas fa-handshake me-1"></i>Meeting</button>
            <button type="button" class="btn btn-outline-light quick-type" data-type="note" onclick="setQuickType(this)"><i class="fas fa-sticky-note me-1"></i>Note</button>
        </div>
        <input type="text" id="quickSubject" class="form-control form-control-sm" placeholder="Quick subject..." class="style-89227">
        <input type="text" id="quickBody" class="form-control form-control-sm" placeholder="Details (optional)..." class="style-85073">
        <button type="button" class="btn btn-sm btn-primary" id="quickLogBtn" onclick="submitQuickLog()" class="style-30672">
            <i class="fas fa-paper-plane me-1"></i> Log
        </button>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleQuickLog()" title="Minimize" class="style-26799">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
</div>

<style>
#quickLogBar.minimized { transform: translateY(calc(100% - 4px)); }
#quickLogBar.minimized:hover { transform: translateY(calc(100% - 40px)); cursor:pointer }
#quickLogBar.minimized::after { content:''; position:absolute; top:-2px; left:50%; transform:translateX(-50%); width:40px; height:4px; background:#667eea; border-radius:2px }
body { padding-bottom: 70px; }
</style>

<script>
const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
const LEAD_ID = '<?= $lead['id'] ?? 0 ?>';
const BASE = '<?= BASE_URL ?>';
let quickType = 'call';

function setQuickType(btn) {
    document.querySelectorAll('.quick-type').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    quickType = btn.dataset.type;
}

function toggleQuickLog() {
    document.getElementById('quickLogBar').classList.toggle('minimized');
}

// —€—€ Quick Log (Sticky Bar) —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
function submitQuickLog() {
    const subject = document.getElementById('quickSubject').value.trim();
    const body = document.getElementById('quickBody').value.trim();
    if (!subject && !body) { showToast('Please enter a subject', 'warning'); return; }

    const btn = document.getElementById('quickLogBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    btn.disabled = true;

    const fd = new FormData();
    fd.append('type', quickType);
    fd.append('subject', subject || quickType.charAt(0).toUpperCase() + quickType.slice(1) + ' with lead');
    fd.append('body', body);
    fd.append('csrf_token', CSRF_TOKEN);

    fetch(BASE + '/admin/leads/' + LEAD_ID + '/log-interaction', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(data => {
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Log';
        btn.disabled = false;
        if (data.success) {
            showToast('âœ“ ' + quickType.charAt(0).toUpperCase() + quickType.slice(1) + ' logged!', 'success');
            document.getElementById('quickSubject').value = '';
            document.getElementById('quickBody').value = '';
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Error: ' + (data.error || 'Failed'), 'danger');
        }
    }).catch(() => {
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Log';
        btn.disabled = false;
        showToast('Network error', 'danger');
    });
}

// —€—€ Full Interaction Modal Submit —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
function submitInteraction(e) {
    e.preventDefault();
    const form = document.getElementById('interactionForm');
    const btn = document.getElementById('interactionSubmitBtn');
    const fd = new FormData(form);

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    btn.disabled = true;

    fetch(BASE + '/admin/leads/' + LEAD_ID + '/log-interaction', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(data => {
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Interaction';
        btn.disabled = false;
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('interactionModal')).hide();
            showToast('âœ“ Interaction logged!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Error: ' + (data.error || 'Failed'), 'danger');
        }
    }).catch(() => {
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Interaction';
        btn.disabled = false;
        showToast('Network error', 'danger');
    });
    return false;
}

// —€—€ Task Modal Submit —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
function submitTask(e) {
    e.preventDefault();
    const form = document.getElementById('taskForm');
    const btn = document.getElementById('taskSubmitBtn');
    const fd = new FormData(form);

    if (!fd.get('title') || !fd.get('due_date')) { showToast('Title and due date required', 'warning'); return false; }

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating...';
    btn.disabled = true;

    fetch(BASE + '/admin/leads/' + LEAD_ID + '/create-task', {
        method: 'POST', body: fd
    }).then(r => r.json()).then(data => {
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Create Task';
        btn.disabled = false;
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('taskModal')).hide();
            showToast('âœ“ Task created!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast('Error: ' + (data.error || 'Failed'), 'danger');
        }
    }).catch(() => {
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Create Task';
        btn.disabled = false;
        showToast('Network error', 'danger');
    });
    return false;
}

// —€—€ Task Toggle (existing) —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
function toggleTask(taskId, completed) {
    fetch(BASE + '/admin/leads/' + LEAD_ID + '/complete-task', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': CSRF_TOKEN},
        body: 'task_id=' + taskId + '&csrf_token=' + CSRF_TOKEN
    }).then(r => r.json()).then(data => {
        if (data.success) showToast(completed ? 'âœ“ Task completed!' : 'Task reopened', 'success');
    });
}

// —€—€ Toast Helper —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
function showToast(msg, type) {
    const toast = document.createElement('div');
    toast.className = 'alert alert-' + type + ' position-fixed shadow-lg';
    toast.style.cssText = 'top:20px;right:20px;z-index:9999;min-width:280px;animation:fadeIn .3s;border-radius:10px;font-size:14px';
    toast.innerHTML = msg;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// —€—€ Existing Modal Openers —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
function addNote() { new bootstrap.Modal(document.getElementById('noteModal')).show(); }
function changeStatus() { new bootstrap.Modal(document.getElementById('statusModal')).show(); }
function assignAgent() { new bootstrap.Modal(document.getElementById('assignModal')).show(); }

// —€—€ Quick Log Enter Key —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
document.getElementById('quickSubject').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); submitQuickLog(); } });
document.getElementById('quickBody').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); submitQuickLog(); } });

// —€—€ Minimized bar click to expand —€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€—€
document.getElementById('quickLogBar').addEventListener('click', function(e) {
    if (this.classList.contains('minimized') && !e.target.closest('button')) { this.classList.remove('minimized'); }
});
</script>
