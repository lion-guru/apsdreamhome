<?php
$page_title = $page_title ?? 'Agentic CRM AI';
$stats = $stats ?? [];
$recent_actions = $recent_actions ?? [];

$autoFollowups = (int)($stats['auto_followups'] ?? 0);
$scoreAdjustments = (int)($stats['score_adjustments'] ?? 0);
$autoAssignments = (int)($stats['auto_assignments'] ?? 0);
$insightsGenerated = (int)($stats['insights_generated'] ?? 0);
$overdueTasks = is_array($stats['overdue_leads'] ?? null) ? count($stats['overdue_leads']) : (int)($stats['overdue_leads'] ?? 0);
$hotLeads = is_array($stats['hot_leads'] ?? null) ? count($stats['hot_leads']) : 0;
$coldLeads = is_array($stats['cold_leads'] ?? null) ? ($stats['cold_leads'][0]['cnt'] ?? 0) : (int)($stats['cold_leads'] ?? 0);
$dormantLeads = is_array($stats['dormant_leads'] ?? null) ? count($stats['dormant_leads']) : 0;
$hotLeadsList = $stats['hot_leads'] ?? [];
$dormantLeadsList = $stats['dormant_leads'] ?? [];

$actionIcons = [
    'auto_followup' => ['icon' => 'fas fa-redo-alt', 'color' => '#667eea', 'bg' => '#eef0ff'],
    'score_adjustment' => ['icon' => 'fas fa-sliders-h', 'color' => '#f6993f', 'bg' => '#fff7ed'],
    'auto_assignment' => ['icon' => 'fas fa-user-plus', 'color' => '#38c172', 'bg' => '#edfcf2'],
    'insight' => ['icon' => 'fas fa-lightbulb', 'color' => '#886ab5', 'bg' => '#f3eefa'],
];
?>

