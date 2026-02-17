<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/ai/AIManager.php';
require_once __DIR__ . '/../includes/ai/AIAdvancedAgent.php';
require_once __DIR__ . '/../includes/ai/AIToolsManager.php';

$page_title = "AI Knowledge & Growth Hub";
$aiManager = new AIManager();
$advancedAgent = new AIAdvancedAgent($aiManager);
$toolsManager = new AIToolsManager();

$userId = $_SESSION['user_id'] ?? 1; // Default for demo

$db = \App\Core\App::database();

// Handle Mode Switch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_mode'])) {
    if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $advancedAgent->setMode($userId, $_POST['mode']);
        log_admin_activity('switch_ai_mode', 'Switched AI mode to: ' . $_POST['mode']);
        $success_msg = $mlSupport->translate('AI Mode updated successfully!');
    } else {
        $error_msg = $mlSupport->translate('Invalid CSRF token.');
    }
}

// Fetch State
$agentState = $db->fetch("SELECT * FROM ai_agent_state WHERE user_id = :user_id", ['user_id' => $userId]);
$currentMode = $agentState['current_mode'] ?? 'assistant';

// Fetch Recommendations
$recommendations = $toolsManager->getRecommendations($userId);
$learningProgress = $db->fetchAll("SELECT * FROM ai_learning_progress WHERE user_id = :user_id", ['user_id' => $userId]);

