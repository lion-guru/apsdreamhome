<?php
$pageTitle = $pageTitle ?? 'Loyalty Rules';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
$rules = $rules ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-cogs me-2 text-primary"></i>Loyalty Rules</h1>
        <a href="<?= $base ?>/admin/loyalty" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">Points Award Rules</h6>
            <a href="<?= $base ?>/admin/commission/create-rule" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>New Rule</a>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($rules)): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-cogs fa-2x d-block mb-2"></i>No loyalty rules configured.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Points Awarded</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td><span class="badge bg-info"><?= htmlspecialchars($rule['event'] ?? $rule['name'] ?? '') ?></span></td>
                                <td><strong class="text-success">+<?= number_format(intval($rule['points'] ?? $rule['points_awarded'] ?? 0)) ?> pts</strong></td>
                                <td><?= htmlspecialchars($rule['description'] ?? '') ?></td>
                                <td>
                                    <?php $s = $rule['status'] ?? 'active'; ?>
                                    <span class="badge bg-<?= $s === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($s) ?></span>
                                </td>
                                <td>
                                    <a href="<?= $base ?>/admin/commission/edit-rule/<?= $rule['id'] ?? 0 ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
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
