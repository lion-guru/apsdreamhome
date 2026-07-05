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
                <p class="mb-0 opacity-75" style="font-size:14px">Intelligent automation — follow-ups, scoring, assignment, insights</p>
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

<div class="container-fluid px-4" style="margin-top:-12px">

    <!-- Agent Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon" style="background:#eef0ff;color:#667eea"><i class="fas fa-redo-alt"></i></div>
                <div class="stat-value" style="color:#667eea"><?= $autoFollowups ?></div>
                <div class="stat-label">Follow-ups Today</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon" style="background:#fff7ed;color:#f6993f"><i class="fas fa-sliders-h"></i></div>
                <div class="stat-value" style="color:#f6993f"><?= $scoreAdjustments ?></div>
                <div class="stat-label">Scores Adjusted</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon" style="background:#edfcf2;color:#38c172"><i class="fas fa-user-plus"></i></div>
                <div class="stat-value" style="color:#38c172"><?= $autoAssignments ?></div>
                <div class="stat-label">Auto-Assigned</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="agent-stat-card">
                <div class="stat-icon" style="background:#f3eefa;color:#886ab5"><i class="fas fa-lightbulb"></i></div>
                <div class="stat-value" style="color:#886ab5"><?= $insightsGenerated ?></div>
                <div class="stat-label">Insights Generated</div>
            </div>
        </div>
    </div>

    <!-- Alert Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#e3342f15;color:#e3342f;width:48px;height:48px;border-radius:12px;font-size:20px"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#e3342f"><?= $overdueTasks ?></div>
                        <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px">Overdue Tasks</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#f6993f15;color:#f6993f;width:48px;height:48px;border-radius:12px;font-size:20px"><i class="fas fa-fire"></i></div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#f6993f"><?= $hotLeads ?></div>
                        <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px">Hot Leads</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#667eea15;color:#667eea;width:48px;height:48px;border-radius:12px;font-size:20px"><i class="fas fa-snowflake"></i></div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#667eea"><?= number_format($coldLeads) ?></div>
                        <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px">Cold Leads</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="agentic-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#886ab515;color:#886ab5;width:48px;height:48px;border-radius:12px;font-size:20px"><i class="fas fa-bed"></i></div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#886ab5"><?= $dormantLeads ?></div>
                        <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:1px">Dormant (7d+)</div>
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
                                <i class="fas fa-redo-alt" style="color:#667eea"></i>
                                <span>Auto Follow-Up</span>
                                <small>Create tasks for stale leads</small>
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/score-recalc" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-sliders-h" style="color:#f6993f"></i>
                                <span>Recalc Scores</span>
                                <small>Update all lead scores</small>
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/auto-assign" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-user-plus" style="color:#38c172"></i>
                                <span>Auto Assign</span>
                                <small>Round-robin unassigned leads</small>
                            </button>
                        </form>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <form method="POST" action="<?= BASE_URL ?>/admin/crm/agentic/insights" class="quick-run-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="agent-action-btn w-100">
                                <i class="fas fa-lightbulb" style="color:#886ab5"></i>
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
                        <div class="text-center py-4" style="color:#aaa">
                            <i class="fas fa-robot" style="font-size:48px;opacity:.3;margin-bottom:12px;display:block"></i>
                            <p class="mb-0">No AI actions today. Run an agent to get started.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_actions as $action):
                            $type = $action['action_type'] ?? 'info';
                            $iconData = $actionIcons[$type] ?? ['icon' => 'fas fa-cog', 'color' => '#667eea', 'bg' => '#eef0ff'];
                            $time = date('h:i A', strtotime($action['created_at'] ?? 'now'));
                        ?>
                            <div class="action-item">
                                <div class="action-dot" style="background:<?= $iconData['bg'] ?>;color:<?= $iconData['color'] ?>">
                                    <i class="<?= $iconData['icon'] ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div style="font-size:13px;font-weight:600;color:#333"><?= htmlspecialchars($action['details'] ?? $type) ?></div>
                                    <div class="action-time"><?= $time ?></div>
                                </div>
                                <span class="badge" style="background:<?= $iconData['bg'] ?>;color:<?= $iconData['color'] ?>;font-size:11px"><?= str_replace('_', ' ', ucfirst($type)) ?></span>
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
                <div class="section-title"><i class="fas fa-fire" style="color:#f6993f"></i> Hot Leads (Score ≥70)</div>
                <?php if (empty($hotLeadsList)): ?>
                    <div class="text-center py-3" style="color:#aaa">
                        <i class="fas fa-check-circle" style="font-size:32px;opacity:.3;margin-bottom:8px;display:block"></i>
                        <p class="mb-0" style="font-size:13px">No hot leads right now</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($hotLeadsList as $hl):
                        $score = (int)($hl['lead_score'] ?? 0);
                        $scoreColor = $score >= 90 ? '#e3342f' : ($score >= 80 ? '#f6993f' : '#38c172');
                        $initials = strtoupper(substr($hl['name'] ?? 'L', 0, 1));
                    ?>
                        <div class="lead-chip mb-2" style="background:<?= $scoreColor ?>10;border:1px solid <?= $scoreColor ?>30;width:100%">
                            <div style="width:32px;height:32px;border-radius:50%;background:<?= $scoreColor ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0"><?= $initials ?></div>
                            <div class="flex-grow-1" style="min-width:0">
                                <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><a href="<?= BASE_URL ?>/admin/leads/<?= $hl['id'] ?>" style="color:#333;text-decoration:none"><?= htmlspecialchars($hl['name'] ?? 'Unknown') ?></a></div>
                                <div style="font-size:11px;color:#888"><?= htmlspecialchars($hl['phone'] ?? '') ?></div>
                            </div>
                            <div style="font-size:16px;font-weight:800;color:<?= $scoreColor ?>;flex-shrink:0"><?= $score ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Dormant Leads -->
            <div class="agentic-card p-4 mb-4">
                <div class="section-title"><i class="fas fa-bed" style="color:#886ab5"></i> Dormant Leads (7d+)</div>
                <?php if (empty($dormantLeadsList)): ?>
                    <div class="text-center py-3" style="color:#aaa">
                        <i class="fas fa-check-circle" style="font-size:32px;opacity:.3;margin-bottom:8px;display:block"></i>
                        <p class="mb-0" style="font-size:13px">No dormant leads</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dormantLeadsList as $dl):
                        $days = (int)($dl['days_inactive'] ?? 0);
                        $daysColor = $days >= 30 ? '#e3342f' : ($days >= 14 ? '#f6993f' : '#886ab5');
                        $initials = strtoupper(substr($dl['name'] ?? 'L', 0, 1));
                    ?>
                        <div class="lead-chip mb-2" style="background:<?= $daysColor ?>10;border:1px solid <?= $daysColor ?>30;width:100%">
                            <div style="width:32px;height:32px;border-radius:50%;background:<?= $daysColor ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0"><?= $initials ?></div>
                            <div class="flex-grow-1" style="min-width:0">
                                <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><a href="<?= BASE_URL ?>/admin/leads/<?= $dl['id'] ?>" style="color:#333;text-decoration:none"><?= htmlspecialchars($dl['name'] ?? 'Unknown') ?></a></div>
                                <div style="font-size:11px;color:#888"><?= htmlspecialchars($dl['phone'] ?? '') ?></div>
                            </div>
                            <div style="font-size:13px;font-weight:700;color:<?= $daysColor ?>;flex-shrink:0"><?= $days ?>d</div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Agent Status -->
            <div class="agentic-card p-4">
                <div class="section-title"><i class="fas fa-satellite-dish" style="color:#38c172"></i> Agent Status</div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="status-dot active"></span>Auto Follow-Up</span>
                        <span class="badge bg-success" style="font-size:11px">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="status-dot active"></span>Score Calculator</span>
                        <span class="badge bg-success" style="font-size:11px">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="status-dot active"></span>Auto Assignment</span>
                        <span class="badge bg-success" style="font-size:11px">Active</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="font-size:13px"><span class="status-dot active"></span>Insight Engine</span>
                        <span class="badge bg-success" style="font-size:11px">Active</span>
                    </div>
                </div>
                <hr style="margin:16px 0;border-color:#f0f0f5">
                <div class="text-center">
                    <a href="<?= BASE_URL ?>/admin/crm" style="font-size:13px;color:#667eea;text-decoration:none;font-weight:600"><i class="fas fa-arrow-left me-1"></i>Back to CRM Dashboard</a>
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
