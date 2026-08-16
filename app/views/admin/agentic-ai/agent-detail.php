ï»¿<?php
$_agent = $agent ?? ['name' => 'Agent', 'color' => '#666', 'icon' => 'fa-robot', 'description' => ''];
$_type = $agent_type ?? '';
$_tasks = $tasks ?? [];
$_insights = $insights ?? [];
$_escalations = $escalations ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas <?= $_agent['icon'] ?>" class="style-38863"></i> <?= htmlspecialchars($_agent['name'] ?? '') ?></h1>
                    <small class="text-muted"><?= htmlspecialchars($_agent['description'] ?? '') ?></small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= BASE_URL ?>/admin/agentic-ai" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Tasks -->
                <div class="col-lg-8">
                    <div class="card card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tasks"></i> Task History (Last 50)</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($_tasks)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                                <p>No tasks executed yet.</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead><tr><th>Time</th><th>Task</th><th>Status</th><th>Confidence</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($_tasks as $t): ?>
                                    <tr>
                                        <td class="text-muted small"><?= date('M d, H:i', strtotime($t['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($t['task_name'] ?? '') ?></td>
                                        <?php
                                        $sc = ['completed' => 'success', 'running' => 'info', 'failed' => 'danger', 'escalated' => 'warning', 'pending' => 'secondary'];
                                        $badge = $sc[$t['status']] ?? 'secondary';
                                        ?>
                                        <td><span class="badge badge-<?= $badge ?>"><?= $t['status'] ?></span></td>
                                        <td><?= round(($t['confidence'] ?? 0) * 100) ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Insights -->
                    <div class="card card-outline" class="style-93777">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-lightbulb"></i> Insights</h3></div>
                        <div class="card-body p-0">
                            <?php if (empty($_insights)): ?>
                            <div class="text-center py-3 text-muted small">No insights yet</div>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($_insights as $ins): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($ins['title'] ?? '') ?></strong>
                                    <p class="small text-muted mb-0"><?= htmlspecialchars($ins['summary'] ?? '') ?></p>
                                    <small class="text-muted"><?= date('M d, H:i', strtotime($ins['created_at'])) ?></small>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Escalations -->
                    <div class="card card-outline card-danger">
                        <div class="card-header"><h3 class="card-title" class="style-78822"><i class="fas fa-exclamation-triangle"></i> Pending Escalations</h3></div>
                        <div class="card-body p-0">
                            <?php if (empty($_escalations)): ?>
                            <div class="text-center py-3 text-muted small">No pending escalations</div>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($_escalations as $esc): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($esc['title'] ?? '') ?></strong>
                                    <p class="small text-muted mb-0"><?= htmlspecialchars($esc['description'] ?? '') ?></p>
                                    <small class="text-muted"><?= date('M d, H:i', strtotime($esc['created_at'])) ?></small>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
