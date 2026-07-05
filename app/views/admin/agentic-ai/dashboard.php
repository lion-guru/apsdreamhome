<?php
$_ss = $sys_stats ?? [];
$_aa = $recent_activity ?? [];
$_as = $agent_stats ?? [];
$_ar = $auto_reply ?? [];
$_ag = $agents ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-brain" style="color:#8b5cf6"></i> Agentic AI System</h1>
                    <small class="text-muted">Autonomous AI Agents running your company 24/7</small>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="runAllAgents(this)" class="btn btn-sm" style="background:#8b5cf6;color:#fff"><i class="fas fa-play me-1"></i> Run All Agents</button>
                    <a href="/admin/agentic-ai/conversations" class="btn btn-sm btn-outline-primary"><i class="fas fa-comments"></i> Conversations</a>
                    <a href="/admin/agentic-ai/logs" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list"></i> Logs</a>
                    <a href="/admin/agentic-ai/auto-reply" class="btn btn-sm btn-outline-success"><i class="fas fa-cog"></i> Auto-Reply</a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- System Overview -->
            <div class="row mb-4">
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon" style="background:#8b5cf6;color:#fff"><i class="fas fa-robot"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Tasks</span>
                            <span class="info-box-number"><?= (int)($_ss['today_tasks'] ?? 0) ?></span>
                            <small>Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon" style="background:#10b981;color:#fff"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Running Now</span>
                            <span class="info-box-number"><?= (int)($_ss['running_now'] ?? 0) ?></span>
                            <small>Active</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon" style="background:#ef4444;color:#fff"><i class="fas fa-exclamation"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Escalated</span>
                            <span class="info-box-number"><?= (int)($_ss['escalated'] ?? 0) ?></span>
                            <small>Needs human</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon" style="background:#3b82f6;color:#fff"><i class="fas fa-comments"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Conversations</span>
                            <span class="info-box-number"><?= (int)($_ss['today_conversations'] ?? 0) ?></span>
                            <small>Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon" style="background:#25D366;color:#fff"><i class="fab fa-whatsapp"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">WA Clicks</span>
                            <span class="info-box-number"><?= (int)($_ss['wa_clicks'] ?? 0) ?></span>
                            <small>Today</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon" style="background:#f59e0b;color:#fff"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">New Leads</span>
                            <span class="info-box-number"><?= (int)($_ss['new_leads'] ?? 0) ?></span>
                            <small>Today</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agent Cards Grid -->
            <h5 class="mb-3"><i class="fas fa-layer-group"></i> AI Agents</h5>
            <div class="row mb-4">
                <?php foreach ($_ag as $key => $agent): ?>
                <?php $stat = $_as[$key] ?? []; ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <a href="/admin/agentic-ai/agent/<?= $key ?>" class="text-decoration-none">
                        <div class="card card-outline h-100" style="border-left:4px solid <?= $agent['color'] ?>;transition:all 0.2s">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div style="width:40px;height:40px;border-radius:10px;background:<?= $agent['color'] ?>15;display:flex;align-items:center;justify-content:center;margin-right:10px">
                                        <i class="fas <?= $agent['icon'] ?>" style="color:<?= $agent['color'] ?>;font-size:18px"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" style="color:<?= $agent['color'] ?>"><?= $agent['name'] ?></h6>
                                    </div>
                                </div>
                                <p class="small text-muted mb-2"><?= $agent['description'] ?></p>
                                <div class="d-flex justify-content-between small">
                                    <span><strong><?= (int)($stat['total_tasks'] ?? 0) ?></strong> tasks</span>
                                    <span class="text-success"><strong><?= (int)($stat['completed'] ?? 0) ?></strong> done</span>
                                    <?php if (($stat['failed'] ?? 0) > 0): ?>
                                    <span class="text-danger"><strong><?= (int)($stat['failed'] ?? 0) ?></strong> failed</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($stat['last_run']): ?>
                                <div class="small text-muted mt-1"><i class="fas fa-clock"></i> Last: <?= date('M d, H:i', strtotime($stat['last_run'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <!-- Recent Activity -->
                <div class="col-lg-8">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Recent Agent Activity</h3>
                            <div class="card-tools">
                                <a href="/admin/agentic-ai/logs" class="btn btn-sm btn-outline-secondary">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($_aa)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p>No activity yet. Agents will start working once activated.</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead><tr><th>Time</th><th>Agent</th><th>Task</th><th>Status</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($_aa as $act): ?>
                                    <tr>
                                        <td class="text-muted small"><?= date('M d, H:i', strtotime($act['created_at'])) ?></td>
                                        <td>
                                            <?php $ag = $_ag[$act['agent_type']] ?? ['name' => $act['agent_type'], 'color' => '#666', 'icon' => 'fa-robot']; ?>
                                            <span style="color:<?= $ag['color'] ?>"><i class="fas <?= $ag['icon'] ?>"></i> <?= $ag['name'] ?></span>
                                        </td>
                                        <td class="small"><?= htmlspecialchars($act['task_name'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $statusColors = ['completed' => 'success', 'running' => 'info', 'failed' => 'danger', 'escalated' => 'warning', 'pending' => 'secondary'];
                                            $sc = $statusColors[$act['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $sc ?>"><?= $act['status'] ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="col-lg-4">
                    <div class="card card-outline card-purple" style="border-left-color:#8b5cf6">
                        <div class="card-header" style="background:#8b5cf610"><h3 class="card-title" style="color:#8b5cf6"><i class="fas fa-info-circle"></i> How It Works</h3></div>
                        <div class="card-body">
                            <ol class="small mb-0 pl-3">
                                <li class="mb-2"><strong>Each agent</strong> specializes in one business function</li>
                                <li class="mb-2"><strong>Cron jobs</strong> trigger agents at scheduled intervals</li>
                                <li class="mb-2"><strong>AI processes</strong> data and takes autonomous actions</li>
                                <li class="mb-2"><strong>Low confidence</strong> → escalates to human</li>
                                <li class="mb-2"><strong>Full audit</strong> trail in agent_task_logs</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card card-outline card-success">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-play-circle"></i> Quick Start</h3></div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="/admin/leads" class="list-group-item list-group-item-action"><i class="fas fa-magnet text-primary"></i> View Leads Pipeline</a>
                                <a href="/admin/bookings" class="list-group-item list-group-item-action"><i class="fas fa-handshake text-success"></i> View Bookings</a>
                                <a href="/admin/agentic-ai/conversations" class="list-group-item list-group-item-action"><i class="fas fa-comments text-info"></i> Live Conversations</a>
                                <a href="/admin/agentic-ai/logs" class="list-group-item list-group-item-action"><i class="fas fa-list text-secondary"></i> Agent Logs</a>
                                <a href="/admin/agentic-ai/auto-reply" class="list-group-item list-group-item-action"><i class="fas fa-robot text-purple"></i> Auto-Reply Config</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-purple{color:#6f42c1!important}
.card-purple{border-left-color:#8b5cf6!important}
.info-box{box-shadow:0 1px 3px rgba(0,0,0,0.08);border-radius:10px}
.info-box-icon{border-radius:10px}
</style>
<script>
function runAllAgents(btn) {
    btn.disabled = true;
    var orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Running...';
    var notif = document.createElement('div');
    notif.className = 'alert alert-info alert-dismissible fade show mt-2';
    notif.id = 'agentRunNotif';
    notif.innerHTML = '<i class="fas fa-sync fa-spin me-2"></i> Running all 8 AI agents...';
    btn.closest('.content-header').after(notif);

    fetch('/admin/agentic-ai/run-all', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            notif.className = data.success ? 'alert alert-success alert-dismissible fade show mt-2' : 'alert alert-warning alert-dismissible fade show mt-2';
            notif.innerHTML = '<i class="fas fa-' + (data.success ? 'check-circle' : 'exclamation-triangle') + ' me-2"></i>' + (data.message || 'Completed') +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            setTimeout(function() { location.reload(); }, 2000);
        })
        .catch(function(err) {
            notif.className = 'alert alert-danger alert-dismissible fade show mt-2';
            notif.innerHTML = '<i class="fas fa-times-circle me-2"></i>Error: ' + err.message;
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = orig;
        });
}
</script>
