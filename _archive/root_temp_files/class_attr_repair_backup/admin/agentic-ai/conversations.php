ï»¿<?php
$_convs = $conversations ?? [];
$_ag = $agents ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-comments" class="style-64047"></i> All Conversations</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= BASE_URL ?>/admin/agentic-ai" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline">
                <div class="card-body p-0">
                    <?php if (empty($_convs)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                        <p>No conversations yet.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>ID</th><th>Lead</th><th>Channel</th><th>Agent</th><th>Status</th><th>Last Message</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($_convs as $c): ?>
                            <tr>
                                <td>#<?= $c['id'] ?></td>
                                <td>
                                    <?php if ($c['lead_name']): ?>
                                    <strong><?= htmlspecialchars($c['lead_name'] ?? '') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($c['lead_phone'] ?? '') ?></small>
                                    <?php else: ?>
                                    <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-light"><?= $c['channel'] ?></span></td>
                                <td><?= htmlspecialchars($c['agent_name'] ?? 'Unassigned') ?></td>
                                <td>
                                    <?php
                                    $sc = ['active' => 'success', 'resolved' => 'secondary', 'escalated' => 'warning'];
                                    $badge = $sc[$c['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $badge ?>"><?= $c['status'] ?></span>
                                </td>
                                <td class="small text-muted" class="style-82232"><?= htmlspecialchars($c['last_message'] ?? '') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/agentic-ai/conversation/<?= $c['id'] ?>" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a>
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