require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php"><?= h($mlSupport->translate('Dashboard')) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?= h($mlSupport->translate('AI Knowledge Hub')) ?></li>
                        </ol>
                    </nav>
                    <h3 class="page-title"><?= h($mlSupport->translate('AI Knowledge & Growth Hub')) ?></h3>
                    <p class="text-muted"><?= h($mlSupport->translate('Master 1000+ AI Tools & Manage your AI Workforce')) ?></p>
                </div>
                <div class="col-auto">
                    <form method="POST" class="d-inline">
                        <?= getCsrfField() ?>
                        <input type="hidden" name="switch_mode" value="1">
                        <div class="btn-group shadow-sm">
                            <button type="submit" name="mode" value="assistant" class="btn btn-<?= $currentMode == 'assistant' ? 'primary' : 'outline-primary' ?> px-3">
                                <i class="fas fa-user-tie me-2"></i><?= h($mlSupport->translate('Assistant Mode')) ?>
                            </button>
                            <button type="submit" name="mode" value="leader" class="btn btn-<?= $currentMode == 'leader' ? 'success' : 'outline-success' ?> px-3">
                                <i class="fas fa-crown me-2"></i><?= h($mlSupport->translate('Leader Mode')) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= h($success_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= h($error_msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Learning Progress Dashboard -->
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-graduation-cap me-2"></i><?= h($mlSupport->translate('My Learning Progress')) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (empty($learningProgress)): ?>
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-book-open fa-3x text-muted mb-3 opacity-25"></i>
                                    <p class="text-muted mb-3"><?= h($mlSupport->translate('No learning modules started yet.')) ?></p>
                                    <button class="btn btn-primary rounded-pill px-4"><?= h($mlSupport->translate('Start Initial Assessment')) ?></button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($learningProgress as $module): ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="p-3 border rounded shadow-sm bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 fw-bold"><?= h($module['module_name']) ?></h6>
                                                <span class="badge bg-<?= $module['status'] == 'completed' ? 'success' : 'info' ?> rounded-pill px-3">
                                                    <?= h(strtoupper($mlSupport->translate($module['status']))) ?>
                                                </span>
                                            </div>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $module['status'] == 'completed' ? '100' : '45' ?>%" aria-valuenow="<?= $module['status'] == 'completed' ? '100' : '45' ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted"><?= h($mlSupport->translate('Score:')) ?> <?= h($module['score']) ?>%</small>
                                                <small class="text-primary fw-medium"><?= h($mlSupport->translate('Continue Module')) ?> <i class="fas fa-arrow-right ms-1 small"></i></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- AI Tools Directory & Recommendations -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-tools me-2"></i><?= h($mlSupport->translate('Recommended AI Tools for You')) ?>
                        </h4>
                        <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3"><?= h($mlSupport->translate('Browse All 1000+ Tools')) ?></a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0"><?= h($mlSupport->translate('Tool Name')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Category')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Complexity')) ?></th>
                                        <th class="border-0"><?= h($mlSupport->translate('Relevance')) ?></th>
                                        <th class="border-0 text-center"><?= h($mlSupport->translate('Action')) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recommendations as $tool): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= h($tool['name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $tool['category'] == 'free' ? 'success' : 'info' ?> rounded-pill px-3">
                                                <?= h(strtoupper($mlSupport->translate($tool['category']))) ?>
                                            </span>
                                        </td>
                                        <td class="small text-uppercase fw-medium"><?= h($mlSupport->translate($tool['integration_complexity'])) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress rounded-pill flex-grow-1 me-2" style="height: 6px; width: 80px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= ($tool['relevance_score'] ?? 5) * 10 ?>%" aria-valuenow="<?= ($tool['relevance_score'] ?? 5) * 10 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted fw-bold"><?= ($tool['relevance_score'] ?? 5) * 10 ?>%</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-info rounded-pill px-3">
                                                <i class="fas fa-info-circle me-1 small"></i><?= h($mlSupport->translate('Guide')) ?>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agent Style & Multilingual Support -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-cog me-2"></i><?= h($mlSupport->translate('Agent Configuration')) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><?= h($mlSupport->translate('Multilingual Support')) ?></h6>
                        <select class="form-select mb-4 shadow-sm">
                            <option value="en">English (Primary)</option>
                            <option value="hi">Hindi (हिन्दी)</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="zh">Chinese</option>
                            <option value="ja">Japanese</option>
                            <option value="ar">Arabic</option>
                            <option value="ru">Russian</option>
                            <option value="pt">Portuguese</option>
                        </select>

                        <hr class="my-4 opacity-50">

                        <h6 class="fw-bold mb-2"><?= h($mlSupport->translate('Mode:')) ?> <?= h(strtoupper($mlSupport->translate($currentMode))) ?></h6>
                        <p class="small text-muted mb-4">
                            <?php if ($currentMode == 'assistant'): ?>
                                <?= h($mlSupport->translate('Agent is currently following detailed instructions and maintaining execution logs. Ideal for operational tasks.')) ?>
                            <?php else: ?>
                                <?= h($mlSupport->translate('Agent is currently in leadership mode, making strategic decisions and coordinating resources autonomously.')) ?>
                            <?php endif; ?>
                        </p>
                        
                        <div class="alert alert-info py-2 px-3 small border-0 shadow-sm d-flex align-items-center mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <span><?= h($mlSupport->translate('Context-sensitive interface is active.')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Industry Specifics -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h4 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-briefcase me-2"></i><?= h($mlSupport->translate('Industry Context')) ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><?= h($mlSupport->translate('Primary Industry')) ?></label>
                                <select class="form-select shadow-sm">
                                    <option value="real_estate"><?= h($mlSupport->translate('Real Estate (Current)')) ?></option>
                                    <option value="healthcare"><?= h($mlSupport->translate('Healthcare')) ?></option>
                                    <option value="finance"><?= h($mlSupport->translate('Finance')) ?></option>
                                    <option value="education"><?= h($mlSupport->translate('Education')) ?></option>
                                    <option value="retail"><?= h($mlSupport->translate('Retail')) ?></option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold"><?= h($mlSupport->translate('Organizational Level')) ?></label>
                                <select class="form-select shadow-sm">
                                    <option value="executive"><?= h($mlSupport->translate('Executive (Strategic)')) ?></option>
                                    <option value="managerial"><?= h($mlSupport->translate('Managerial (Coordination)')) ?></option>
                                    <option value="operational" selected><?= h($mlSupport->translate('Operational (Execution)')) ?></option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary w-100 shadow-sm rounded-pill py-2">
                                <?= h($mlSupport->translate('Update Profile')) ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>


