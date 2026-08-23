<?php
$mlSupport = $mlSupport ?? new class { public function translate($s) { return $s; } public function getCurrentLanguage() { return 'EN'; } };
$aiManager = $aiManager ?? new class { public function getMode() { return 'AUTO'; } };
?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosado/drawflow/dist/drawflow.min.css" media="print" onload="this.media='all'">
<style>
    #workflowCanvas {
        background-color: #f0f2f5;
        background-image: radial-gradient(#d1d1d1 1px, transparent 1px);
        background-size: 20px 20px;
    }

    .drawflow-node {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 180px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .drawflow-node.trigger {
        border-left: 5px solid #ffc107;
    }

    .drawflow-node.agent {
        border-left: 5px solid #007bff;
    }

    .drawflow-node.database {
        border-left: 5px solid #28a745;
    }

    .drawflow-node.condition {
        border-left: 5px solid #17a2b8;
    }

    .drawflow-node.notification {
        border-left: 5px solid #dc3545;
    }

    .node-item {
        cursor: grab;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .node-item:hover {
        background: #f8f9fa;
    }

    .drawflow .connection .main-path {
        stroke: #6c757d;
        stroke-width: 3px;
    }
</style>

<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?= h($mlSupport->translate('AI Ecosystem Hub')) ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?= h($mlSupport->translate('AI Hub')) ?></li>
                </ul>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newWorkflowModal">
                    <i class="fas fa-plus me-1"></i> <?= h($mlSupport->translate('New Workflow')) ?>
                </button>
            </div>
        </div>
    </div>

    <?php if (isset($msg)) echo $msg; ?>

    <!-- Mode & Language Switcher Quick Access -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-dark text-white shadow-sm border-0">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="me-3">
                            <i class="fas fa-robot text-info me-1"></i> <?= h($mlSupport->translate('Mode')) ?>:
                            <strong class="text-uppercase" id="currentModeDisplay"><?= h($aiManager->getMode()) ?></strong>
                        </span>
                        <span>
                            <i class="fas fa-globe text-warning me-1"></i> <?= h($mlSupport->translate('Language')) ?>:
                            <strong id="currentLangDisplay" class="text-uppercase"><?= h($mlSupport->getCurrentLanguage()) ?></strong>
                        </span>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-light" onclick="toggleAIMode()"><?= h($mlSupport->translate('Switch Mode')) ?></button>
                        <button class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#langModal"><?= h($mlSupport->translate('Language')) ?></button>
                        <button class="btn btn-sm btn-outline-info" onclick="toggleNotifications()"><i class="fas fa-bell"></i> <span id="notifBadge" class="badge bg-danger d-none">!</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Notifications Panel -->
    <div id="notificationPanel" class="card shadow-lg d-none position-fixed style-87917">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><?= h($mlSupport->translate('Live AI Monitoring')) ?></h6>
            <button class="btn-close btn-close-white" onclick="toggleNotifications()"></button>
        </div>
        <div class="card-body p-0 style-61454">
            <div class="list-group list-group-flush" id="notifList">
                <div class="list-group-item small text-muted text-center py-4"><?= h($mlSupport->translate('No live activities...')) ?></div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-tabs nav-tabs-solid mb-4">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#dashboard"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#workflows"><?= h($mlSupport->translate('Workflows')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#marketing"><?= h($mlSupport->translate('Marketing')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#telecalling"><?= h($mlSupport->translate('Telecalling')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tool-directory" onclick="loadAITools()"><?= h($mlSupport->translate('Tool Directory')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#recommendations" onclick="loadRecommendations()"><?= h($mlSupport->translate('Recommendations')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ecosystem" onclick="loadEcosystem()"><?= h($mlSupport->translate('AI Ecosystem')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bot-management" onclick="loadBotSettings()"><?= h($mlSupport->translate('Bot Management')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#learning-center" onclick="loadLearningUpdates()"><?= h($mlSupport->translate('Learning Center')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#insights" onclick="loadInsights()"><?= h($mlSupport->translate('Insights & Analysis')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dev-tools"><?= h($mlSupport->translate('Dev Tools')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#health" onclick="loadHealthStatus()"><?= h($mlSupport->translate('System Health')) ?></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#settings"><?= h($mlSupport->translate('Settings')) ?></a></li>
    </ul>

    <div class="tab-content">
        <!-- Dashboard Tab -->
        <div class="tab-pane show active" id="dashboard">
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm border-0 mb-4">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Active users')) ?></h6>
                            <h3 class="fw-bold mb-0"><?= h($total_agents ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm border-0 mb-4">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Workflows')) ?></h6>
                            <h3 class="fw-bold mb-0"><?= h($active_workflows ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm border-0 mb-4">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Pending Jobs')) ?></h6>
                            <h3 class="fw-bold mb-0"><?= h($pending_jobs ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm border-0 mb-4">
                        <div class="card-body aps-cp-card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Success Rate')) ?></h6>
                            <h3 class="fw-bold mb-0">98.5%</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('AI users')) ?></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php
                                $users = $agents_list ?? [];
                                foreach ($users as $agent): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 small fw-bold"><?= h($agent['name']) ?></h6>
                                            <small class="text-muted text-uppercase"><?= h($mlSupport->translate($agent['agent_type'])) ?></small>
                                        </div>
                                        <span class="badge rounded-pill bg-<?= $agent['status'] == 'active' ? 'success' : 'secondary' ?> small"><?= h($mlSupport->translate($agent['status'])) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Recent Workflows')) ?></h5>
                            <a href="<?= BASE_URL ?>admin/workflows/list" class="btn btn-sm btn-link text-decoration-none"><?= h($mlSupport->translate('View All')) ?></a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small fw-bold"><?= h($mlSupport->translate('Name')) ?></th>
                                            <th class="small fw-bold"><?= h($mlSupport->translate('Trigger')) ?></th>
                                            <th class="small fw-bold"><?= h($mlSupport->translate('Status')) ?></th>
                                            <th class="small fw-bold"><?= h($mlSupport->translate('Last Run')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $workflows = $workflows_list ?? [];
                                        foreach ($workflows as $wf): ?>
                                            <tr>
                                                <td class="small fw-bold"><?= h($wf['name']) ?></td>
                                                <td><span class="badge bg-light text-dark small"><?= h($mlSupport->translate($wf['trigger_type'])) ?></span></td>
                                                <td>
                                                    <span class="badge rounded-pill bg-<?= $wf['is_active'] ? 'success' : 'danger' ?> small">
                                                        <?= $wf['is_active'] ? $mlSupport->translate('Active') : $mlSupport->translate('Inactive') ?>
                                                    </span>
                                                </td>
                                                <td class="small text-muted"><?= h($wf['last_run'] ?: $mlSupport->translate('Never')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Performance Analytics')) ?></h5>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <div class="style-17333">
                                <canvas id="aiPerformanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflows Tab (n8n-like) -->
        <div class="tab-pane" id="workflows">
            <div class="row">
                <div class="col-md-3">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Nodes Library')) ?></h5>
                        </div>
                        <div class="card-body p-2">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="trigger">
                                    <i class="fas fa-bolt text-warning me-2"></i> <?= h($mlSupport->translate('Trigger (Webhook)')) ?>
                                </div>
                                <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="agent">
                                    <i class="fas fa-robot text-primary me-2"></i> <?= h($mlSupport->translate('AI Agent (LLM)')) ?>
                                </div>
                                <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="database">
                                    <i class="fas fa-database text-success me-2"></i> <?= h($mlSupport->translate('DB Operation')) ?>
                                </div>
                                <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="condition">
                                    <i class="fas fa-code-branch text-info me-2"></i> <?= h($mlSupport->translate('IF Condition')) ?>
                                </div>
                                <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="notification">
                                    <i class="fas fa-envelope text-danger me-2"></i> <?= h($mlSupport->translate('Notification')) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card shadow-sm border-0 style-29486">
                        <div class="card-body p-0 position-relative">
                            <div id="workflowCanvas" class="w-100 h-100"></div>
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-success" onclick="saveWorkflow()"><i class="fas fa-save me-1"></i> <?= h($mlSupport->translate('Save')) ?></button>
                                <button class="btn btn-primary" onclick="executeWorkflow()"><i class="fas fa-play me-1"></i> <?= h($mlSupport->translate('Run')) ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marketing Automation Tab -->
        <div class="tab-pane" id="marketing">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Total Campaigns</h6><h3 class="mb-0"><?= count($campaigns_list ?? []) ?></h3></div><i class="fas fa-bullhorn fa-2x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Sent</h6><h3 class="mb-0"><?= count(array_filter($campaigns_list ?? [], fn($c) => in_array($c['status'] ?? '', ['sent','completed']))) ?></h3></div><i class="fas fa-paper-plane fa-2x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Drafts</h6><h3 class="mb-0"><?= count(array_filter($campaigns_list ?? [], fn($c) => ($c['status'] ?? '') === 'draft')) ?></h3></div><i class="fas fa-file-alt fa-2x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Total Recipients</h6><h3 class="mb-0"><?= number_format(array_sum(array_column($campaigns_list ?? [], 'total_recipients'))) ?></h3></div><i class="fas fa-users fa-2x opacity-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-bullhorn me-2"></i>Marketing Campaigns</h5>
                    <a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($campaigns_list)): ?>
                        <div class="text-center py-5"><i class="fas fa-bullhorn fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No campaigns yet</h5><p class="text-muted mb-3">Create email, SMS, WhatsApp, and push notification campaigns.</p><a href="<?= BASE_URL ?>/admin/campaigns" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create Campaign</a></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Name</th><th>Type</th><th>Audience</th><th>Status</th><th>Recipients</th><th>Sent</th><th>Opened</th><th>Created</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($campaigns_list ?? [], 0, 10) as $c): ?>
                                    <tr>
                                        <td class="fw-semibold small"><?= htmlspecialchars($c['name'] ?? '') ?></td>
                                        <td><span class="badge bg-<?= $c['type'] === 'email' ? 'primary' : ($c['type'] === 'sms' ? 'success' : ($c['type'] === 'whatsapp' ? 'success' : 'info')) ?>"><?= ucfirst($c['type']) ?></span></td>
                                        <td class="small"><?= htmlspecialchars($c['target_audience'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= $c['status'] === 'sent' || $c['status'] === 'completed' ? 'success' : ($c['status'] === 'draft' ? 'secondary' : ($c['status'] === 'sending' ? 'warning' : 'info')) ?>"><?= ucfirst($c['status']) ?></span></td>
                                        <td><?= number_format($c['total_recipients'] ?? 0) ?></td>
                                        <td><?= number_format($c['sent_count'] ?? 0) ?></td>
                                        <td><?= number_format($c['opened_count'] ?? 0) ?></td>
                                        <td class="small text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Telecalling Tab -->
        <div class="tab-pane" id="telecalling">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Active Channels</h6><h3 id="tc-channels" class="mb-0">—</h3></div><i class="fas fa-phone fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Pending Calls</h6><h3 id="tc-pending" class="mb-0">—</h3></div><i class="fas fa-clock fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Today's Calls</h6><h3 id="tc-today" class="mb-0">—</h3></div><i class="fas fa-calendar-day fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Success Rate</h6><h3 id="tc-rate" class="mb-0">—</h3></div><i class="fas fa-chart-line fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-phone-alt me-2"></i>AI Telecalling Dashboard</h5>
                    <a href="<?= BASE_URL ?>/admin/ai-calling" class="btn btn-sm btn-primary">Open Full Dashboard</a>
                </div>
                <div class="card-body" id="tc-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading telecalling data...</p></div>
                </div>
            </div>
        </div>

        <!-- Tool Directory Tab -->
        <div class="tab-pane" id="tool-directory">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-tools me-2"></i>AI Tool Directory</h5>
                </div>
                <div class="card-body" id="td-content">
                    <div class="row g-3">
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-brain text-primary me-2"></i>Lead Scoring</h6><p class="small text-muted mb-2">AI-powered lead qualification and scoring</p><a href="<?= BASE_URL ?>/admin/ai-system/qualifier" class="btn btn-sm btn-outline-primary">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-chart-line text-success me-2"></i>Market Intelligence</h6><p class="small text-muted mb-2">Price trends, demand analysis, colony performance</p><a href="<?= BASE_URL ?>/admin/ai/market_report" class="btn btn-sm btn-outline-success">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-phone-rotary text-warning me-2"></i>Auto Dialer</h6><p class="small text-muted mb-2">Automated calling with AI conversation</p><a href="<?= BASE_URL ?>/admin/ai-calling/auto-dialer" class="btn btn-sm btn-outline-warning">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-robot text-info me-2"></i>Agentic CRM</h6><p class="small text-muted mb-2">Auto follow-up, score recalc, auto-assign</p><a href="<?= BASE_URL ?>/admin/crm/agentic" class="btn btn-sm btn-outline-info">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-comments text-danger me-2"></i>Chatbot Training</h6><p class="small text-muted mb-2">Train chatbot responses and intents</p><a href="<?= BASE_URL ?>/admin/chatbot/train" class="btn btn-sm btn-outline-danger">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-file-alt text-secondary me-2"></i>AI Training</h6><p class="small text-muted mb-2">Voice models, scripts, intents</p><a href="<?= BASE_URL ?>/admin/ai-calling/training" class="btn btn-sm btn-outline-secondary">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-home text-primary me-2"></i>Property Recommendations</h6><p class="small text-muted mb-2">AI matching leads to properties</p><a href="<?= BASE_URL ?>/admin/ai/property_recommendations" class="btn btn-sm btn-outline-primary">Open</a></div></div></div>
                        <div class="col-md-4"><div class="card border h-100"><div class="card-body"><h6><i class="fas fa-tachometer-alt text-success me-2"></i>AI System Dashboard</h6><p class="small text-muted mb-2">Unified AI engine overview</p><a href="<?= BASE_URL ?>/admin/ai-system" class="btn btn-sm btn-outline-success">Open</a></div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations Tab -->
        <div class="tab-pane" id="recommendations">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-lightbulb me-2"></i>AI Recommendations</h5>
                </div>
                <div class="card-body" id="rec-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Analyzing data for recommendations...</p></div>
                </div>
            </div>
        </div>

        <!-- AI Ecosystem Tab -->
        <div class="tab-pane" id="ecosystem">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-project-diagram me-2"></i>AI Ecosystem Map</h5>
                </div>
                <div class="card-body" id="eco-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading ecosystem data...</p></div>
                </div>
            </div>
        </div>

        <!-- Bot Management Tab -->
        <div class="tab-pane" id="bot-management">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Bot Status</h6><h3 id="bot-status" class="mb-0 small">Active</h3></div><i class="fas fa-robot fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Conversations Today</h6><h3 id="bot-convos" class="mb-0">—</h3></div><i class="fas fa-comments fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Satisfaction</h6><h3 id="bot-satisfaction" class="mb-0">—</h3></div><i class="fas fa-smile fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm border-0">
                        <div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-uppercase small mb-1">Training Items</h6><h3 id="bot-training" class="mb-0">—</h3></div><i class="fas fa-graduation-cap fa-2x opacity-50"></i></div></div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-robot me-2"></i>Bot Management</h5>
                    <a href="<?= BASE_URL ?>/admin/ai-chatbot" class="btn btn-sm btn-primary">Open Chatbot Admin</a>
                </div>
                <div class="card-body" id="bot-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading bot data...</p></div>
                </div>
            </div>
        </div>

        <!-- Learning Center Tab -->
        <div class="tab-pane" id="learning-center">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-graduation-cap me-2"></i>AI Learning Center</h5>
                    <a href="<?= BASE_URL ?>/admin/ai-calling/training" class="btn btn-sm btn-primary">Open Training</a>
                </div>
                <div class="card-body" id="learn-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading learning data...</p></div>
                </div>
            </div>
        </div>

        <!-- Insights & Analysis Tab -->
        <div class="tab-pane" id="insights">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-chart-bar me-2"></i>Insights & Analysis</h5>
                </div>
                <div class="card-body" id="insights-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Generating insights...</p></div>
                </div>
            </div>
        </div>

        <!-- Dev Tools Tab -->
        <div class="tab-pane" id="dev-tools">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-code me-2"></i>Developer Tools</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><a href="<?= BASE_URL ?>/admin/ai-system/health" class="btn btn-outline-primary w-100 py-3"><i class="fas fa-heartbeat mb-2 d-block style-41417"></i>System Health Check</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>/admin/ai-calling/call-logs" class="btn btn-outline-success w-100 py-3"><i class="fas fa-list mb-2 d-block style-41417"></i>Call Logs</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>/admin/ai-calling/call-analytics" class="btn btn-outline-info w-100 py-3"><i class="fas fa-chart-pie mb-2 d-block style-41417"></i>Call Analytics</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>/admin/crm/dedup" class="btn btn-outline-warning w-100 py-3"><i class="fas fa-clone mb-2 d-block style-41417"></i>Lead Deduplication</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>/admin/crm/role-dashboard" class="btn btn-outline-secondary w-100 py-3"><i class="fas fa-users-cog mb-2 d-block style-41417"></i>CRM Role Dashboard</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>/admin/ai/market_report" class="btn btn-outline-danger w-100 py-3"><i class="fas fa-chart-area mb-2 d-block style-41417"></i>Market Report</a></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health Tab -->
        <div class="tab-pane" id="health">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadHealthStatus()"><i class="fas fa-sync me-1"></i>Refresh</button>
                </div>
                <div class="card-body" id="health-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Checking system health...</p></div>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane" id="settings">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><i class="fas fa-cog me-2"></i>AI Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6>AI Engine Mode</h6>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="aiMode" id="modeAuto" checked>
                                <label class="btn btn-outline-primary" for="modeAuto">Auto</label>
                                <input type="radio" class="btn-check" name="aiMode" id="modeManual">
                                <label class="btn btn-outline-secondary" for="modeManual">Manual</label>
                                <input type="radio" class="btn-check" name="aiMode" id="modeOff">
                                <label class="btn btn-outline-danger" for="modeOff">Off</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Quick Actions</h6>
                            <a href="<?= BASE_URL ?>/admin/ai-system" class="btn btn-outline-primary me-2"><i class="fas fa-robot me-1"></i>AI System</a>
                            <a href="<?= BASE_URL ?>/admin/ai-calling/training" class="btn btn-outline-success me-2"><i class="fas fa-graduation-cap me-1"></i>Training</a>
                            <a href="<?= BASE_URL ?>/admin/crm/agentic" class="btn btn-outline-info"><i class="fas fa-magic me-1"></i>Agentic CRM</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<!-- New Workflow Modal -->
<div class="modal fade" id="newWorkflowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= h($mlSupport->translate('Create New Workflow')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL ?>admin/workflows/create" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="create_workflow" value="1">
                    <div class="mb-3">
                        <label class="form-label"><?= h($mlSupport->translate('Workflow Name')) ?></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= h($mlSupport->translate('Description')) ?></label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= h($mlSupport->translate('Trigger Type')) ?></label>
                        <select name="trigger_type" class="form-select">
                            <option value="webhook"><?= h($mlSupport->translate('Webhook')) ?></option>
                            <option value="schedule"><?= h($mlSupport->translate('Schedule')) ?></option>
                            <option value="event"><?= h($mlSupport->translate('System Event')) ?></option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><?= h($mlSupport->translate('Create')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script async src="https://cdn.jsdelivr.net/gh/jerosado/drawflow/dist/drawflow.min.js"></script>
<script async src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
    function initLibraries() {
        // Initialize Drawflow
        if (typeof Drawflow !== 'undefined') {
            var canvas = document.getElementById("workflowCanvas");
            if (canvas) {
                const editor = new Drawflow(canvas);
                editor.reroute = true;
                editor.start();
            }
        }
        // Initialize Chart.js
        if (typeof Chart !== 'undefined') {
            const canvas = document.getElementById('aiPerformanceChart');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                const aiChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($chart_labels ?? []) ?>,
                        datasets: [{
                            label: 'Successful Executions',
                            data: <?= json_encode($chart_success ?? []) ?>,
                            borderColor: '#28a745',
                            tension: 0.4
                        }, {
                            label: 'Failed Executions',
                            data: <?= json_encode($chart_failed ?? []) ?>,
                            borderColor: '#dc3545',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        }
    }
    // Retry until libraries load
    var initRetries = 0;
    function tryInit() {
        if (typeof Drawflow !== 'undefined' || typeof Chart !== 'undefined' || initRetries > 20) {
            initLibraries();
        } else {
            initRetries++;
            setTimeout(tryInit, 500);
        }
    }
    tryInit();

    function toggleAIMode() {
        showToast('AI mode toggle — use Settings tab', 'info');
    }

    function toggleNotifications() {
        const panel = document.getElementById('notificationPanel');
        if (panel) panel.classList.toggle('d-none');
    }

    function showToast(msg, type) {
        var toast = document.createElement('div');
        toast.className = 'alert alert-' + (type || 'info') + ' position-fixed top-0 end-0 m-3 shadow';
        toast.style.zIndex = 9999;
        toast.innerHTML = '<strong>' + msg + '</strong>';
        document.body.appendChild(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }

    function fetchTabData(url, targetId, transform) {
        var el = document.getElementById(targetId);
        if (!el || el.dataset.loaded === '1') return;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) {
                if (transform) { el.innerHTML = transform(html); } else { el.innerHTML = html; }
                el.dataset.loaded = '1';
            })
            .catch(function() {
                el.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>Failed to load. <a href="' + url + '" target="_blank">Open in new tab</a></div>';
                el.dataset.loaded = '1';
            });
    }

    function loadAITools() { /* rendered server-side */ }

    function loadRecommendations() {
        fetchTabData('<?= BASE_URL ?>/admin/ai-system/qualifier', 'rec-content', function(html) {
            return '<div class="alert alert-info">AI Lead Qualifier data loaded. <a href="<?= BASE_URL ?>/admin/ai-system/qualifier" target="_blank">Open full page</a></div>' + extractContent(html);
        });
    }

    function loadEcosystem() {
        var el = document.getElementById('eco-content');
        if (!el || el.dataset.loaded === '1') return;
        el.innerHTML = '<div class="row g-3">' +
            '<div class="col-md-4"><div class="card border-success h-100"><div class="card-body text-center"><i class="fas fa-brain fa-2x text-success mb-2"></i><h6>Lead Qualifier</h6><p class="small text-muted">Auto-qualifies leads 24/7</p><span class="badge bg-success">Active</span></div></div></div>' +
            '<div class="col-md-4"><div class="card border-primary h-100"><div class="card-body text-center"><i class="fas fa-home fa-2x text-primary mb-2"></i><h6>Property Matchmaker</h6><p class="small text-muted">Matches leads to plots</p><span class="badge bg-success">Active</span></div></div></div>' +
            '<div class="col-md-4"><div class="card border-info h-100"><div class="card-body text-center"><i class="fas fa-chart-line fa-2x text-info mb-2"></i><h6>Market Intelligence</h6><p class="small text-muted">Price trends & analysis</p><span class="badge bg-success">Active</span></div></div></div>' +
            '<div class="col-md-4"><div class="card border-warning h-100"><div class="card-body text-center"><i class="fas fa-phone-alt fa-2x text-warning mb-2"></i><h6>Auto Dialer</h6><p class="small text-muted">AI voice calling</p><span class="badge bg-warning">Ready</span></div></div></div>' +
            '<div class="col-md-4"><div class="card border-danger h-100"><div class="card-body text-center"><i class="fas fa-robot fa-2x text-danger mb-2"></i><h6>Chatbot</h6><p class="small text-muted">Customer support AI</p><span class="badge bg-success">Active</span></div></div></div>' +
            '<div class="col-md-4"><div class="card border-secondary h-100"><div class="card-body text-center"><i class="fas fa-calendar-check fa-2x text-secondary mb-2"></i><h6>Smart Scheduler</h6><p class="small text-muted">Site visit optimization</p><span class="badge bg-success">Active</span></div></div></div>' +
            '</div>';
        el.dataset.loaded = '1';
    }

    function loadBotSettings() {
        fetchTabData('<?= BASE_URL ?>/admin/ai-chatbot', 'bot-content', function(html) {
            return extractContent(html);
        });
    }

    function loadLearningUpdates() {
        fetchTabData('<?= BASE_URL ?>/admin/ai-calling/training', 'learn-content', function(html) {
            return extractContent(html);
        });
    }

    function loadInsights() {
        fetchTabData('<?= BASE_URL ?>/admin/ai/market_report', 'insights-content', function(html) {
            return extractContent(html);
        });
    }

    function loadHealthStatus() {
        var el = document.getElementById('health-content');
        if (!el) return;
        el.dataset.loaded = '';
        el.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Checking services...</p></div>';
        fetch('<?= BASE_URL ?>/admin/ai-calling/health', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.text(); })
            .then(function(html) { el.innerHTML = extractContent(html); el.dataset.loaded = '1'; })
            .catch(function() { el.innerHTML = '<div class="alert alert-danger">Health check failed. <a href="<?= BASE_URL ?>/admin/ai-calling/health" target="_blank">Open directly</a></div>'; el.dataset.loaded = '1'; });
    }

    function extractContent(html) {
        var div = document.createElement('div');
        div.innerHTML = html;
        var main = div.querySelector('.container-fluid') || div.querySelector('.main-content') || div.querySelector('#content') || div;
        return main.innerHTML;
    }

    function saveWorkflow() { showToast('Use the Workflows tab to manage workflows', 'info'); }
    function executeWorkflow() { showToast('Select a workflow from the list and click Run', 'info'); }
</script>