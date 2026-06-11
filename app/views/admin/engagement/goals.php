<?php
$pageTitle = $pageTitle ?? 'Engagement Goals';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$goals = $goals ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-bullseye me-2 text-primary"></i>Engagement Goals</h1>
        <a href="<?= $base ?>/admin/engagement" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Back</a>
        <a href="<?= $base ?>/admin/engagement/create-goal" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Goal</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Goals List</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($goals)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-flag fa-2x d-block mb-2"></i>No engagement goals defined yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Target</th>
                                <th>Progress</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($goals as $g): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($g['name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($g['target'] ?? $g['target_value'] ?? '') ?></td>
                                <td>
                                    <?php $progress = intval($g['progress'] ?? 0); ?>
                                    <div class="progress" style="height:20px">
                                        <div class="progress-bar bg-<?= $progress >= 100 ? 'success' : ($progress >= 50 ? 'info' : 'warning') ?>" role="progressbar" style="width:<?= min($progress, 100) ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"><?= $progress ?>%</div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($g['deadline'] ?? '') ?></td>
                                <td>
                                    <?php $s = $g['status'] ?? ''; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : ($s === 'completed' ? 'primary' : ($s === 'cancelled' ? 'danger' : 'secondary')) ?>"><?= ucfirst($s) ?: 'Unknown' ?></span>
                                </td>
                                <td>
                                    <a href="<?= $base ?>/admin/engagement/goals/<?= $g['id'] ?? 0 ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="<?= $base ?>/admin/engagement/goals/<?= $g['id'] ?? 0 ?>/edit" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
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
