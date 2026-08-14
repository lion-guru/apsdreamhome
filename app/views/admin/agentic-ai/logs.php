<?php
$_logs = $logs ?? [];
$_ag = $agents ?? [];
$_filter = $filter ?? '';
$_date = $date ?? date('Y-m-d');
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-list" style="color:#666"></i> Agent Logs</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= BASE_URL ?>/admin/agentic-ai" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card card-outline mb-3">
                <div class="card-body">
                    <form method="GET" class="form-inline">
    <?php echo CSRFProtection::csrfField(); ?>
                        <label class="mr-2">Agent:</label>
                        <select name="agent" class="form-control form-control-sm mr-3">
                            <option value="">All Agents</option>
                            <?php foreach ($_ag as $key => $a): ?>
                            <option value="<?= $key ?>" <?= $_filter === $key ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="mr-2">Date:</label>
                        <input type="date" name="date" value="<?= $_date ?>" class="form-control form-control-sm mr-3">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> Filter</button>
                    </form>
                </div>
            </div>

            <div class="card card-outline">
                <div class="card-body p-0">
                    <?php if (empty($_logs)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                        <p>No logs found for this date/agent.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr><th>Time</th><th>Agent</th><th>Task</th><th>Status</th><th>Confidence</th><th>Result</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($_logs as $l): ?>
                            <tr>
                                <td class="text-muted small" style="white-space:nowrap"><?= date('H:i:s', strtotime($l['created_at'])) ?></td>
                                <td>
                                    <?php $a = $_ag[$l['agent_type']] ?? ['name' => $l['agent_type'], 'color' => '#666', 'icon' => 'fa-robot']; ?>
                                    <span style="color:<?= $a['color'] ?>"><i class="fas <?= $a['icon'] ?>"></i> <?= htmlspecialchars($a['name']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($l['task_name']) ?></td>
                                <td>
                                    <?php
                                    $sc = ['completed' => 'success', 'running' => 'info', 'failed' => 'danger', 'escalated' => 'warning', 'pending' => 'secondary'];
                                    $badge = $sc[$l['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $badge ?>"><?= $l['status'] ?></span>
                                </td>
                                <td><?= round(($l['confidence'] ?? 0) * 100) ?>%</td>
                                <td class="small text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    <?= htmlspecialchars($l['result'] ? mb_substr($l['result'], 0, 100) : '-') ?>
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
    </section>
</div>
