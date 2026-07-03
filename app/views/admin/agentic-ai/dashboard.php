<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-brain text-teal"></i> Agentic AI <small class="text-muted">Auto-Reply Agent System</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                        <li class="breadcrumb-item active">Agentic AI</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card card-outline card-teal">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?= (int)($stats['active'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">Active Chats</p>
                                </div>
                                <i class="fas fa-comments fa-2x text-teal opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?= (int)($stats['resolved'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">Resolved Today</p>
                                </div>
                                <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?= (int)($stats['wa_clicks'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">WhatsApp Clicks</p>
                                </div>
                                <i class="fab fa-whatsapp fa-2x text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-outline card-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0"><?= (int)($stats['new_leads'] ?? 0) ?></h3>
                                    <p class="text-muted mb-0">New Leads Today</p>
                                </div>
                                <i class="fas fa-user-plus fa-2x text-warning opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Active Conversations -->
                <div class="col-lg-8">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-comments"></i> Active Conversations</h3>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()"><i class="fas fa-sync"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($conversations)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p>No active conversations. Customers will appear here when they start chatting.</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead><tr><th>Customer</th><th>Channel</th><th>Last Message</th><th>Status</th><th></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($conversations as $conv): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($conv['lead_name'] ?? 'Guest') ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($conv['lead_phone'] ?? '') ?></small>
                                        </td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($conv['channel'] ?? 'chatbot') ?></span></td>
                                        <td><small><?= htmlspecialchars(mb_substr($conv['last_message'] ?? '', 0, 60)) ?></small></td>
                                        <td>
                                            <?php if ($conv['agent_id'] == ($agent_id ?? 0)): ?>
                                            <span class="badge badge-success">Mine</span>
                                            <?php elseif ($conv['agent_id']): ?>
                                            <span class="badge badge-secondary">Assigned</span>
                                            <?php else: ?>
                                            <span class="badge badge-warning">Unclaimed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$conv['agent_id']): ?>
                                            <button class="btn btn-xs btn-teal" onclick="claimConv(<?= $conv['id'] ?>)"><i class="fas fa-hand-paper"></i> Claim</button>
                                            <?php else: ?>
                                            <a href="/admin/agentic-ai/conversation/<?= $conv['id'] ?>" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a>
                                            <?php endif; ?>
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

                <!-- Auto-Reply Status -->
                <div class="col-lg-4">
                    <div class="card card-outline <?= !empty($auto_reply['auto_reply_enabled']) ? 'card-success' : 'card-secondary' ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-robot"></i> Auto-Reply</h3>
                        </div>
                        <div class="card-body text-center">
                            <?php if (!empty($auto_reply['auto_reply_enabled'])): ?>
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                            <h5 class="text-success">ACTIVE</h5>
                            <p class="text-muted small">
                                Business hours: <?= htmlspecialchars($auto_reply['business_hours_start'] ?? '9:00') ?> - <?= htmlspecialchars($auto_reply['business_hours_end'] ?? '19:00') ?>
                            </p>
                            <p class="small text-muted">
                                Max auto-replies: <?= (int)($auto_reply['max_auto_replies'] ?? 5) ?> before human handoff
                            </p>
                            <?php else: ?>
                            <div class="mb-3">
                                <i class="fas fa-pause-circle fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">INACTIVE</h5>
                            <p class="text-muted small">Auto-reply is turned off. Customers will wait for human agent.</p>
                            <?php endif; ?>
                            <a href="/admin/agentic-ai/auto-reply" class="btn btn-sm btn-outline-teal mt-2"><i class="fas fa-cog"></i> Settings</a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3></div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="/admin/agentic-ai/auto-reply" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cog text-teal"></i> Auto-Reply Settings
                                </a>
                                <a href="/admin/leads" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users text-info"></i> View All Leads
                                </a>
                                <a href="/admin/voice-agents" class="list-group-item list-group-item-action">
                                    <i class="fas fa-phone text-purple"></i> Voice Agents
                                </a>
                                <a href="/admin/sim-calling" class="list-group-item list-group-item-action">
                                    <i class="fas fa-phone-volume text-success"></i> SIM Calling
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-teal{color:#0d9488!important}
.btn-teal{background:#0d9488;color:#fff;border-color:#0d9488}
.btn-teal:hover{background:#0f766e;color:#fff}
.text-purple{color:#6f42c1!important}
</style>

<script>
function claimConv(id) {
    fetch('/admin/agentic-ai/api/claim', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({conversation_id: id})
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.success) location.reload();
        else alert('Failed to claim');
    });
}
</script>
