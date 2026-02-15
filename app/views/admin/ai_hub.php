<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/ai/AIManager.php';
require_once __DIR__ . '/../includes/ai/AIToolsManager.php';
require_once __DIR__ . '/../includes/ai/AIEcosystemManager.php';
require_once __DIR__ . '/../includes/MultiLanguageSupport.php';
require_once __DIR__ . '/../includes/log_admin_activity.php';

$page_title = "AI Agent Hub";
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';

$db = \App\Core\App::database();
$aiManager = new AIManager($db);
$toolsManager = new AIToolsManager($db);
$ecoManager = new AIEcosystemManager($db);
$mlSupport = new MultiLanguageSupport();

// Update mode if changed in session
if (isset($_SESSION['ai_mode'])) {
    $aiManager->setMode($_SESSION['ai_mode']);
}

$currentLang = $mlSupport->getCurrentLanguage();
$langName = 'English';
$supportedLangs = $mlSupport->getSupportedLanguages();
foreach ($supportedLangs as $l) {
    if ($l['code'] === $currentLang) {
        $langName = $l['native_name'];
        break;
    }
}

$total_agents = $db->fetchOne("SELECT COUNT(*) as count FROM ai_agents")['count'] ?? 0;

$active_workflows = $db->fetchOne("SELECT COUNT(*) as count FROM ai_workflows WHERE is_active=1")['count'] ?? 0;

$total_executions = $db->fetchOne("SELECT COUNT(*) as count FROM ai_agent_logs")['count'] ?? 0;

$avg_latency = $db->fetchOne("SELECT AVG(execution_time_ms) as avg FROM ai_agent_logs")['avg'] ?: 0;

$pending_jobs = $db->fetchOne("SELECT COUNT(*) as count FROM ai_jobs WHERE status='pending'")['count'] ?? 0;

// Data for Chart (Last 7 Days)
$chart_labels = [];
$chart_success = [];
$chart_failed = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('D', strtotime($date));

    $status_success = 'success';
    $success = $db->fetchOne("SELECT COUNT(*) as count FROM ai_agent_logs WHERE DATE(created_at) = :date AND status = :status", [
        'date' => $date,
        'status' => $status_success
    ])['count'] ?? 0;

    $status_failed = 'failed';
    $failed = $db->fetchOne("SELECT COUNT(*) as count FROM ai_agent_logs WHERE DATE(created_at) = :date AND status = :status", [
        'date' => $date,
        'status' => $status_failed
    ])['count'] ?? 0;

    $chart_success[] = $success;
    $chart_failed[] = $failed;
}

