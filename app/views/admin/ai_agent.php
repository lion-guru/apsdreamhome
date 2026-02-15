<?php
/**
 * AI Agent - Standardized Version
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Agent\Agent;

$agent = new Agent();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'run') {
            $res = $agent->runDailyOps();
            $message = $res['planned'];
        }
        if ($action === 'report') {
            $res = $agent->generateReport();
            $message = $res['report'];
        }
    }
}

$logFile = __DIR__ . '/../storage/logs/agent.log';
$logs = file_exists($logFile) ? array_slice(array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), 0, 20) : [];

$page_title = "AI Agent";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">AI Agent</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">AI Agent</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="post" class="d-flex gap-2">
                            <?= getCsrfField() ?>
                            <button name="action" value="run" class="btn btn-primary">
                                <i class="fas fa-play"></i> Run Strategy
                            </button>
                            <button name="action" value="report" class="btn btn-secondary">
                                <i class="fas fa-file-alt"></i> Generate Report
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="card-title mb-0">Recent Logs</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php if (empty($logs)): ?>
                                <li class="list-group-item text-center text-muted">No logs found.</li>
                            <?php else: ?>
                                <?php foreach ($logs as $l): ?>
                                    <li class="list-group-item small text-muted">
                                        <i class="fas fa-terminal me-2"></i> <?= h($l) ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>



