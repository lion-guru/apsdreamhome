<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php';
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin','superadmin']);
if (!$isAdmin) { http_response_code(403); echo 'Forbidden'; exit; }
use App\Core\Agent\Agent;
$agent = new Agent();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'run') { $res = $agent->runDailyOps(); $message = $res['planned']; }
    if ($action === 'report') { $res = $agent->generateReport(); $message = $res['report']; }
}
$logFile = __DIR__ . '/../storage/logs/agent.log';
$logs = file_exists($logFile) ? array_slice(array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)), 0, 20) : [];
?><!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>AI Agent</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body class="bg-light"><div class="container py-4"><h1 class="h3 mb-3">AI Agent</h1><form method="post" class="d-flex gap-2 mb-3"><button name="action" value="run" class="btn btn-primary">Run Strategy</button><button name="action" value="report" class="btn btn-secondary">Generate Report</button></form><?php if ($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?><div class="card"><div class="card-header">Recent Logs</div><div class="card-body"><ul class="list-group list-group-flush"><?php foreach ($logs as $l): ?><li class="list-group-item small text-muted"><?=htmlspecialchars($l)?></li><?php endforeach; ?></ul></div></div></div></body></html>