// Handle Workflow Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_workflow'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $msg = '<div class="alert alert-danger">' . $mlSupport->translate('Security validation failed. Please try again.') . '</div>';
    } else {
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $trigger = $_POST['trigger_type'] ?? '';

        $actions = [
            [
                'agent_id' => $_POST['agent_id'] ?? '',
                'task_type' => $_POST['task_type'] ?? '',
                'config' => [],
                'stop_on_failure' => true
            ]
        ];
        $actions_json = json_encode($actions);

        $result = $db->execute("INSERT INTO ai_workflows (name, description, trigger_type, actions, is_active) VALUES (:name, :description, :trigger_type, :actions, 1)", [
            'name' => $name,
            'description' => $desc,
            'trigger_type' => $trigger,
            'actions' => $actions_json
        ]);
        if ($result) {
            $msg = '<div class="alert alert-success">' . $mlSupport->translate('Workflow created successfully!') . '</div>';
            if (function_exists('log_admin_activity')) {
                log_admin_activity($_SESSION['admin_id'], 'create_ai_workflow', "Created workflow: $name");
            }
        } else {
            $msg = '<div class="alert alert-danger">' . $mlSupport->translate('Error creating workflow') . '</div>';
        }
    }
}
?>

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

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?= h($mlSupport->translate('AI Ecosystem Hub')) ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
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
                                <strong id="currentLangDisplay" class="text-uppercase"><?= h($currentLang) ?> (<?= h($langName) ?>)</strong>
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
                                <h3 class="fw-bold mb-0"><?= h($total_agents) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Workflows')) ?></h6>
                                <h3 class="fw-bold mb-0"><?= h($active_workflows) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark shadow-sm border-0 mb-4">
                            <div class="card-body">
                                <h6 class="text-uppercase small mb-2"><?= h($mlSupport->translate('Pending Jobs')) ?></h6>
                                <h3 class="fw-bold mb-0"><?= h($pending_jobs) ?></h3>
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
                                    $agents = $db->fetchAll("SELECT * FROM ai_agents");
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
                                            $workflows = $db->fetchAll("SELECT * FROM ai_workflows LIMIT 5");
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
                                        <i class="fas fa-bell text-danger me-2"></i> <?= h($mlSupport->translate('Send Notification')) ?>
                                    </div>
                                    <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="telecalling">
                                        <i class="fas fa-phone-alt text-dark me-2"></i> <?= h($mlSupport->translate('AI Telecall')) ?>
                                    </div>
                                    <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="sms">
                                        <i class="fas fa-sms text-info me-2"></i> <?= h($mlSupport->translate('Send SMS')) ?>
                                    </div>
                                    <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="calendar">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i> <?= h($mlSupport->translate('Calendar Event')) ?>
                                    </div>
                                    <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="payment">
                                        <i class="fas fa-credit-card text-success me-2"></i> <?= h($mlSupport->translate('Payment Link')) ?>
                                    </div>
                                    <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="email">
                                        <i class="fas fa-envelope text-danger me-2"></i> <?= h($mlSupport->translate('Send Email')) ?>
                                    </div>
                                    <div class="list-group-item node-item border-0 py-2" draggable="true" data-type="social_media">
                                        <i class="fas fa-share-alt text-primary me-2"></i> <?= h($mlSupport->translate('Social Post')) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Workflow Canvas')) ?>: <input type="text" id="workflowName" class="border-0 text-primary fw-bold" value="Lead Automation v1" style="width: 200px;"></h5>
                                <div>
                                    <input type="hidden" id="currentWorkflowId" value="">
                                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="executeWorkflow()"><i class="fas fa-play me-1"></i> <?= h($mlSupport->translate('Execute')) ?></button>
                                    <button class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#templatesModal"><i class="fas fa-layer-group me-1"></i> <?= h($mlSupport->translate('Templates')) ?></button>
                                    <button class="btn btn-sm btn-primary" onclick="saveWorkflow()"><i class="fas fa-save me-1"></i> <?= h($mlSupport->translate('Save Workflow')) ?></button>
                                </div>
                            </div>
                            <div class="card-body p-0 position-relative" style="height: 600px; background: #f0f2f5; overflow: hidden;" id="canvasParent">
                                <!-- Canvas Grid Pattern -->
                                <div style="position: absolute; width: 200%; height: 200%; background-image: radial-gradient(#d1d1d1 1px, transparent 1px); background-size: 20px 20px; top: -50%; left: -50%;"></div>

                                <div id="workflowCanvas" class="position-absolute w-100 h-100" ondrop="dropNode(event)" ondragover="allowDrop(event)">
                                    <!-- Nodes will be added here dynamically -->
                                </div>
                            </div>
                        </div>

                        <!-- Execution Logs -->
                        <div class="card shadow-sm mt-4 border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Execution Logs')) ?></h5>
                                <button class="btn btn-sm btn-link text-decoration-none" onclick="loadExecutionLogs()"><?= h($mlSupport->translate('Refresh')) ?></button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 250px;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-3"><?= h($mlSupport->translate('Time')) ?></th>
                                                <th><?= h($mlSupport->translate('Status')) ?></th>
                                                <th><?= h($mlSupport->translate('Duration')) ?></th>
                                                <th class="pe-3"><?= h($mlSupport->translate('Log Summary')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="executionLogsList">
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted"><?= h($mlSupport->translate('No recent executions')) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marketing Tab -->
            <div class="tab-pane" id="marketing">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Ad Generation')) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Select Property')) ?></label>
                                    <select id="marketingProperty" class="form-select form-select-sm">
                                        <option value="1">Dream Villa - Sector 12</option>
                                        <option value="2">Skyline Apartments</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Target Audience')) ?></label>
                                    <input type="text" id="marketingAudience" class="form-control form-control-sm" placeholder="<?= h($mlSupport->translate('e.g. Young Professionals')) ?>">
                                </div>
                                <button class="btn btn-sm btn-primary w-100" onclick="generateAd()"><?= h($mlSupport->translate('Generate AI Ad Copy')) ?></button>
                                <div id="adOutput" class="mt-3 p-3 bg-light border rounded d-none small"></div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Social Scheduling')) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Content Type')) ?></label>
                                    <select id="socialType" class="form-select form-select-sm">
                                        <option value="image"><?= h($mlSupport->translate('Image Post')) ?></option>
                                        <option value="video"><?= h($mlSupport->translate('Short Video/Reel')) ?></option>
                                        <option value="text"><?= h($mlSupport->translate('Text Update')) ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Platforms')) ?></label>
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" value="facebook" id="platFB" checked>
                                        <label class="form-check-label" for="platFB"><?= h($mlSupport->translate('Facebook')) ?></label>
                                    </div>
                                    <div class="form-check small">
                                        <input class="form-check-input" type="checkbox" value="instagram" id="platIG" checked>
                                        <label class="form-check-label" for="platIG"><?= h($mlSupport->translate('Instagram')) ?></label>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-success w-100" onclick="scheduleSocial()"><?= h($mlSupport->translate('Schedule via AI')) ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Lead Quality Scoring')) ?></h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshLeadScores()"><?= h($mlSupport->translate('Refresh')) ?></button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-3"><?= h($mlSupport->translate('Lead Name')) ?></th>
                                                <th><?= h($mlSupport->translate('Interest')) ?></th>
                                                <th><?= h($mlSupport->translate('Budget')) ?></th>
                                                <th><?= h($mlSupport->translate('AI Score')) ?></th>
                                                <th class="pe-3"><?= h($mlSupport->translate('Action')) ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="leadScoreList">
                                            <tr>
                                                <td class="ps-3">John Doe</td>
                                                <td>High</td>
                                                <td>60L</td>
                                                <td><span class="badge bg-success">92/100</span></td>
                                                <td class="pe-3"><button class="btn btn-xs btn-primary"><?= h($mlSupport->translate('Call')) ?></button></td>
                                            </tr>
                                            <tr>
                                                <td class="ps-3">Sarah Smith</td>
                                                <td>Medium</td>
                                                <td>45L</td>
                                                <td><span class="badge bg-warning">65/100</span></td>
                                                <td class="pe-3"><button class="btn btn-xs btn-primary"><?= h($mlSupport->translate('Call')) ?></button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telecalling Tab -->
            <div class="tab-pane" id="telecalling">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-dark text-white py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Initiate AI Call')) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Select Lead')) ?></label>
                                    <select id="callLead" class="form-select form-select-sm">
                                        <option value="1">John Doe (+91 9876543210)</option>
                                        <option value="2">Sarah Smith (+91 8877665544)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Script Type')) ?></label>
                                    <select id="callScript" class="form-select form-select-sm">
                                        <option value="intro"><?= h($mlSupport->translate('Introduction & Interest Check')) ?></option>
                                        <option value="followup"><?= h($mlSupport->translate('Follow-up on Previous Interest')) ?></option>
                                        <option value="closing"><?= h($mlSupport->translate('Closing/Site Visit Scheduling')) ?></option>
                                    </select>
                                </div>
                                <button class="btn btn-sm btn-dark w-100" onclick="startAICall()"><?= h($mlSupport->translate('Start AI Call')) ?></button>
                                <div id="callStatus" class="mt-3 p-3 border rounded d-none small"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Recent AI Conversations')) ?></h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush" id="callLogs">
                                    <div class="list-group-item py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold"><?= h($mlSupport->translate('Call with John Doe')) ?></h6>
                                            <small class="text-muted">2 <?= h($mlSupport->translate('hours ago')) ?></small>
                                        </div>
                                        <p class="mb-2 small text-muted">"I am interested in the 3BHK villa but need to check the payment plan."</p>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info-soft text-info border-0"><?= h($mlSupport->translate('Intent: High Interest')) ?></span>
                                            <span class="badge bg-success-soft text-success border-0"><?= h($mlSupport->translate('Action: Scheduled Visit')) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">AI Tools Ecosystem</h4>
                    <div class="d-flex" style="width: 60%;">
                        <input type="text" id="toolSearch" class="form-control me-2" placeholder="Search 1000+ AI tools..." onkeyup="loadAITools()">
                        <select id="toolCategory" class="form-select w-25" onchange="loadAITools()">
                            <option value="">All Categories</option>
                            <option value="free">Free/Open Source</option>
                            <option value="paid">Premium/Paid</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="toolsList">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations Tab -->
        <div class="tab-pane" id="recommendations">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Personalized AI Recommendations')) ?></h5>
                    <p class="mb-0 small opacity-75"><?= h($mlSupport->translate('Based on your industry, skills, and organizational goals')) ?></p>
                </div>
                <div class="card-body">
                    <div class="row" id="recommendationsList">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted"><?= h($mlSupport->translate('Analyzing your profile for recommendations...')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ecosystem Tab -->
        <div class="tab-pane" id="ecosystem">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Data Pipelines')) ?></h5>
                            <button class="btn btn-xs btn-primary" onclick="newPipeline()">+ <?= h($mlSupport->translate('Create')) ?></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3"><?= h($mlSupport->translate('Pipeline')) ?></th>
                                            <th><?= h($mlSupport->translate('Tool')) ?></th>
                                            <th class="pe-3"><?= h($mlSupport->translate('Status')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="pipelineList">
                                        <!-- Loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Model Training Env')) ?></h5>
                            <button class="btn btn-xs btn-success" onclick="newTraining()">+ <?= h($mlSupport->translate('Start Session')) ?></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3"><?= h($mlSupport->translate('Model')) ?></th>
                                            <th><?= h($mlSupport->translate('Accuracy')) ?></th>
                                            <th class="pe-3"><?= h($mlSupport->translate('Status')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="trainingList">
                                        <!-- Loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bot Management Tab -->
        <div class="tab-pane" id="bot-management">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('AI Bot Policies & Roles')) ?></h5>
                            <button class="btn btn-sm btn-light" onclick="addPolicy()">+ <?= h($mlSupport->translate('Add Policy')) ?></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3"><?= h($mlSupport->translate('Role')) ?></th>
                                            <th><?= h($mlSupport->translate('Key')) ?></th>
                                            <th><?= h($mlSupport->translate('Response/Rule')) ?></th>
                                            <th class="pe-3"><?= h($mlSupport->translate('Status')) ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="policyTable">
                                        <!-- Loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Knowledge Graph Insights')) ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="kgVisualization" style="height: 300px; background: #f8f9fa; border: 1px dashed #ccc;" class="d-flex align-items-center justify-content-center">
                                <p class="text-muted"><?= h($mlSupport->translate('Graph visualization loading...')) ?></p>
                            </div>
                            <div class="mt-3" id="kgList">
                                <!-- List of entities -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Bot Test Console')) ?></h5>
                        </div>
                        <div class="card-body">
                            <form id="botTestForm" onsubmit="event.preventDefault(); testBot();">
                                <?= getCsrfField() ?>
                                <div id="botChatBox" class="mb-3 p-2 border rounded small" style="height: 200px; overflow-y: auto; background: #f8f9fa;">
                                    <div class="text-muted"><?= h($mlSupport->translate('Select role and type query to test...')) ?></div>
                                </div>
                                <div class="mb-2">
                                    <select id="testRole" class="form-select form-select-sm">
                                        <option value="visitor"><?= h($mlSupport->translate('Visitor')) ?></option>
                                        <option value="customer"><?= h($mlSupport->translate('Customer')) ?></option>
                                        <option value="associate"><?= h($mlSupport->translate('Associate')) ?></option>
                                        <option value="admin"><?= h($mlSupport->translate('Admin')) ?></option>
                                    </select>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="testQuery" class="form-control" placeholder="<?= h($mlSupport->translate('Type message...')) ?>">
                                    <button class="btn btn-primary" type="submit"><?= h($mlSupport->translate('Send')) ?></button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Auto-Optimization')) ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="small"><?= h($mlSupport->translate('Self-learning is active.')) ?> <?= h($mlSupport->translate('Last optimization:')) ?> <strong id="lastOpt">2 <?= h($mlSupport->translate('hours ago')) ?></strong></p>
                            <button class="btn btn-sm btn-outline-dark w-100" onclick="triggerLearning()"><?= h($mlSupport->translate('Process Now')) ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Learning Center Tab -->
        <div class="tab-pane" id="learning-center">
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Learning Progress')) ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="personalPlanInfo" class="mb-4">
                                <div class="text-center py-4">
                                    <p class="text-muted small"><?= h($mlSupport->translate('No active plan found.')) ?></p>
                                    <button class="btn btn-sm btn-primary" onclick="generateLearningPlan()"><?= h($mlSupport->translate('Generate AI Roadmap')) ?></button>
                                </div>
                            </div>
                            <div id="learningProgressBars">
                                <!-- Progress bars loaded via AJAX -->
                            </div>
                            <hr>
                            <h6 class="text-muted small text-uppercase fw-bold mb-3"><?= h($mlSupport->translate('Recent Certificates')) ?></h6>
                            <div id="certificatesList">
                                <div class="text-center py-2"><small class="text-muted"><?= h($mlSupport->translate('No certificates yet.')) ?></small></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Weekly AI Knowledge Updates')) ?></h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadLearningUpdates()"><?= h($mlSupport->translate('Refresh')) ?></button>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="learningUpdatesList">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted"><?= h($mlSupport->translate('Analyzing industry trends...')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Implementation Resources')) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="trainingModulesList">
                                <!-- Training modules loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Insights Tab -->
        <div class="tab-pane" id="insights">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Data Visualization')) ?></h5>
                            <select id="analysisSource" class="form-select form-select-sm w-25" onchange="loadInsights()">
                                <option value="leads"><?= h($mlSupport->translate('Lead Distribution')) ?></option>
                                <option value="sales"><?= h($mlSupport->translate('Sales Trends')) ?></option>
                                <option value="system_health"><?= h($mlSupport->translate('AI Performance')) ?></option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="insightsChart"></canvas>
                            </div>
                            <div id="analysisSummary" class="mt-3 small text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-success text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Real-time Decision Engine')) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold"><?= h($mlSupport->translate('Test Decision Type')) ?></label>
                                <select id="decisionType" class="form-select form-select-sm">
                                    <option value="lead_prioritization"><?= h($mlSupport->translate('Lead Prioritization')) ?></option>
                                    <option value="campaign_allocation"><?= h($mlSupport->translate('Budget Allocation')) ?></option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold"><?= h($mlSupport->translate('Input JSON')) ?></label>
                                <textarea id="decisionInput" class="form-control form-control-sm" rows="3">{"budget": 6000000, "intent": "high_interest"}</textarea>
                            </div>
                            <button class="btn btn-sm btn-success w-100" onclick="runDecision()"><?= h($mlSupport->translate('Execute Decision')) ?></button>
                            <div id="decisionResult" class="mt-3 p-2 bg-light border rounded d-none small"></div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-info text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('NLP Understanding Test')) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" id="nlpInput" class="form-control" placeholder="<?= h($mlSupport->translate('Type query for AI understanding...')) ?>">
                                <button class="btn btn-info text-white" onclick="testNLP()"><?= h($mlSupport->translate('Analyze')) ?></button>
                            </div>
                            <div id="nlpResult" class="small p-2 border rounded d-none" style="background: #f8f9fa;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dev Tools Tab -->
        <div class="tab-pane" id="dev-tools">
            <div class="row">
                <div class="col-md-7">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('AI Coding Assistant')) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Action')) ?></label>
                                    <select id="coderAction" class="form-select form-select-sm">
                                        <option value="generate_snippet"><?= h($mlSupport->translate('Generate Snippet')) ?></option>
                                        <option value="debug_code"><?= h($mlSupport->translate('Debug Code')) ?></option>
                                        <option value="optimize_query"><?= h($mlSupport->translate('Optimize SQL')) ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold"><?= h($mlSupport->translate('Language')) ?></label>
                                    <select id="coderLang" class="form-select form-select-sm">
                                        <option value="php"><?= h($mlSupport->translate('PHP')) ?></option>
                                        <option value="javascript"><?= h($mlSupport->translate('JavaScript')) ?></option>
                                        <option value="sql"><?= h($mlSupport->translate('SQL')) ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold"><?= h($mlSupport->translate('Input / Task Description')) ?></label>
                                <textarea id="coderInput" class="form-control form-control-sm" rows="5" placeholder="<?= h($mlSupport->translate('Enter task or paste code...')) ?>"></textarea>
                            </div>
                            <button class="btn btn-sm btn-dark w-100" onclick="runCoder()"><?= h($mlSupport->translate('Process Task')) ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-secondary text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Output / Suggestions')) ?></h5>
                        </div>
                        <div class="card-body">
                            <pre id="coderOutput" class="p-2 bg-light border rounded small" style="min-height: 200px; white-space: pre-wrap;"></pre>
                            <div id="coderTips" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tool Directory Tab -->
        <div class="tab-pane" id="tool-directory">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('1000+ AI Tools Database')) ?></h5>
                    <div class="input-group input-group-sm w-25">
                        <input type="text" id="toolSearch" class="form-control" placeholder="<?= h($mlSupport->translate('Search tools...')) ?>" onkeyup="filterTools()">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3"><?= h($mlSupport->translate('Tool Name')) ?></th>
                                    <th><?= h($mlSupport->translate('Category')) ?></th>
                                    <th><?= h($mlSupport->translate('Pricing')) ?></th>
                                    <th><?= h($mlSupport->translate('Level')) ?></th>
                                    <th class="pe-3"><?= h($mlSupport->translate('Action')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="toolsListTable">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted"><?= h($mlSupport->translate('Loading directory...')) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health & Diagnostics Tab -->
        <div class="tab-pane" id="health">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Component Status')) ?></h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush" id="healthStatusList">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <?= h($mlSupport->translate('Database Connection')) ?>
                                    <span class="badge bg-secondary"><?= h($mlSupport->translate('Checking...')) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <?= h($mlSupport->translate('Background Worker')) ?>
                                    <span class="badge bg-secondary"><?= h($mlSupport->translate('Checking...')) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <?= h($mlSupport->translate('Uploads Directory')) ?>
                                    <span class="badge bg-secondary"><?= h($mlSupport->translate('Checking...')) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <?= h($mlSupport->translate('Logs Directory')) ?>
                                    <span class="badge bg-secondary"><?= h($mlSupport->translate('Checking...')) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-info text-white py-3">
                            <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('Self-Diagnostic & Repair')) ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-4"><?= h($mlSupport->translate('Run a complete system check to identify and automatically fix common configuration issues.')) ?></p>
                            <button class="btn btn-primary w-100 py-2" id="repairBtn" onclick="runSelfRepair()">
                                <i class="fas fa-tools me-2"></i> <?= h($mlSupport->translate('Run Full System Check')) ?>
                            </button>
                            <div id="repairOutput" class="mt-3 p-3 bg-light small border rounded d-none" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane" id="settings">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title h6 fw-bold mb-0"><?= h($mlSupport->translate('AI System Configuration')) ?></h5>
                </div>
                <div class="card-body">
                    <form id="aiConfigForm">
                        <?= getCsrfField() ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold"><?= h($mlSupport->translate('Global Agent Mode')) ?></label>
                            <select class="form-select" name="mode">
                                <option value="assistant" <?= ($aiManager->getMode() ?? 'assistant') == 'assistant' ? 'selected' : '' ?>><?= h($mlSupport->translate('Assistant (Task-focused)')) ?></option>
                                <option value="leader" <?= ($aiManager->getMode() ?? 'assistant') == 'leader' ? 'selected' : '' ?>><?= h($mlSupport->translate('Leader (Strategy-focused)')) ?></option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary px-4" onclick="updateAISettings()"><?= h($mlSupport->translate('Update Settings')) ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Node Configuration Modal -->