<style>
.agentic-header{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px}
.agentic-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;transition:.3s;overflow:hidden}
.agentic-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
.agent-stat-card{text-align:center;padding:20px 12px;border-radius:14px;transition:.3s;border:1px solid #f0f0f5;background:#fff}
.agent-stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.06)}
.agent-stat-card .stat-icon{width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 12px;transition:.3s}
.agent-stat-card:hover .stat-icon{transform:scale(1.1)}
.agent-stat-card .stat-value{font-size:32px;font-weight:800;margin:0;line-height:1}
.agent-stat-card .stat-label{font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1.2px;margin:6px 0 0}
.agent-action-btn{border-radius:14px;text-align:center;border:2px solid #e9ecef;background:#fff;transition:.3s;padding:20px 12px;cursor:pointer;text-decoration:none;color:inherit}
.agent-action-btn:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.1);text-decoration:none;color:inherit;border-color:#667eea;background:#f8f9ff}
.agent-action-btn i{font-size:32px;margin-bottom:10px;display:block}
.agent-action-btn span{font-size:13px;font-weight:700;display:block}
.agent-action-btn small{font-size:11px;color:#888;display:block;margin-top:4px}
.action-timeline{max-height:500px;overflow-y:auto}
.action-item{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #f5f5f5;transition:.2s}
.action-item:last-child{border:none}
.action-item:hover{background:#fafafe;border-radius:8px;padding-left:8px;padding-right:8px}
.action-dot{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.action-time{font-size:11px;color:#aaa;white-space:nowrap}
.lead-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;transition:.2s}
.lead-chip:hover{transform:scale(1.03)}
.run-all-btn{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;border-radius:14px;padding:16px 32px;font-weight:800;font-size:16px;color:#fff;transition:.3s;box-shadow:0 4px 16px rgba(102,126,234,.3)}
.run-all-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(102,126,234,.4);color:#fff}
.run-all-btn:active{transform:scale(.98)}
.quick-run-form{display:inline}
.glow-pulse{animation:glowPulse 2s ease-in-out infinite}
@keyframes glowPulse{0%,100%{box-shadow:0 0 0 0 rgba(102,126,234,.4)}50%{box-shadow:0 0 20px 4px rgba(102,126,234,.2)}}
.badge-pulse{animation:badgePulse 2s ease-in-out infinite}
@keyframes badgePulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
.section-title{font-size:16px;font-weight:700;color:#333;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.section-title i{color:#667eea}
.status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px}
.status-dot.active{background:#38c172}
.status-dot.warning{background:#f6993f}
.status-dot.danger{background:#e3342f}
</style>

<!-- Header -->
<div class="agentic-header">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1 fw-bold"><i class="fas fa-robot me-2"></i>Agentic CRM AI</h2>
                <p class="mb-0 opacity-75 style-42715">Intelligent automation — follow-ups, scoring, assignment, insights</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/run-all" class="quick-run-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="run-all-btn glow-pulse"><i class="fas fa-play me-2"></i>Run All Agents</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4 style-71772">

    <!-- Agent Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon style-64551"><i class="fas fa-redo-alt"></i></div>
                <div class="stat-value style-23141"><?= $autoFollowups ?></div>
                <div class="stat-label">Follow-ups Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon style-27495"><i class="fas fa-sliders-h"></i></div>
                <div class="stat-value style-52774"><?= $scoreAdjustments ?></div>
                <div class="stat-label">Scores Adjusted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon style-54834"><i class="fas fa-user-plus"></i></div>
                <div class="stat-value style-61533"><?= $autoAssignments ?></div>
                <div class="stat-label">Auto-Assigned</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon style-33961"><i class="fas fa-lightbulb"></i></div>
                <div class="stat-value style-91868"><?= $insightsGenerated ?></div>
                <div class="stat-label">Insights Generated</div>
            </div>
        </div>
    </div>

    <!-- Alert Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon style-93721"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <div class="style-32488"><?= $overdueTasks ?></div>
                        <div class="style-21552">Overdue Tasks</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon style-71368"><i class="fas fa-fire"></i></div>
                    <div>
                        <div class="style-58900"><?= $hotLeads ?></div>
                        <div class="style-21552">Hot Leads</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon style-88061"><i class="fas fa-snowflake"></i></div>
                    <div>
                        <div class="style-13074"><?= number_format($coldLeads) ?></div>
                        <div class="style-21552">Cold Leads</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon style-5679"><i class="fas fa-bed"></i></div>
                    <div>
                        <div class="style-80383"><?= $dormantLeads ?></div>
                        <div class="style-21552">Dormant (7d+)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Agent Actions (Left) -->
        <div class="col-lg-8">
            <div class="agentic-card p-4 mb-4">
                <div class="section-title"><i class="fas fa-bolt"></i> Agent Actions</div>
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/auto-followup" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-redo-alt style-23141"></i>
                                <span>Auto Follow-Up</span>
                                <small>Create tasks for stale leads</small>
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/score-recalc" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-sliders-h style-52774"></i>
                                <span>Recalc Scores</span>
                                <small>Update all lead scores</small>
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/auto-assign" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-user-plus style-61533"></i>
                                <span>Auto Assign</span>
                                <small>Round-robin unassigned leads</small>
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/insights" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-lightbulb style-91868"></i>
                                <span>Generate Insights</span>
                                <small>AI pipeline analysis</small>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent AI Actions Timeline -->
            <div class="agentic-card p-4">
                <div class="section-title"><i class="fas fa-history"></i> Recent AI Actions (Today)</div>
                <div class="action-timeline">
                    <?php if (empty($recent_actions)): ?>
                        <div class="text-center py-4 style-1686">
                            <i class="fas fa-robot style-99737"></i>
                            <p class="mb-0">No AI actions today. Run an agent to get started.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_actions as $action):
                            $type = $action['action_type'] ?? 'info';
                            $iconData = $actionIcons[$type] ?? ['icon' => 'fas fa-cog', 'color' => '#667eea', 'bg' => '#eef0ff'];
                            $time = date('h:i A', strtotime($action['created_at'] ?? 'now'));
                        ?>
                            <div class="action-item">
                                <div class="action-dot style-18463">
                                    <i class="<?= $iconData['icon'] ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="style-59141"><?= htmlspecialchars($action['details'] ?? $type) ?></div>
                                    <div class="action-time"><?= $time ?></div>
                                </div>
                                <span class="badge style-15978"><?= str_replace('_', ' ', ucfirst($type)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Hot Leads -->
            <div class="agentic-card p-4 mb-4">
                <div class="section-title"><i class="fas fa-fire style-52774"></i> Hot Leads (Score â‰¥70)</div>
                <?php if (empty($hotLeadsList)): ?>
                    <div class="text-center py-3 style-1686">
                        <i class="fas fa-check-circle style-37281"></i>
                        <p class="mb-0 style-87981">No hot leads right now</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($hotLeadsList as $hl):
                        $score = (int)($hl['lead_score'] ?? 0);
                        $scoreColor = $score >= 90 ? '#e3342f' : ($score >= 80 ? '#f6993f' : '#38c172');
                        $initials = strtoupper(substr($hl['name'] ?? 'L', 0, 1));
                    ?>
                        <div class="lead-chip mb-2 style-97701">
                            <div class="style-18252"><?= $initials ?></div>
                            <div class="flex-grow-1 style-62036">
                                <div class="style-22898"><a href="<?= BASE_URL ?>/admin/leads/<?= $hl['id'] ?>" class="style-2116"><?= htmlspecialchars($hl['name'] ?? 'Unknown') ?></a></div>
                                <div class="style-90847"><?= htmlspecialchars($hl['phone'] ?? '') ?></div>
                            </div>
                            <div class="style-97024"><?= $score ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Dormant Leads -->
            <div class="agentic-card p-4 mb-4">
                <div class="section-title"><i class="fas fa-bed style-91868"></i> Dormant Leads (7d+)</div>
                <?php if (empty($dormantLeadsList)): ?>
                    <div class="text-center py-3 style-1686">
                        <i class="fas fa-check-circle style-37281"></i>
                        <p class="mb-0 style-87981">No dormant leads</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dormantLeadsList as $dl):
                        $days = (int)($dl['days_inactive'] ?? 0);
                        $daysColor = $days >= 30 ? '#e3342f' : ($days >= 14 ? '#f6993f' : '#886ab5');
                        $initials = strtoupper(substr($dl['name'] ?? 'L', 0, 1));
                    ?>
                        <div class="lead-chip mb-2 style-97291">
                            <div class="style-66020"><?= $initials ?></div>
                            <div class="flex-grow-1 style-62036">
                                <div class="style-22898"><a href="<?= BASE_URL ?>/admin/leads/<?= $dl['id'] ?>" class="style-2116"><?= htmlspecialchars($dl['name'] ?? 'Unknown') ?></a></div>
                                <div class="style-90847"><?= htmlspecialchars($dl['phone'] ?? '') ?></div>
                            </div>
                            <div class="style-35746"><?= $days ?>d</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Agent Status -->
            <div class="agentic-card p-4">
                <div class="section-title"><i class="fas fa-satellite-dish style-61533"></i> Agent Status</div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="style-87981"><span class="status-dot active"></span>Auto Follow-Up</span>
                        <span class="badge bg-success style-26285">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="style-87981"><span class="status-dot active"></span>Score Calculator</span>
                        <span class="badge bg-success style-26285">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="style-87981"><span class="status-dot active"></span>Auto Assignment</span>
                        <span class="badge bg-success style-26285">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="style-87981"><span class="status-dot active"></span>Insight Engine</span>
                        <span class="badge bg-success style-26285">Active</span>
                    </div>
                </div>
                <hr class="style-7292">
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/admin/crm" class="style-60431"><i class="fas fa-arrow-left me-1"></i>Back to CRM Dashboard</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.querySelectorAll('.quick-run-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Running...';
        }
    });
});
</script>
