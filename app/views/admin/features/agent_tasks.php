<?php
$page_title = $page_title ?? 'Agent Tasks';
$page_heading = $page_heading ?? 'Agent Tasks & Workflows';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-robot me-2"></i>Agent Tasks & Workflows</h1>

  <form method="POST" action="<?= BASE_URL ?>/api/v2/agent/tasks/process" class="mb-3">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <button class="btn btn-primary"><i class="fas fa-play me-1"></i> Process Pending Tasks</button>
  </form>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tsk">Tasks</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#exe">Executions</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#wfl">Workflows</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tsk">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>ID</th><th>Agent</th><th>Type</th><th>Priority</th><th>Status</th><th>Created</th></tr></thead>
          <tbody>
            <?php if (empty($tasks)): ?>
              <tr><td colspan="6" class="text-center py-3 text-muted">No tasks</td></tr>
            <?php else: foreach ($tasks as $t): ?>
              <tr>
                <td>#<?= htmlspecialchars($t['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($t['agent_name'] ?? $t['agent_id'] ?? '') ?></td>
                <td><code><?= htmlspecialchars($t['task_type'] ?? '') ?></code></td>
                <td><span class="badge bg-<?= ($t['priority'] ?? 0) > 5 ? 'danger' : 'secondary' ?>"><?= htmlspecialchars($t['priority'] ?? '') ?></span></td>
                <td><span class="badge bg-<?= ($t['status'] ?? '') === 'completed' ? 'success' : (($t['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= htmlspecialchars($t['status'] ?? '') ?></span></td>
                <td><small><?= htmlspecialchars($t['created_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="exe">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Task</th><th>Agent</th><th>Status</th><th>Duration</th><th>Completed</th></tr></thead>
          <tbody>
            <?php if (empty($executions)): ?>
              <tr><td colspan="5" class="text-center py-3 text-muted">No executions</td></tr>
            <?php else: foreach ($executions as $e): ?>
              <tr>
                <td><code><?= htmlspecialchars($e['task_type'] ?? '') ?></code></td>
                <td><?= htmlspecialchars($e['agent_name'] ?? '') ?></td>
                <td><span class="badge bg-<?= ($e['status'] ?? '') === 'success' ? 'success' : 'danger' ?>"><?= htmlspecialchars($e['status'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($e['duration_ms'] ?? '') ?>ms</td>
                <td><small><?= htmlspecialchars($e['completed_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="wfl">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Name</th><th>Trigger</th><th>Status</th><th>Created</th></tr></thead>
          <tbody>
            <?php if (empty($workflows)): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No workflows</td></tr>
            <?php else: foreach ($workflows as $w): ?>
              <tr>
                <td><strong><?= htmlspecialchars($w['workflow_name'] ?? '') ?></strong></td>
                <td><code><?= htmlspecialchars($w['trigger_event'] ?? '') ?></code></td>
                <td><span class="badge bg-<?= ($w['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($w['status'] ?? '') ?></span></td>
                <td><small><?= htmlspecialchars($w['created_at'] ?? '') ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