<div class="modal fade" id="nodeConfigModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nodeConfigTitle">Configure Node</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="nodeConfigBody">
                <!-- Form will be generated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveNodeConfig()">Save Configuration</button>
            </div>
        </div>
    </div>
</div>
<div id="modalContainer"></div>

<!-- Language Modal -->
<div class="modal fade" id="langModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select System Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?php foreach ($supportedLangs as $lang): ?>
                        <div class="col-6 mb-2">
                            <button class="btn btn-outline-secondary w-100 text-start <?= $lang['code'] === $currentLang ? 'active' : '' ?>"
                                onclick="setLang('<?= $lang['code'] ?>')">
                                <?= $lang['native_name'] ?> (<?= strtoupper($lang['code']) ?>)
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';

    function h(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function updateAISettings() {
        const form = document.getElementById('aiConfigForm');
        const formData = new FormData(form);

        fetch('../api/ai/mode.php', {
            method: 'POST',
            body: formData
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                alert('<?= h($mlSupport->translate('Settings updated successfully!')) ?>');
                location.reload();
            } else {
                alert(data.message || '<?= h($mlSupport->translate('Error updating settings')) ?>');
            }
        });
    }

    function loadMarketplaceTools() {
        const search = document.getElementById('toolSearch').value;
        const cat = document.getElementById('toolCategory').value;
        const list = document.getElementById('toolsList');

        fetch(`../api/ai/tools.php?search=${search}&category=${cat}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    list.innerHTML = data.data.map(tool => `
                    <div class="col-md-4 mb-4">
                        <div class="card border h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title">${h(tool.name)}</h5>
                                    <span class="badge bg-${tool.category === 'free' ? 'success' : 'info'}">${h(tool.category)}</span>
                                </div>
                                <p class="card-text small text-muted">${h(tool.description.substring(0, 100))}...</p>
                                <div class="mt-3">
                                    <button class="btn btn-xs btn-outline-primary" onclick="integrateTool('${h(tool.name)}', '${h(tool.category)}')">
                                        <?= h($mlSupport->translate('Integrate')) ?>
                                    </button>
                                    <button class="btn btn-xs btn-link text-muted"><?= h($mlSupport->translate('Details')) ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                }
            });
    }

    function toggleNotifications() {
        const panel = document.getElementById('notificationPanel');
        panel.classList.toggle('d-none');
        document.getElementById('notifBadge').classList.add('d-none');
    }

    // Simulate WebSockets for live monitoring
    function startLiveMonitoring() {
        setInterval(() => {
            // Refresh dashboard stats
            fetch('../api/ai/health.php?action=stats')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update UI stats if elements exist
                        // (Dashboard values like active jobs, latency, etc)
                    }
                });

            // Load latest execution logs if we're on the workflows tab
            const workflowsTab = document.getElementById('workflows');
            if (workflowsTab && workflowsTab.classList.contains('active')) {
                loadExecutionLogs();
            }

            // Auto-refresh notifications
            const badge = document.getElementById('notifBadge');
            if (badge && !badge.classList.contains('d-none')) {
                // New activities detected
            }
        }, 5000); // Poll every 5s for updates
    }

    function loadTemplates() {
        fetch('../api/ai/templates.php?action=list')
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('templatesList');
                if (data.status === 'success') {
                    list.innerHTML = data.data.map(t => `
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm hover-shadow" onclick="loadTemplate('${t.id}')" style="cursor: pointer;">
                            <div class="card-body">
                                <h6 class="fw-bold"><i class="fas fa-${t.icon} text-info me-2"></i> ${t.name}</h6>
                                <p class="small text-muted mb-0">${t.description}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
                }
            });
    }

    function loadTemplate(id) {
        if (!confirm("This will clear your current canvas. Continue?")) return;

        fetch(`../api/ai/templates.php?action=get&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const t = data.data;
                    editor.clear();

                    // Reconstruct Drawflow data from our template format
                    t.nodes.forEach(n => {
                        const nodeType = n.type;
                        let nodeData = {
                            trigger: {
                                inputs: 0,
                                outputs: 1,
                                color: 'warning'
                            },
                            agent: {
                                inputs: 1,
                                outputs: 1,
                                color: 'primary'
                            },
                            database: {
                                inputs: 1,
                                outputs: 1,
                                color: 'success'
                            },
                            condition: {
                                inputs: 1,
                                outputs: 2,
                                color: 'info'
                            },
                            notification: {
                                inputs: 1,
                                outputs: 0,
                                color: 'danger'
                            },
                            telecalling: {
                                inputs: 1,
                                outputs: 1,
                                color: 'dark'
                            },
                            sms: {
                                inputs: 1,
                                outputs: 1,
                                color: 'info'
                            },
                            calendar: {
                                inputs: 1,
                                outputs: 1,
                                color: 'primary'
                            },
                            payment: {
                                inputs: 1,
                                outputs: 1,
                                color: 'success'
                            },
                            email: {
                                inputs: 1,
                                outputs: 1,
                                color: 'danger'
                            },
                            social_media: {
                                inputs: 1,
                                outputs: 1,
                                color: 'primary'
                            }
                        } [nodeType] || {
                            inputs: 1,
                            outputs: 1,
                            color: 'primary'
                        };

                        const icons = {
                            trigger: 'bolt',
                            agent: 'robot',
                            database: 'database',
                            condition: 'code-branch',
                            notification: 'bell',
                            telecalling: 'phone-alt',
                            sms: 'sms',
                            calendar: 'calendar-alt',
                            payment: 'credit-card',
                            email: 'envelope',
                            social_media: 'share-alt'
                        };
                        const icon = icons[nodeType] || 'robot';

                        const html = `
                        <div>
                            <div class="title-box"><i class="fas fa-${icon}"></i> ${n.name}</div>
                            <div class="box">
                                <p class="small mb-1">Config:</p>
                                <input type="text" df-name class="form-control form-control-xs mb-1" placeholder="Name" value="${n.name}">
                                <button class="btn btn-xs btn-outline-secondary w-100" onclick="editNodeConfig(this)">Configure</button>
                            </div>
                        </div>
                    `;

                        editor.addNode(nodeType, nodeData.inputs, nodeData.outputs, n.pos.x, n.pos.y, nodeData.color, n.config, html);
                    });

                    // Add connections
                    t.connections.forEach(c => {
                        editor.addConnection(c.from, c.to, c.from_port, c.to_port);
                    });

                    bootstrap.Modal.getInstance(document.getElementById('templatesModal')).hide();
                    document.getElementById('workflowName').value = t.name;
                    document.getElementById('currentWorkflowId').value = ""; // New workflow from template
                }
            });
    }

    function startMockNotifications() {
        const events = [{
                type: 'job',
                msg: 'Lead Qualification job started',
                icon: 'fa-robot text-primary'
            },
            {
                type: 'workflow',
                msg: 'Lead Automation v1 executed',
                icon: 'fa-play text-success'
            },
            {
                type: 'training',
                msg: 'New model training session queued',
                icon: 'fa-microchip text-info'
            },
            {
                type: 'error',
                msg: 'API Gateway connection retry',
                icon: 'fa-exclamation-triangle text-warning'
            },
            {
                type: 'bot',
                msg: 'Bot policy updated: Pricing Inquiry',
                icon: 'fa-shield-alt text-dark'
            }
        ];

        setInterval(() => {
            const ev = events[Math.floor(Math.random() * events.length)];
            const list = document.getElementById('notifList');
            if (!list) return;
            if (list.querySelector('.text-center')) list.innerHTML = '';

            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action py-2 border-start border-3 border-' + (ev.type === 'error' ? 'warning' : 'info');
            item.innerHTML = `
            <div class="d-flex w-100 justify-content-between">
                <small class="fw-bold"><i class="fas ${ev.icon} me-1"></i> ${ev.type.toUpperCase()}</small>
                <small class="text-muted">now</small>
            </div>
            <p class="mb-0 small">${ev.msg}</p>
        `;
            list.prepend(item);
            if (list.children.length > 8) list.lastElementChild.remove();

            const badge = document.getElementById('notifBadge');
            if (document.getElementById('notificationPanel').classList.contains('d-none')) {
                badge.classList.remove('d-none');
            }
        }, 15000); // Every 15 seconds
    }

    function toggleAIMode() {
        const currentMode = document.getElementById('currentModeDisplay').innerText.toLowerCase();
        const newMode = currentMode === 'assistant' ? 'leader' : 'assistant';

        fetch('../api/ai/mode.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `mode=${newMode}&csrf_token=${CSRF_TOKEN}`
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                document.getElementById('currentModeDisplay').innerText = data.mode.toUpperCase();
                location.reload();
            }
        });
    }

    function loadDirectoryTools() {
        fetch('../api/ai/directory.php?action=list')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const tbody = document.getElementById('toolsListTable');
                    tbody.innerHTML = data.data.map(tool => `
                    <tr>
                        <td><strong>${h(tool.name)}</strong></td>
                        <td>${h(tool.category)}</td>
                        <td><span class="badge bg-${tool.pricing === 'free' ? 'success' : 'info'}">${h(tool.pricing.toUpperCase())}</span></td>
                        <td><span class="badge bg-secondary">${h(tool.integration_level)}</span></td>
                        <td>
                            <button class="btn btn-xs btn-outline-primary" onclick="viewToolGuidance(${tool.id})">
                                <i class="fas fa-book me-1"></i> <?= h($mlSupport->translate('Guide')) ?>
                            </button>
                        </td>
                    </tr>
                `).join('');
                }
            });
    }

    function loadRecommendations() {
        fetch('../api/ai/directory.php?action=recommendations')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const grid = document.getElementById('recommendationsGrid');
                    grid.innerHTML = data.data.map(tool => `
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100 border-start border-4 border-info">
                            <div class="card-body">
                                <h5 class="fw-bold">${tool.name}</h5>
                                <p class="small text-muted">${tool.use_cases.substring(0, 100)}...</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="badge bg-light text-dark">${tool.category}</span>
                                    <button class="btn btn-sm btn-primary" onclick="viewToolGuidance(${tool.id})">Implement</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                }
            });
    }

    function filterTools() {
        const q = document.getElementById('toolSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#toolsListTable tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    function viewToolGuidance(id) {
        fetch(`../api/ai/directory.php?action=get&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const tool = data.data;
                    const steps = JSON.parse(tool.guidance_steps || '[]');
                    let html = `
                    <h6><strong>Features:</strong> ${tool.features}</h6>
                    <p><strong>Use Cases:</strong> ${tool.use_cases}</p>
                    <hr>
                    <h6>Step-by-Step Implementation:</h6>
                    <ol class="list-group list-group-numbered mb-3">
                        ${steps.map(s => `<li class="list-group-item small">${s}</li>`).join('')}
                    </ol>
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Requirements:</strong> ${tool.tech_requirements}
                    </div>
                `;

                    document.getElementById('nodeConfigTitle').innerText = `Implementation Guide: ${tool.name}`;
                    document.getElementById('nodeConfigBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('nodeConfigModal')).show();
                }
            });
    }

    function loadEcosystem() {
        fetch('../api/ai/ecosystem.php?action=list')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const pipelines = data.data.pipelines;
                    const training = data.data.training;

                    document.getElementById('pipelineList').innerHTML = pipelines.length ? pipelines.map(p => `
                    <tr>
                        <td>${p.name}</td>
                        <td>${p.tool_id}</td>
                        <td><span class="badge bg-success">${p.status}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center">No pipelines found</td></tr>';

                    document.getElementById('trainingList').innerHTML = training.length ? training.map(t => `
                    <tr>
                        <td>${t.model_name}</td>
                        <td>${t.accuracy ? (t.accuracy * 100).toFixed(1) + '%' : 'N/A'}</td>
                        <td><span class="badge bg-info">${t.status}</span></td>
                    </tr>
                `).join('') : '<tr><td colspan="3" class="text-center">No sessions found</td></tr>';
                }
            });
    }

    function loadBotSettings() {
        fetch('../api/ai/manage_bot.php?action=list_policies')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('policyTable').innerHTML = data.policies.map(p => `
                    <tr>
                        <td><span class="badge bg-secondary">${p.role}</span></td>
                        <td><code>${p.rule_key}</code></td>
                        <td>${p.rule_value}</td>
                        <td><span class="badge bg-${p.is_active ? 'success' : 'danger'}">${p.is_active ? 'Active' : 'Inactive'}</span></td>
                    </tr>
                `).join('');

                    document.getElementById('kgList').innerHTML = data.kg.map(k => `
                    <div class="d-inline-block p-2 m-1 border rounded bg-light">
                        <strong>${k.entity_name}</strong> <span class="badge bg-info">${(k.confidence_score * 100).toFixed(0)}%</span>
                    </div>
                `).join('');
                }
            });
    }



    function triggerLearning() {
        fetch('../api/ai/manage_bot.php?action=trigger_learning', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                loadBotSettings();
            });
    }

    function addPolicy() {
        const role = prompt("<?= h($mlSupport->translate('Enter Role (visitor/customer/associate/admin):')) ?>");
        const key = prompt("<?= h($mlSupport->translate('Enter Intent Key (e.g. pricing_inquiry):')) ?>");
        const val = prompt("<?= h($mlSupport->translate('Enter Response Rule:')) ?>");
        if (!role || !key || !val) return;

        fetch('../api/ai/manage_bot.php?action=add_policy', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `role=${role}&key=${key}&val=${encodeURIComponent(val)}&csrf_token=${CSRF_TOKEN}`
        }).then(() => loadBotSettings());
    }

    function newPipeline() {
        const name = prompt("<?= h($mlSupport->translate('Enter Pipeline Name:')) ?>");
        if (!name) return;
        fetch('../api/ai/ecosystem.php?action=create_pipeline', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `name=${encodeURIComponent(name)}&tool_id=1&config={}&csrf_token=${CSRF_TOKEN}`
        }).then(() => loadEcosystem());
    }

    function newTraining() {
        const name = prompt("<?= h($mlSupport->translate('Enter Model Name:')) ?>");
        if (!name) return;
        fetch('../api/ai/ecosystem.php?action=start_training', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `model_name=${encodeURIComponent(name)}&dataset=leads_v1&params={}&csrf_token=${CSRF_TOKEN}`
        }).then(() => {
            alert("<?= h($mlSupport->translate('Training session started!')) ?>");
            loadEcosystem();
        });
    }

    let insightsChart = null;

    const AI_KEY = 'APS_AI_SECURE_KEY_2026';

    function loadInsights() {
        const source = document.getElementById('analysisSource').value;
        fetch(`../api/ai/advanced.php?action=analyze&source=${source}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const ctx = document.getElementById('insightsChart').getContext('2d');
                    const analysis = data.data;

                    if (insightsChart) insightsChart.destroy();

                    let chartType = 'bar';
                    if (analysis.type === 'distribution') chartType = 'pie';
                    if (analysis.type === 'time_series') chartType = 'line';

                    insightsChart = new Chart(ctx, {
                        type: chartType,
                        data: {
                            labels: analysis.labels || analysis.metrics.map(m => m.task_type),
                            datasets: [{
                                label: source.toUpperCase(),
                                data: analysis.values || analysis.metrics.map(m => m.avg_time),
                                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });

                    document.getElementById('analysisSummary').innerText = analysis.summary || `<?= h($mlSupport->translate('Analysis completed for')) ?> ${source}.`;
                }
            });
    }

    function runDecision() {
        const type = document.getElementById('decisionType').value;
        const input = document.getElementById('decisionInput').value;
        const resultDiv = document.getElementById('decisionResult');

        try {
            const jsonInput = JSON.parse(input);
            fetch(`../api/ai/advanced.php?action=decide`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `type=${type}&input=${encodeURIComponent(JSON.stringify(jsonInput))}&csrf_token=${CSRF_TOKEN}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        resultDiv.classList.remove('d-none');
                        resultDiv.innerHTML = `
                    <div class="fw-bold text-success"><?= h($mlSupport->translate('Decision:')) ?> ${data.data.decision.toUpperCase()}</div>
                    <div class="small text-muted">${data.data.reason || data.data.recommended_action}</div>
                    <div class="mt-1"><span class="badge bg-info"><?= h($mlSupport->translate('Score:')) ?> ${data.data.score || 'N/A'}</span></div>
                `;
                    }
                });
        } catch (e) {
            alert("<?= h($mlSupport->translate('Invalid JSON input!')) ?>");
        }
    }

    function testNLP() {
        const text = document.getElementById('nlpInput').value;
        const resultDiv = document.getElementById('nlpResult');
        if (!text) return;

        fetch(`../api/ai/advanced.php?action=understand`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `text=${encodeURIComponent(text)}&csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const d = data.data;
                    resultDiv.classList.remove('d-none');
                    resultDiv.innerHTML = `
                <div class="mb-1"><strong><?= h($mlSupport->translate('Intent:')) ?></strong> <span class="badge bg-primary">${d.intent.name} (${(d.intent.confidence * 100).toFixed(0)}%)</span></div>
                <div class="mb-1"><strong><?= h($mlSupport->translate('Sentiment:')) ?></strong> <span class="text-${d.sentiment.label === 'positive' ? 'success' : (d.sentiment.label === 'negative' ? 'danger' : 'muted')}">${d.sentiment.label}</span></div>
                <div><strong><?= h($mlSupport->translate('Entities:')) ?></strong> ${JSON.stringify(d.entities)}</div>
            `;
                }
            });
    }

    function generateAd() {
        const propertyId = document.getElementById('marketingProperty').value;
        const audience = document.getElementById('marketingAudience').value;
        const output = document.getElementById('adOutput');

        output.classList.remove('d-none');
        output.innerHTML = "<?= h($mlSupport->translate('Generating ad copy...')) ?>";

        fetch(`../api/ai/advanced.php?action=marketing`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `sub_action=generate_ad&params[property_data][id]=${propertyId}&params[property_data][audience]=${encodeURIComponent(audience)}&csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                output.innerHTML = `<strong><?= h($mlSupport->translate('AI Generated Ad:')) ?></strong><br>${data.data.output || '<?= h($mlSupport->translate('Ad generated successfully and saved to drafts.')) ?>'}`;
            });
    }

    function scheduleSocial() {
        const type = document.getElementById('socialType').value;
        const platforms = Array.from(document.querySelectorAll('input[id^="plat"]:checked')).map(i => i.value);

        fetch(`../api/ai/advanced.php?action=marketing`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `sub_action=schedule_social&params[type]=${type}&params[platforms]=${JSON.stringify(platforms)}&csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                alert("<?= h($mlSupport->translate('Social media content scheduled successfully!')) ?>");
            });
    }

    function startAICall() {
        const leadId = document.getElementById('callLead').value;
        const script = document.getElementById('callScript').value;
        const status = document.getElementById('callStatus');

        status.classList.remove('d-none');
        status.className = 'mt-3 p-2 border rounded small alert-info';
        status.innerHTML = "<?= h($mlSupport->translate('Dialing lead via AI Agent...')) ?>";

        fetch(`../api/ai/advanced.php?action=telecalling`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `sub_action=initiate_call&params[lead_id]=${leadId}&params[script_type]=${script}&csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                status.className = 'mt-3 p-2 border rounded small alert-success';
                status.innerHTML = `<strong><?= h($mlSupport->translate('Call Active:')) ?></strong> <?= h($mlSupport->translate('AI is now conversing with the lead. Monitor logs for real-time updates.')) ?>`;
            });
    }

    function refreshLeadScores() {
        loadInsights();
    }

    function runCoder() {
        const action = document.getElementById('coderAction').value;
        const lang = document.getElementById('coderLang').value;
        const input = document.getElementById('coderInput').value;
        const outputDiv = document.getElementById('coderOutput');
        const tipsDiv = document.getElementById('coderTips');

        if (!input) return;

        let params = {};
        if (action === 'generate_snippet') params = {
            task: input,
            lang: lang
        };
        else if (action === 'debug_code') params = {
            code: input,
            lang: lang
        };
        else if (action === 'optimize_query') params = {
            query: input
        };

        fetch(`../api/ai/advanced.php?action=code_assist`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `sub_action=${action}&params[${Object.keys(params)[0]}]=${encodeURIComponent(Object.values(params)[0])}&params[${Object.keys(params)[1]}]=${encodeURIComponent(Object.values(params)[1] || '')}&csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const d = data.data;
                    outputDiv.innerText = d.code || d.optimized_query || d.original_code || '';

                    let tipsHtml = '';
                    if (d.suggestions) tipsHtml += d.suggestions.map(s => `<div class="alert alert-warning py-1 px-2 small mb-1">${s}</div>`).join('');
                    if (d.tips) tipsHtml += d.tips.map(t => `<div class="alert alert-info py-1 px-2 small mb-1">${t}</div>`).join('');
                    tipsDiv.innerHTML = tipsHtml;
                }
            });
    }

    function loadHealthStatus() {
        fetch('../api/ai/health.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const d = data.data;
                    const list = document.getElementById('healthStatusList');
                    list.innerHTML = `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($mlSupport->translate('Database')) ?> (${d.database.name})
                        <span class="badge bg-success"><?= h($mlSupport->translate('Connected')) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($mlSupport->translate('Background Worker')) ?>
                        <span class="badge bg-${d.worker.status === 'active' ? 'success' : 'danger'}">
                            ${d.worker.status.toUpperCase()} ${d.worker.seconds_since ? '('+d.worker.seconds_since+'s <?= h($mlSupport->translate('ago')) ?>)' : ''}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($mlSupport->translate('Uploads Directory')) ?>
                        <span class="badge bg-${d.storage.uploads_writable ? 'success' : 'danger'}">
                            ${d.storage.uploads_writable ? '<?= h($mlSupport->translate('Writable')) ?>' : '<?= h($mlSupport->translate('Read-only')) ?>'}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($mlSupport->translate('Logs Directory')) ?>
                        <span class="badge bg-${d.storage.logs_writable ? 'success' : 'danger'}">
                            ${d.storage.logs_writable ? '<?= h($mlSupport->translate('Writable')) ?>' : '<?= h($mlSupport->translate('Read-only')) ?>'}
                        </span>
                    </li>
                `;
                }
            });
    }

    function loadLearningUpdates() {
        fetch('../api/ai/learning.php?action=get_plan')
            .then(res => res.json())
            .then(data => {
                const planInfo = document.getElementById('personalPlanInfo');

                if (data.status === 'success' && data.data) {
                    const plan = data.data;
                    planInfo.innerHTML = `
                    <h6 class="fw-bold mb-1 text-primary">${plan.title}</h6>
                    <p class="small text-muted mb-2">${plan.goal}</p>
                    <div class="progress mb-1" style="height: 10px;">
                        <div class="progress-bar bg-info" style="width: ${plan.progress_percentage}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span><?= h($mlSupport->translate('Overall Progress')) ?></span>
                        <span>${parseFloat(plan.progress_percentage).toFixed(1)}%</span>
                    </div>
                `;

                    // Load modules details
                    fetch('../api/ai/learning.php?action=get_modules')
                        .then(res => res.json())
                        .then(mdata => {
                            if (mdata.status === 'success') {
                                const modulesList = document.getElementById('trainingModulesList');
                                const updatesList = document.getElementById('learningUpdatesList');

                                modulesList.innerHTML = mdata.data.map(m => `
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded h-100 bg-light-hover transition-all">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-${m.type === 'video' ? 'video text-primary' : (m.type === 'document' ? 'file-pdf text-danger' : 'question-circle text-info')} me-2"></i> ${m.title}</h6>
                                        <p class="small text-muted mb-2" style="height: 40px; overflow: hidden;">${m.description}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-white text-dark border">${m.duration_minutes} <?= h($mlSupport->translate('mins')) ?></span>
                                            <button class="btn btn-xs btn-primary" onclick="startModule(${m.id})"><?= h($mlSupport->translate('Start Module')) ?></button>
                                        </div>
                                    </div>
                                </div>
                            `).join('');

                                updatesList.innerHTML = mdata.data.slice(0, 4).map(m => `
                                <div class="list-group-item border-start border-3 border-info">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold">${m.title}</h6>
                                        <small class="text-info"><?= h($mlSupport->translate('Recommended')) ?></small>
                                    </div>
                                    <p class="mb-1 small text-muted">${m.description.substring(0, 120)}...</p>
                                    <div class="text-end">
                                        <button class="btn btn-link btn-xs p-0 text-decoration-none" onclick="startModule(${m.id})"><?= h($mlSupport->translate('Learn More')) ?> &raquo;</button>
                                    </div>
                                </div>
                            `).join('');
                            }
                        });
                }
            });
    }

    function generateLearningPlan() {
        const btn = event.target;
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = "<?= h($mlSupport->translate('Generating...')) ?>";

        fetch('../api/ai/learning.php?action=generate_plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    loadLearningUpdates();
                } else {
                    alert("<?= h($mlSupport->translate('Error:')) ?> " + data.message);
                    btn.disabled = false;
                    btn.innerText = originalText;
                }
            });
    }

    function startModule(id) {
        const msg = "<?= h($mlSupport->translate('Starting Module #')) ?>" + id + "\n\n<?= h($mlSupport->translate('In this simulation, we\'ll mark some progress for you.')) ?>";
        if (!confirm(msg)) return;

        fetch('../api/ai/learning.php?action=update_progress', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `plan_id=1&progress=15&csrf_token=${CSRF_TOKEN}`
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                alert("<?= h($mlSupport->translate('Progress updated! Plan is now')) ?> " + data.new_progress + "% <?= h($mlSupport->translate('complete.')) ?>");
                loadLearningUpdates();
            }
        });
    }

    function runSelfRepair() {
        const btn = document.getElementById('repairBtn');
        const out = document.getElementById('repairOutput');
        btn.disabled = true;
        out.classList.remove('d-none');
        out.innerHTML = "<?= h($mlSupport->translate('Initializing deep system scan...')) ?><br>";

        fetch(`../api/ai/repair.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                out.innerHTML = "";
                data.logs.forEach(log => {
                    let prefix = "";
                    if (log.type === 'fixed') prefix = "<span class='text-success'>[FIXED]</span> ";
                    else if (log.type === 'ok') prefix = "<span class='text-info'>[OK]</span> ";
                    else if (log.type === 'error') prefix = "<span class='text-danger'>[ERROR]</span> ";
                    else if (log.type === 'warning') prefix = "<span class='text-warning'>[NOTICE]</span> ";

                    out.innerHTML += `${prefix}${log.msg}<br>`;
                });
                out.innerHTML += "<strong><?= h($mlSupport->translate('System Maintenance Complete.')) ?></strong>";
                btn.disabled = false;
                loadHealthStatus();
            })
            .catch(err => {
                out.innerHTML = "<span class='text-danger'><?= h($mlSupport->translate('Repair failed. Please check server logs.')) ?></span>";
                btn.disabled = false;
            });
    }

    // Initialize Drawflow
    let editor = null;
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('workflowCanvas');
        if (canvas) {
            editor = new Drawflow(canvas);
            editor.start();

            // Add default nodes from library
            editor.on('nodeCreated', function(id) {
                console.log("Node created " + id);
            });
        }

        startLiveMonitoring();
        startMockNotifications();
        loadMarketplaceTools();
        loadDirectoryTools();
        loadEcosystem();
        loadBotSettings();
        loadHealthStatus();
        loadTemplates();

        // Auto-refresh health every 30 seconds
        setInterval(loadHealthStatus, 30000);
    });

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function dropNode(ev) {
        ev.preventDefault();
        const type = ev.dataTransfer.getData("node");
        addNodeToEditor(type, ev.clientX, ev.clientY);
    }

    document.querySelectorAll('.node-item').forEach(item => {
        item.addEventListener('dragstart', (ev) => {
            ev.dataTransfer.setData("node", ev.target.dataset.type);
        });
    });

    function addNodeToEditor(type, pos_x, pos_y) {
        if (editor.editor_mode === 'view') return;

        pos_x = pos_x * (editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)) - (editor.precanvas.getBoundingClientRect().x * (editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)));
        pos_y = pos_y * (editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)) - (editor.precanvas.getBoundingClientRect().y * (editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)));

        let nodeData = {
            trigger: {
                title: 'Trigger',
                color: 'warning',
                icon: 'bolt',
                inputs: 0,
                outputs: 1
            },
            agent: {
                title: 'AI Agent',
                color: 'primary',
                icon: 'robot',
                inputs: 1,
                outputs: 1
            },
            database: {
                title: 'Database',
                color: 'success',
                icon: 'database',
                inputs: 1,
                outputs: 1
            },
            condition: {
                title: 'Condition',
                color: 'info',
                icon: 'code-branch',
                inputs: 1,
                outputs: 2
            },
            notification: {
                title: 'Notification',
                color: 'danger',
                icon: 'bell',
                inputs: 1,
                outputs: 0
            },
            telecalling: {
                title: 'AI Telecall',
                color: 'dark',
                icon: 'phone-alt',
                inputs: 1,
                outputs: 1
            },
            sms: {
                title: 'SMS',
                color: 'info',
                icon: 'sms',
                inputs: 1,
                outputs: 1
            },
            calendar: {
                title: 'Calendar',
                color: 'primary',
                icon: 'calendar-alt',
                inputs: 1,
                outputs: 1
            },
            payment: {
                title: 'Payment',
                color: 'success',
                icon: 'credit-card',
                inputs: 1,
                outputs: 1
            },
            email: {
                title: 'Email',
                color: 'danger',
                icon: 'envelope',
                inputs: 1,
                outputs: 1
            },
            social_media: {
                title: 'Social Post',
                color: 'primary',
                icon: 'share-alt',
                inputs: 1,
                outputs: 1
            }
        };

        const data = nodeData[type] || nodeData['agent'];

        const html = `
        <div>
            <div class="title-box"><i class="fas fa-${data.icon}"></i> ${data.title}</div>
            <div class="box">
                <p class="small mb-1">Config:</p>
                <input type="text" df-name class="form-control form-control-xs mb-1" placeholder="Name">
                <button class="btn btn-xs btn-outline-secondary w-100" onclick="editNodeConfig(this)">Configure</button>
            </div>
        </div>
    `;

        editor.addNode(type, data.inputs, data.outputs, pos_x, pos_y, data.color, {}, html);
    }

    let editingNodeId = null;

    function editNodeConfig(btn) {
        editingNodeId = btn.closest('.drawflow-node').id.replace('node-', '');
        const nodeData = editor.getNodeFromId(editingNodeId);
        const config = nodeData.data || {};
        const type = nodeData.name;

        document.getElementById('nodeConfigTitle').innerText = `Configure ${type.toUpperCase()} Node`;
        const body = document.getElementById('nodeConfigBody');

        let html = `
        <input type="hidden" id="editNodeType" value="${type}">
        <div class="mb-3">
            <label class="form-label">Node Name</label>
            <input type="text" id="config_name" class="form-control" value="${config.name || type}">
        </div>
    `;

        switch (type) {
            case 'trigger':
                html += `
                <div class="alert alert-info small">This workflow will be triggered via manual execution or webhook ID: ${document.getElementById('currentWorkflowId').value || 'New'}</div>
            `;
                break;
            case 'agent':
                html += `
                <div class="mb-3">
                    <label class="form-label">Task Type</label>
                    <select id="config_task_type" class="form-select">
                        <option value="marketing" ${config.task_type === 'marketing' ? 'selected' : ''}>Marketing Copy</option>
                        <option value="analyze_response" ${config.task_type === 'analyze_response' ? 'selected' : ''}>Analyze Response</option>
                        <option value="decide" ${config.task_type === 'decide' ? 'selected' : ''}>Decision Making</option>
                    </select>
                </div>
            `;
                break;
            case 'database':
                html += `
                <div class="mb-3">
                    <label class="form-label">SQL Query</label>
                    <textarea id="config_query" class="form-control" rows="3" placeholder="e.g. UPDATE leads SET status='contacted' WHERE id={{nodes.1.lead_id}}">${config.query || ''}</textarea>
                    <small class="text-muted">Use {{nodes.ID.path}} for variables</small>
                </div>
            `;
                break;
            case 'condition':
                html += `
                <div class="mb-3">
                    <label class="form-label">Expression</label>
                    <input type="text" id="config_expression" class="form-control" placeholder="e.g. {{nodes.1.score}} > 50" value="${config.expression || ''}">
                </div>
            `;
                break;
            case 'notification':
                html += `
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea id="config_message" class="form-control" rows="2">${config.message || ''}</textarea>
                </div>
            `;
                break;
            case 'telecalling':
                html += `
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" id="config_phone_number" class="form-control" value="${config.phone_number || '{{trigger.phone}}'}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Call Script</label>
                    <textarea id="config_script" class="form-control" rows="3">${config.script || 'Hello {{trigger.name}}, calling regarding your property interest.'}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Goal</label>
                    <select id="config_goal" class="form-select">
                        <option value="qualification" ${config.goal === 'qualification' ? 'selected' : ''}>Lead Qualification</option>
                        <option value="appointment" ${config.goal === 'appointment' ? 'selected' : ''}>Book Appointment</option>
                        <option value="followup" ${config.goal === 'followup' ? 'selected' : ''}>Follow-up</option>
                    </select>
                </div>
            `;
                break;
        }

        body.innerHTML = html;
        new bootstrap.Modal(document.getElementById('nodeConfigModal')).show();
    }

    function saveNodeConfig() {
        const type = document.getElementById('editNodeType').value;
        const config = {
            name: document.getElementById('config_name').value
        };

        switch (type) {
            case 'agent':
                config.task_type = document.getElementById('config_task_type').value;
                break;
            case 'database':
                config.query = document.getElementById('config_query').value;
                break;
            case 'condition':
                config.expression = document.getElementById('config_expression').value;
                break;
            case 'notification':
                config.message = document.getElementById('config_message').value;
                break;
            case 'telecalling':
                config.phone_number = document.getElementById('config_phone_number').value;
                config.script = document.getElementById('config_script').value;
                config.goal = document.getElementById('config_goal').value;
                break;
        }

        editor.updateNodeDataFromId(editingNodeId, config);
        bootstrap.Modal.getInstance(document.getElementById('nodeConfigModal')).hide();
    }

    function saveWorkflow() {
        const name = document.getElementById('workflowName').value;
        const id = document.getElementById('currentWorkflowId').value;
        const exportData = editor.export();

        // Transform Drawflow export to our backend format
        const nodes = [];
        const connections = [];

        Object.keys(exportData.drawflow.Home.data).forEach(nodeId => {
            const node = exportData.drawflow.Home.data[nodeId];
            nodes.push({
                id: nodeId,
                name: node.data.name || node.name,
                type: node.name,
                config: node.data,
                pos: {
                    x: node.pos_x,
                    y: node.pos_y
                }
            });

            Object.keys(node.outputs).forEach(outKey => {
                node.outputs[outKey].connections.forEach(conn => {
                    connections.push({
                        from: nodeId,
                        to: conn.node,
                        from_port: outKey,
                        to_port: conn.output
                    });
                });
            });
        });

        const workflowData = {
            nodes: nodes,
            connections: connections
        };

        const formData = new FormData();
        if (id) formData.append('id', id);
        formData.append('name', name);
        formData.append('actions', JSON.stringify(workflowData)); // Using 'actions' column for the graph

        fetch('../api/ai/workflows.php?action=save_graph', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `csrf_token=${CSRF_TOKEN}&name=${encodeURIComponent(name)}&actions=${encodeURIComponent(JSON.stringify(workflowData))}${id ? `&id=${id}` : ''}`
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                document.getElementById('currentWorkflowId').value = data.id;
                alert("<?= h($mlSupport->translate('Workflow Graph saved successfully!')) ?>");
            }
        });
    }

    function loadExecutionLogs() {
        const id = document.getElementById('currentWorkflowId').value;
        if (!id) return;

        fetch(`../api/ai/workflows.php?action=logs&id=${id}`)
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('executionLogsList');
                if (data.status === 'success' && data.data.length > 0) {
                    list.innerHTML = data.data.map(log => {
                        const statusClass = log.status === 'success' ? 'success' : (log.status === 'failed' ? 'danger' : 'info');
                        const logs = JSON.parse(log.execution_log || '[]');
                        const summary = logs.map(l => l.node_name).join(' → ');
                        return `
                        <tr>
                            <td>${h(new Date(log.created_at).toLocaleString())}</td>
                            <td><span class="badge bg-${h(statusClass)}">${h(log.status)}</span></td>
                            <td>${h(log.duration_ms)}ms</td>
                            <td class="small">${h(summary) || "<?= h($mlSupport->translate('No steps recorded')) ?>"}</td>
                        </tr>
                    `;
                    }).join('');
                } else {
                    list.innerHTML = '<tr><td colspan="4" class="text-center py-4"><?= h($mlSupport->translate('No recent executions')) ?></td></tr>';
                }
            });
    }

    function executeWorkflow() {
        const id = document.getElementById('currentWorkflowId').value;
        if (!id) return alert("<?= h($mlSupport->translate('Please save the workflow first!')) ?>");

        fetch(`../api/ai/workflows.php?action=execute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${id}&csrf_token=${CSRF_TOKEN}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("<?= h($mlSupport->translate('Execution started!')) ?>");
                    loadExecutionLogs();
                } else {
                    alert("<?= h($mlSupport->translate('Error:')) ?> " + (data.message || "<?= h($mlSupport->translate('Unknown error')) ?>"));
                }
            });
    }

    function testBot() {
        const query = document.getElementById('testQuery').value;
        const chat = document.getElementById('botChatBox');

        if (!query) return;

        // Add user message
        const userMsg = document.createElement('div');
        userMsg.className = 'mb-2 text-end';
        userMsg.innerHTML = `<span class="badge bg-light text-dark p-2" style="white-space: normal; max-width: 80%;">${h(query)}</span>`;
        chat.appendChild(userMsg);
        document.getElementById('testQuery').value = '';
        chat.scrollTop = chat.scrollHeight;

        fetch('../api/ai/test_bot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `csrf_token=${CSRF_TOKEN}&message=${encodeURIComponent(query)}&mode=assistant`
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                const botMsg = document.createElement('div');
                botMsg.className = 'mb-2';
                botMsg.innerHTML = `
                <div class="d-flex align-items-start">
                    <i class="fas fa-robot text-primary mt-1 me-2"></i>
                    <div>
                        <div class="badge bg-primary p-2 mb-1" style="white-space: normal; text-align: left;">${h(data.response)}</div>
                        <div class="small text-muted" style="font-size: 9px;">
                            <?= h($mlSupport->translate('Intent:')) ?> ${h(data.intent)} |
                            <?= h($mlSupport->translate('Sentiment:')) ?> ${h(data.sentiment)}
                        </div>
                    </div>
                </div>
            `;
                chat.appendChild(botMsg);
                chat.scrollTop = chat.scrollHeight;
            }
        });
    }

    function triggerLearning() {
        alert("<?= h($mlSupport->translate('AI self-learning session started. Processing historical logs and feedback...')) ?>");
        setTimeout(() => {
            document.getElementById('lastOpt').innerText = "<?= h($mlSupport->translate('Just now')) ?>";
            alert("<?= h($mlSupport->translate('Learning complete. AI policies updated.')) ?>");
        }, 2000);
    }

    function integrateTool(name, cat) {
        if (!confirm("<?= h($mlSupport->translate('Do you want to integrate')) ?> " + name + " <?= h($mlSupport->translate('into your AI ecosystem?')) ?>")) return;

        fetch('../api/ai/integrate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `csrf_token=${CSRF_TOKEN}&tool_name=${encodeURIComponent(name)}&category=${encodeURIComponent(cat)}`
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadEcosystem(); // Refresh ecosystem stats
            }
        });
    }

    function setLang(code) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', code);
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('aiPerformanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Success',
                    data: <?= json_encode($chart_success) ?>,
                    borderColor: '#28a745',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(40, 167, 69, 0.1)'
                }, {
                    label: 'Failed',
                    data: <?= json_encode($chart_failed) ?>,
                    borderColor: '#dc3545',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(220, 53, 69, 0.1)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>