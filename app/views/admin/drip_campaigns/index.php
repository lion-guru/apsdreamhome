<?php
$page_title = $page_title ?? 'Drip Campaigns';
$page_heading = $page_heading ?? 'Drip Campaigns';
$content = $content ?? '';
$stats = $stats ?? [];
$campaigns = $campaigns ?? [];
$last_process = $last_process ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Drip Campaigns</h2>
            <p class="text-muted mb-0">Automated lead nurturing email sequences</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/drip-campaigns/process" class="btn btn-outline-info" onclick="return confirm('Process pending emails now?')">
                <i class="fas fa-cogs me-1"></i> Process Queue
            </a>
            <a href="<?= BASE_URL ?>/admin/drip-campaigns/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Campaign
            </a>
        </div>
    </div>

    <?php if (!empty($last_process['processed'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            Last queue run: Processed <strong><?= $last_process['processed'] ?? 0 ?></strong> enrollments,
            sent <strong><?= $last_process['sent'] ?? 0 ?></strong> emails,
            completed <strong><?= $last_process['completed'] ?? 0 ?></strong> sequences
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Total Campaigns</p>
                    <h3><?= number_format($stats['total_campaigns'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['active_campaigns'] ?? 0 ?> active, <?= $stats['draft_campaigns'] ?? 0 ?> draft</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Total Enrollments</p>
                    <h3 class="text-info"><?= number_format($stats['total_enrollments'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['active_enrollments'] ?? 0 ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Emails Sent Today</p>
                    <h3 class="text-success"><?= number_format($stats['emails_sent_today'] ?? 0) ?></h3>
                    <small class="text-muted"><?= number_format($stats['emails_sent_week'] ?? 0) ?> this week</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Completion Rate</p>
                    <h3 class="text-warning"><?= number_format($stats['avg_completion_rate'] ?? 0, 1) ?>%</h3>
                    <small class="text-muted"><?= $stats['completed_enrollments'] ?? 0 ?> completed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Campaigns</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Enrolled</th>
                            <th>Completed</th>
                            <th>Emails Sent</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campaigns)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No campaigns yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($campaigns as $c): ?>
                                <tr>
                                    <td>#<?= $c['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($c['name']) ?></strong>
                                        <?php if ($c['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($c['description'], 0, 60)) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $c['trigger_event'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ['active' => 'success', 'paused' => 'warning', 'draft' => 'secondary', 'archived' => 'dark'][$c['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($c['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($c['total_enrolled']) ?></td>
                                    <td><?= number_format($c['total_completed']) ?></td>
                                    <td><?= number_format($c['emails_sent']) ?></td>
                                    <td><small><?= date('M j, Y', strtotime($c['created_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/drip-campaigns/show/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/drip-campaigns/toggle?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-<?= $c['status'] === 'active' ? 'warning' : 'success' ?>" title="<?= $c['status'] === 'active' ? 'Pause' : 'Activate' ?>">
                                            <i class="fas fa-<?= $c['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/drip-campaigns/delete?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this campaign and all its data?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/unified.php';
