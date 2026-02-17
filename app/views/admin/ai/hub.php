<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/jerosado/drawflow/dist/drawflow.min.css">
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
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
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
    <div id="notificationPanel" class="card shadow-lg d-none position-fixed" style="top: 150px; right: 20px; width: 300px; z-index: 1050;">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><?= h($mlSupport->translate('Live AI Monitoring')) ?></h6>
            <button class="btn-close btn-close-white" onclick="toggleNotifications()"></button>
        </div>
        <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
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
                        <div class="card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Active Agents')) ?></h6>
                            <h3 class="fw-bold mb-0"><?= h($total_agents ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Workflows')) ?></h6>
                            <h3 class="fw-bold mb-0"><?= h($active_workflows ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Pending Jobs')) ?></h6>
                            <h3 class="fw-bold mb-0"><?= h($pending_jobs ?? 0) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm border-0 mb-4">
                        <div class="card-body">
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
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('AI Agents')) ?></h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php
                                $agents = $agents_list ?? [];
                                foreach ($agents as $agent): ?>
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
                            <a href="#" class="btn btn-sm btn-link text-decoration-none"><?= h($mlSupport->translate('View All')) ?></a>
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
                        <div class="card-body">
                            <div style="height: 200px;">
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
                    <div class="card shadow-sm border-0" style="height: 600px;">
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

        <!-- Other tabs (placeholders for now) -->
        <div class="tab-pane" id="marketing">
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <h5 class="text-muted"><?= h($mlSupport->translate('Marketing Automation Module - Coming Soon')) ?></h5>
            </div>
        </div>

        <!-- ... (Other tabs) ... -->

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
                <form action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
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

<script src="https://cdn.jsdelivr.net/gh/jerosado/drawflow/dist/drawflow.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Drawflow
    var id = document.getElementById("workflowCanvas");
    const editor = new Drawflow(id);
    editor.reroute = true;
    editor.start();

    // Initialize Chart.js
    const ctx = document.getElementById('aiPerformanceChart').getContext('2d');
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

    function toggleAIMode() {
        // Implement AJAX to toggle mode
        alert('Mode toggle implementation pending');
    }

    function toggleNotifications() {
        const panel = document.getElementById('notificationPanel');
        panel.classList.toggle('d-none');
    }
</script>