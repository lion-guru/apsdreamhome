<?php
$page_title = $page_title ?? 'Lead Details';
$lead = $lead ?? [];
$activities = $activities ?? [];
$site_visits = $site_visits ?? [];
$colonies = $colonies ?? [];
$success = $success ?? null;
$error = $error ?? null;

$statuses = [
    'new' => ['label' => 'New', 'color' => 'primary', 'icon' => 'fa-star'],
    'contacted' => ['label' => 'Contacted', 'color' => 'info', 'icon' => 'fa-phone'],
    'qualified' => ['label' => 'Qualified', 'color' => 'warning', 'icon' => 'fa-check-circle'],
    'site_visit' => ['label' => 'Site Visit', 'color' => 'purple', 'icon' => 'fa-map-marker-alt'],
    'proposal' => ['label' => 'Proposal Sent', 'color' => 'pink', 'icon' => 'fa-file-alt'],
    'negotiation' => ['label' => 'Negotiation', 'color' => 'orange', 'icon' => 'fa-handshake'],
    'closed_won' => ['label' => 'Closed Won', 'color' => 'success', 'icon' => 'fa-trophy'],
    'closed_lost' => ['label' => 'Closed Lost', 'color' => 'secondary', 'icon' => 'fa-times-circle'],
    'nurture' => ['label' => 'Nurture', 'color' => 'teal', 'icon' => 'fa-seedling'],
];
$pipelineOrder = ['new','contacted','qualified','site_visit','proposal','negotiation','closed_won'];
$currentStatus = $lead['status'] ?? 'new';
$currentIdx = array_search($currentStatus, $pipelineOrder);
if ($currentIdx === false) $currentIdx = 0;

$phone = preg_replace('/[^0-9]/', '', $lead['phone'] ?? '');
$leadName = htmlspecialchars($lead['name'] ?? '');
$leadPhone = htmlspecialchars($lead['phone'] ?? '');
$today = date('Y-m-d');
?>

<style>
    .crm-header { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #fff; border-radius: 16px; padding: 24px; margin-bottom: 20px; }
    .crm-header .lead-name { font-size: 1.5rem; font-weight: 700; }
    .crm-header .lead-meta { opacity: 0.8; font-size: 0.85rem; }
    .crm-pipeline-bar { display: flex; gap: 0; margin-bottom: 20px; border-radius: 10px; overflow: hidden; }
    .pipeline-step { flex: 1; padding: 10px 6px; text-align: center; font-size: 0.68rem; font-weight: 600; cursor: pointer; transition: all 0.2s; position: relative; }
    .pipeline-step.completed { background: #10b981; color: #fff; }
    .pipeline-step.current { background: #6366f1; color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,0.4); }
    .pipeline-step.future { background: #f1f5f9; color: #94a3b8; }
    .pipeline-step:not(:last-child)::after { content: ''; position: absolute; right: -8px; top: 50%; transform: translateY(-50%); width: 0; height: 0; border-top: 10px solid transparent; border-bottom: 10px solid transparent; z-index: 1; }
    .pipeline-step.completed:not(:last-child)::after { border-left: 10px solid #10b981; }
    .pipeline-step.current:not(:last-child)::after { border-left: 10px solid #6366f1; }
    .pipeline-step.future:not(:last-child)::after { border-left: 10px solid #f1f5f9; }
    .detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
    .detail-card h6 { font-weight: 700; color: #1e293b; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #f1f5f9; }
    .detail-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f8fafc; }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: #64748b; font-size: 0.85rem; }
    .detail-value { font-weight: 600; color: #1e293b; font-size: 0.85rem; }
    .activity-timeline { position: relative; padding-left: 30px; }
    .activity-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #e2e8f0; }
    .activity-item { position: relative; margin-bottom: 20px; }
    .activity-item::before { content: ''; position: absolute; left: -24px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: #6366f1; border: 2px solid #fff; box-shadow: 0 0 0 2px #e2e8f0; }
    .activity-item.note::before { background: #f59e0b; }
    .activity-item.status::before { background: #10b981; }
    .activity-item.created::before { background: #3b82f6; }
    .activity-item.site_visit::before { background: #14b8a6; }
    .activity-item.call::before { background: #06b6d4; }
    .activity-item.email::before { background: #ec4899; }
    .whatsapp-template { padding: 10px 14px; border-radius: 10px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s; border: 1px solid #e2e8f0; font-size: 0.85rem; }
    .whatsapp-template:hover { background: #f0fdf4; border-color: #25d366; }
    .whatsapp-template .wa-icon { color: #25d366; font-size: 1.2rem; }
    .score-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
    .score-fill { height: 100%; border-radius: 4px; transition: width 0.5s; }
    .sv-mini { padding: 10px; border-radius: 8px; margin-bottom: 8px; border: 1px solid #f1f5f9; }
    .sv-mini .sv-date { font-weight: 700; font-size: 0.85rem; }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Back link -->
    <a href="<?= BASE_URL ?>/associate/leads" class="text-decoration-none mb-3 d-inline-block">
        <i class="fas fa-arrow-left me-1"></i> Back to Leads
    </a>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- CRM Header -->
    <div class="crm-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="lead-name"><i class="fas fa-user-circle me-2"></i><?= $leadName ?></div>
                <div class="lead-meta mt-1">
                    <i class="fas fa-phone me-1"></i><?= $leadPhone ?>
                    <?php if (!empty($lead['email'])): ?>
                        &nbsp;&bull;&nbsp;<i class="fas fa-envelope me-1"></i><?= htmlspecialchars($lead['email']) ?>
                    <?php endif; ?>
                    &nbsp;&bull;&nbsp;<i class="fas fa-calendar me-1"></i>Added <?= date('M d, Y', strtotime($lead['created_at'])) ?>
                    <?php
                    $score = (int)($lead['lead_score'] ?? 0);
                    $scoreColor = $score >= 70 ? '#10b981' : ($score >= 40 ? '#f59e0b' : '#94a3b8');
                    ?>
                    &nbsp;&bull;&nbsp;<span style="color:<?= $scoreColor ?>;font-weight:700;">Score: <?= $score ?>/100</span>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="tel:<?= $leadPhone ?>" class="btn btn-light btn-sm"><i class="fas fa-phone me-1"></i>Call</a>
                <?php if (!empty($lead['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="btn btn-light btn-sm"><i class="fas fa-envelope me-1"></i>Email</a>
                <?php endif; ?>
                <a href="https://wa.me/91<?= $phone ?>" class="btn btn-light btn-sm" target="_blank" style="color:#25d366;"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleVisitModal"><i class="fas fa-map-marker-alt me-1"></i>Schedule Visit</button>
            </div>
        </div>
    </div>

    <!-- Pipeline Bar -->
    <div class="crm-pipeline-bar">
        <?php foreach ($pipelineOrder as $idx => $sKey):
            $s = $statuses[$sKey];
            $cls = 'future';
            if ($idx < $currentIdx) $cls = 'completed';
            elseif ($idx == $currentIdx) $cls = 'current';
        ?>
        <div class="pipeline-step <?= $cls ?>" onclick="changeStatus('<?= $sKey ?>')">
            <i class="fas <?= $s['icon'] ?> me-1"></i><?= $s['label'] ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Lead Details -->
            <div class="detail-card">
                <h6><i class="fas fa-info-circle text-primary me-2"></i>Lead Details</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-row"><span class="detail-label">Property Interest</span><span class="detail-value"><?= htmlspecialchars($lead['property_interest'] ?: '—') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Budget Range</span><span class="detail-value"><?= htmlspecialchars($lead['budget_range'] ?: '—') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Preferred Location</span><span class="detail-value"><?= htmlspecialchars($lead['location_preference'] ?: '—') ?></span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-row"><span class="detail-label">Source</span><span class="detail-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $lead['source'] ?? ''))) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Priority</span><span class="detail-value"><span class="badge bg-<?= $lead['priority'] === 'high' ? 'danger' : ($lead['priority'] === 'low' ? 'info' : 'warning') ?>"><?= ucfirst($lead['priority'] ?? 'medium') ?></span></span></div>
                        <div class="detail-row">
                            <span class="detail-label">Lead Score</span>
                            <span class="detail-value">
                                <span style="font-weight:700;color:<?= $scoreColor ?>"><?= $score ?></span>/100
                                <div class="score-bar mt-1" style="width:120px;">
                                    <div class="score-fill" style="width:<?= $score ?>%;background:<?= $scoreColor ?>;"></div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                <?php if (!empty($lead['notes'])): ?>
                    <hr>
                    <div class="detail-label mb-1">Notes</div>
                    <p class="mb-0" style="white-space: pre-line; font-size: 0.9rem; color: #334155;"><?= htmlspecialchars($lead['notes']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Site Visits for this Lead -->
            <div class="detail-card">
                <h6 class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-map-marker-alt text-purple me-2"></i>Site Visits (<?= count($site_visits) ?>)</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#scheduleVisitModal"><i class="fas fa-plus me-1"></i>New Visit</button>
                </h6>
                <?php if (empty($site_visits)): ?>
                    <p class="text-muted text-center py-3 mb-0">No site visits scheduled yet. <a href="#" data-bs-toggle="modal" data-bs-target="#scheduleVisitModal">Schedule one now</a>.</p>
                <?php else: ?>
                    <?php foreach ($site_visits as $sv):
                        $svStatus = match($sv['status'] ?? 'scheduled') {
                            'completed' => ['color'=>'success','icon'=>'fa-check-circle','label'=>'Completed'],
                            'cancelled' => ['color'=>'secondary','icon'=>'fa-times-circle','label'=>'Cancelled'],
                            'rescheduled' => ['color'=>'warning','icon'=>'fa-calendar-alt','label'=>'Rescheduled'],
                            default => ['color'=>'primary','icon'=>'fa-calendar-check','label'=>'Scheduled'],
                        };
                        $svDate = date('D, d M Y', strtotime($sv['visit_date']));
                        $svTime = date('h:i A', strtotime($sv['visit_time']));
                        $isTodayVisit = ($sv['visit_date'] === $today);
                    ?>
                    <div class="sv-mini <?= $isTodayVisit ? 'border-primary bg-light' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="sv-date" style="color:<?= $isTodayVisit ? '#0d9488' : '#1e293b' ?>">
                                    <i class="fas fa-calendar me-1"></i><?= $svDate ?> at <?= $svTime ?>
                                    <?php if ($isTodayVisit): ?><span class="badge bg-primary ms-1">Today</span><?php endif; ?>
                                </div>
                                <small class="text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($sv['visitor_name']) ?></small>
                                <?php if (!empty($sv['notes'])): ?>
                                    <div style="font-size:0.8rem;color:#475569;"><?= htmlspecialchars(mb_substr($sv['notes'], 0, 80)) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($sv['rating'])): ?>
                                    <div style="color:#f59e0b;font-size:0.8rem;"><?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star<?= $i <= $sv['rating'] ? '' : '-o' ?>"></i><?php endfor; ?></div>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-<?= $svStatus['color'] ?>"><?= $svStatus['label'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Activity Timeline -->
            <div class="detail-card">
                <h6><i class="fas fa-history text-primary me-2"></i>Activity Timeline</h6>
                <?php if (empty($activities)): ?>
                    <p class="text-muted text-center py-3">No activities yet</p>
                <?php else: ?>
                    <div class="activity-timeline">
                        <?php foreach ($activities as $act):
                            $actClass = 'created';
                            $actIcon = 'fa-circle';
                            $actLabel = ucfirst(str_replace('_', ' ', $act['activity_type']));
                            if ($act['activity_type'] === 'note') { $actClass = 'note'; $actIcon = 'fa-sticky-note'; $actLabel = 'Note Added'; }
                            elseif ($act['activity_type'] === 'status_change') { $actClass = 'status'; $actIcon = 'fa-sync'; $actLabel = 'Status Updated'; }
                            elseif ($act['activity_type'] === 'site_visit') { $actClass = 'site_visit'; $actIcon = 'fa-map-marker-alt'; $actLabel = 'Site Visit'; }
                            elseif ($act['activity_type'] === 'call') { $actClass = 'call'; $actIcon = 'fa-phone'; $actLabel = 'Call'; }
                            elseif ($act['activity_type'] === 'email') { $actClass = 'email'; $actIcon = 'fa-envelope'; $actLabel = 'Email'; }
                        ?>
                        <div class="activity-item <?= $actClass ?>">
                            <div class="d-flex justify-content-between">
                                <strong style="font-size: 0.85rem;"><i class="fas <?= $actIcon ?> me-1"></i> <?= $actLabel ?></strong>
                                <small class="text-muted"><?= date('M d, Y g:i A', strtotime($act['created_at'])) ?></small>
                            </div>
                            <p class="mb-0 mt-1" style="font-size: 0.85rem; color: #475569;"><?= htmlspecialchars($act['description']) ?></p>
                            <?php if (!empty($act['old_value']) && !empty($act['new_value'])): ?>
                                <small class="text-muted"><i class="fas fa-arrow-right me-1"></i><?= htmlspecialchars($act['old_value']) ?> → <?= htmlspecialchars($act['new_value']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Add Note / Schedule Follow-up -->
            <div class="detail-card">
                <h6><i class="fas fa-sticky-note text-warning me-2"></i>Add Note / Schedule Follow-up</h6>
                <form action="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>/note" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <textarea class="form-control" name="note" rows="3" placeholder="Add a note about this lead, follow-up details, client feedback..." required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem;"><i class="fas fa-calendar me-1"></i>Follow-up Date</label>
                            <input type="date" class="form-control form-control-sm" name="followup_date" min="<?= $today ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem;"><i class="fas fa-clock me-1"></i>Time</label>
                            <input type="time" class="form-control form-control-sm" name="followup_time">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem;">Task Type</label>
                            <select class="form-select form-select-sm" name="task_type">
                                <option value="follow_up">Follow-up Call</option>
                                <option value="visit">Site Visit</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size: 0.8rem;">Priority</label>
                            <select class="form-select form-select-sm" name="task_priority">
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-plus me-1"></i> Add Note</button>
                    <button type="submit" class="btn btn-primary btn-sm ms-1" name="schedule_followup" value="1"><i class="fas fa-calendar-plus me-1"></i> Schedule Follow-up</button>
                </form>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Status Update -->
            <div class="detail-card">
                <h6><i class="fas fa-sync text-primary me-2"></i>Update Status</h6>
                <form action="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>/status" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <select class="form-select" name="status">
                            <?php foreach ($statuses as $key => $s): ?>
                                <option value="<?= $key ?>" <?= $currentStatus === $key ? 'selected' : '' ?>><?= $s['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Update Status</button>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="detail-card">
                <h6><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="tel:<?= $leadPhone ?>" class="btn btn-outline-success">
                        <i class="fas fa-phone me-2"></i>Call Client
                    </a>
                    <?php if (!empty($lead['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($lead['email']) ?>?subject=APS Dream Home - Property Inquiry" class="btn btn-outline-info">
                        <i class="fas fa-envelope me-2"></i>Send Email
                    </a>
                    <?php endif; ?>
                    <a href="https://wa.me/91<?= $phone ?>" class="btn btn-outline-success" target="_blank" style="border-color: #25d366; color: #25d366;">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <button class="btn btn-outline-purple" style="border-color:#14b8a6;color:#14b8a6;" data-bs-toggle="modal" data-bs-target="#scheduleVisitModal">
                        <i class="fas fa-map-marker-alt me-2"></i>Schedule Site Visit
                    </button>
                    <hr>
                    <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>All Leads
                    </a>
                    <a href="<?= BASE_URL ?>/associate/leads/add" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Add New Lead
                    </a>
                </div>
            </div>

            <!-- WhatsApp Templates -->
            <div class="detail-card">
                <h6><i class="fab fa-whatsapp me-2" style="color:#25d366;"></i>Quick Messages</h6>
                <?php
                $waTemplates = [
                    ['label'=>'Follow-up', 'msg'=>"Hi {$leadName}, just checking in regarding your property inquiry at APS Dream Home. Do you have any questions?"],
                    ['label'=>'Site Visit Invite', 'msg'=>"Hi {$leadName}, would you like to schedule a site visit? We'd love to show you the property in person. Let us know a convenient date and time."],
                    ['label'=>'Property Info', 'msg'=>"Hi {$leadName}, thank you for your interest in APS Dream Home! I'd be happy to share more details about available properties. What specifically are you looking for?"],
                    ['label'=>'Thank You', 'msg'=>"Hi {$leadName}, thank you for visiting APS Dream Home! We hope you liked the property. Feel free to reach out for any queries."],
                ];
                foreach ($waTemplates as $t):
                    $encoded = urlencode($t['msg']);
                ?>
                <div class="whatsapp-template" onclick="window.open('https://wa.me/91<?= $phone ?>?text=<?= $encoded ?>','_blank')">
                    <div class="d-flex align-items-center gap-2">
                        <span class="wa-icon"><i class="fab fa-whatsapp"></i></span>
                        <span><?= $t['label'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

    <!-- AI Score Breakdown -->
    <div class="detail-card">
        <h6><i class="fas fa-brain text-purple me-2"></i>AI Score Breakdown</h6>
        <?php
        $scoreBreakdown = null;
        try {
            $scoringService = new \App\Services\LeadScoringService();
            $scoreBreakdown = $scoringService->calculateScore($lead['id']);
        } catch (\Throwable $e) {}
        ?>
        <?php if ($scoreBreakdown): ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:0.85rem;font-weight:600;color:#1e293b;">Total Score</span>
                    <span style="font-size:1.2rem;font-weight:700;color:<?= $scoreBreakdown['total'] >= 70 ? '#10b981' : ($scoreBreakdown['total'] >= 40 ? '#f59e0b' : '#94a3b8') ?>"><?= $scoreBreakdown['total'] ?>/100</span>
                </div>
                <div class="score-bar mb-3" style="height:10px;">
                    <div class="score-fill" style="width:<?= $scoreBreakdown['total'] ?>%;background:<?= $scoreBreakdown['total'] >= 70 ? '#10b981' : ($scoreBreakdown['total'] >= 40 ? '#f59e0b' : '#94a3b8') ?>;"></div>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div style="padding:8px 12px;border-radius:8px;background:#f0f9ff;border:1px solid #bae6fd;">
                        <div style="font-size:0.75rem;color:#0369a1;font-weight:500;">Demographics</div>
                        <div style="font-size:1.1rem;font-weight:700;color:#0c4a6e;"><?= $scoreBreakdown['demographics'] ?>/40</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="padding:8px 12px;border-radius:8px;background:#fef3c7;border:1px solid #fde68a;">
                        <div style="font-size:0.75rem;color:#92400e;font-weight:500;">Engagement</div>
                        <div style="font-size:1.1rem;font-weight:700;color:#78350f;"><?= $scoreBreakdown['engagement'] ?>/40</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="padding:8px 12px;border-radius:8px;background:#ede9fe;border:1px solid #99f6e4;">
                        <div style="font-size:0.75rem;color:#134e4a;font-weight:500;">Behavior</div>
                        <div style="font-size:1.1rem;font-weight:700;color:#5b21b6;"><?= $scoreBreakdown['behavior'] ?>/40</div>
                    </div>
                </div>
                <div class="col-6">
                    <div style="padding:8px 12px;border-radius:8px;background:#fce7f3;border:1px solid #f9a8d4;">
                        <div style="font-size:0.75rem;color:#be185d;font-weight:500;">AI Analysis</div>
                        <div style="font-size:1.1rem;font-weight:700;color:#9d174d;"><?= $scoreBreakdown['ai_analysis'] ?>/40</div>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-<?= $scoreBreakdown['rank'] === 'hot' || $scoreBreakdown['rank'] === 'hot_plus' ? 'danger' : ($scoreBreakdown['rank'] === 'warm' ? 'warning' : 'secondary') ?>" style="font-size:0.75rem;">
                    <?= ucfirst(str_replace('_', ' ', $scoreBreakdown['rank'])) ?>
                </span>
                <?php if ($scoreBreakdown['is_hot']): ?>
                    <span class="badge bg-danger" style="font-size:0.7rem;"><i class="fas fa-fire me-1"></i>Hot Lead</span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-2" style="font-size:0.85rem;">Score not yet calculated for this lead.</p>
        <?php endif; ?>
        <form method="POST" action="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>/recalculate-score" class="mt-2">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <button type="submit" class="btn btn-outline-purple btn-sm w-100" style="border-color:#14b8a6;color:#14b8a6;">
                <i class="fas fa-sync me-1"></i> Recalculate Score
            </button>
        </form>
    </div>

    <!-- Revenue & Commission -->
    <?php if (!empty($lead['budget_range']) || !empty($lead['budget'])): ?>
    <div class="detail-card">
        <h6><i class="fas fa-coins text-success me-2"></i>Revenue & Commission</h6>
        <?php
        $budget = (float)($lead['budget'] ?? 0);
        $budgetRange = $lead['budget_range'] ?? '';
        if ($budget === 0 && preg_match('/(\d[\d,]*)/', str_replace(['₹', ' '], '', $budgetRange), $m)) {
            $budget = (float)str_replace(',', '', $m[1]);
        }
        $commissionRates = [
            'associate' => 0.05, 'sr_associate' => 0.07, 'bdm' => 0.10,
            'sr_bdm' => 0.12, 'vice_president' => 0.15, 'president' => 0.18, 'site_manager' => 0.20,
        ];
        $currentUserRole = $_SESSION['role'] ?? 'associate';
        $myRate = $commissionRates[$currentUserRole] ?? 0.05;
        $estCommission = $budget * $myRate;
        ?>
        <div class="row g-2 mb-2">
            <div class="col-6">
                <div style="padding:8px 12px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;">
                    <div style="font-size:0.75rem;color:#166534;font-weight:500;">Estimated Budget</div>
                    <div style="font-size:1rem;font-weight:700;color:#14532d;">₹<?= number_format($budget) ?></div>
                </div>
            </div>
            <div class="col-6">
                <div style="padding:8px 12px;border-radius:8px;background:#fef3c7;border:1px solid #fde68a;">
                    <div style="font-size:0.75rem;color:#92400e;font-weight:500;">Your Commission (<?= number_format($myRate * 100, 0) ?>%)</div>
                    <div style="font-size:1rem;font-weight:700;color:#78350f;">₹<?= number_format($estCommission) ?></div>
                </div>
            </div>
        </div>
        <div style="font-size:0.78rem;color:#64748b;">
            <i class="fas fa-info-circle me-1"></i>Commission based on your current rank. Actual commission depends on booking value and payment status.
        </div>
    </div>
    <?php endif; ?>

    <!-- Timeline -->
    <div class="detail-card">
        <h6><i class="fas fa-clock text-info me-2"></i>Timeline</h6>
                <div class="detail-row"><span class="detail-label">Created</span><span class="detail-value"><?= date('M d, Y', strtotime($lead['created_at'])) ?></span></div>
                <div class="detail-row"><span class="detail-label">Updated</span><span class="detail-value"><?= date('M d, Y', strtotime($lead['updated_at'])) ?></span></div>
                <?php if (!empty($lead['next_activity_date'])): ?>
                    <?php
                    $nextDate = strtotime($lead['next_activity_date']);
                    $isOverdue = $nextDate < time();
                    ?>
                    <div class="detail-row"><span class="detail-label">Next Follow-up</span><span class="detail-value <?= $isOverdue ? 'text-danger fw-bold' : '' ?>"><?= date('M d, Y', $nextDate) ?> <?= $isOverdue ? '(Overdue)' : '' ?></span></div>
                <?php endif; ?>
                <?php if (!empty($lead['last_activity_date'])): ?>
                    <div class="detail-row"><span class="detail-label">Last Activity</span><span class="detail-value"><?= date('M d, Y', strtotime($lead['last_activity_date'])) ?></span></div>
                <?php endif; ?>
            </div>

            <!-- Assign Lead -->
            <div class="detail-card">
                <h6><i class="fas fa-user-friends text-primary me-2"></i>Assign to Team Member</h6>
                <?php
                $teamMembers = [];
                try {
                    $db2 = \App\Core\Database\Database::getInstance();
                    $pdo2 = $db2->getConnection();
                    $st2 = $pdo2->prepare("SELECT id, name FROM users WHERE role = 'associate' AND id != ? ORDER BY name ASC");
                    $st2->execute([$_SESSION['user_id'] ?? 0]);
                    $teamMembers = $st2->fetchAll(\PDO::FETCH_ASSOC) ?: [];
                } catch (\Throwable $e) {}
                ?>
                <?php if (!empty($teamMembers)): ?>
                <form method="POST" action="<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>/assign">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="input-group">
                        <select class="form-select form-select-sm" name="assign_to">
                            <option value="">— Select member —</option>
                            <?php foreach ($teamMembers as $tm): ?>
                                <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-share me-1"></i>Assign</button>
                    </div>
                </form>
                <?php else: ?>
                    <p class="text-muted mb-0" style="font-size:0.85rem;">No other team members to assign to.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Visit Modal -->
<div class="modal fade" id="scheduleVisitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/associate/site-visits/schedule">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                <input type="hidden" name="visitor_name" value="<?= $leadName ?>">
                <input type="hidden" name="visitor_phone" value="<?= $leadPhone ?>">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-map-marker-alt text-primary me-2"></i>Schedule Site Visit for <?= $leadName ?></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Visit Date *</label>
                            <input type="date" class="form-control" name="visit_date" min="<?= $today ?>" required value="<?= $today ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Visit Time *</label>
                            <input type="time" class="form-control" name="visit_time" required value="10:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Duration</label>
                            <select class="form-select" name="duration">
                                <option value="30">30 min</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Colony</label>
                            <select class="form-select" name="colony_id">
                                <option value="">— Select —</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="What to show, special instructions..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-calendar-check me-1"></i> Schedule Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function changeStatus(status) {
    if (confirm('Change lead status to "' + status.replace('_', ' ') + '"?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= BASE_URL ?>/associate/leads/<?= $lead['id'] ?>/status';
        var csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = 'csrf_token'; csrf.value = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        form.appendChild(csrf);
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'status'; input.value = status;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
