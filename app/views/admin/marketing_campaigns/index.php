<?php
$page_title = $page_title ?? 'Marketing Campaigns';
$page_heading = $page_heading ?? 'Marketing Campaigns';
$content = $content ?? '';
$stats = $stats ?? [];
$campaigns = $campaigns ?? [];
$templates = $templates ?? [];
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Marketing Campaigns</h2>
            <p class="text-muted mb-0">Create, schedule, and track email, SMS, and WhatsApp campaigns</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/marketing-campaigns/templates" class="btn btn-outline-secondary">
                <i class="fas fa-file-alt me-1"></i> Templates
            </a>
            <a href="<?= BASE_URL ?>/admin/marketing-campaigns/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Campaign
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Total Campaigns</p>
                    <h3 class="mb-0"><?= number_format($stats['total_campaigns'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['draft'] ?? 0 ?> drafts, <?= $stats['scheduled'] ?? 0 ?> scheduled</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Sent</p>
                    <h3 class="mb-0 text-success"><?= number_format($stats['sent'] ?? 0) ?></h3>
                    <small class="text-muted"><?= number_format($stats['total_sent'] ?? 0) ?> total messages</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Avg Open Rate</p>
                    <h3 class="mb-0 text-info"><?= number_format($stats['avg_open_rate'] ?? 0, 1) ?>%</h3>
                    <small class="text-muted"><?= number_format($stats['total_opened'] ?? 0) ?> opens</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Avg Click Rate</p>
                    <h3 class="mb-0 text-warning"><?= number_format($stats['avg_click_rate'] ?? 0, 1) ?>%</h3>
                    <small class="text-muted"><?= number_format($stats['total_clicked'] ?? 0) ?> clicks</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach (($stats['by_type'] ?? []) as $bt): ?>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body aps-cp-card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1"><?= ucfirst($bt['type']) ?> Campaigns</p>
                                <h4 class="mb-0"><?= $bt['count'] ?></h4>
                                <small class="text-muted"><?= number_format($bt['sent'] ?? 0) ?> sent</small>
                            </div>
                            <div>
                                <?php
                                $icon = ['email' => 'fa-envelope', 'sms' => 'fa-sms', 'whatsapp' => 'fa-whatsapp', 'push' => 'fa-bell', 'multi' => 'fa-layer-group'][$bt['type']] ?? 'fa-paper-plane';
                                $color = ['email' => 'primary', 'sms' => 'info', 'whatsapp' => 'success', 'push' => 'warning', 'multi' => 'secondary'][$bt['type']] ?? 'secondary';
                                ?>
                                <i class="fab <?= $bt['type'] === 'whatsapp' ? 'fa-whatsapp' : 'fas ' . $icon ?> text-<?= $color ?> fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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
                            <th>Type</th>
                            <th>Status</th>
                            <th>Recipients</th>
                            <th>Delivered</th>
                            <th>Opened</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campaigns)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No campaigns yet. <a href="<?= BASE_URL ?>/admin/marketing-campaigns/create">Create your first one</a></td></tr>
                        <?php else: ?>
                            <?php foreach ($campaigns as $c):
                                $statusClass = ['draft' => 'secondary', 'sent' => 'success', 'scheduled' => 'info', 'sending' => 'warning', 'paused' => 'secondary', 'cancelled' => 'danger'][$c['status']] ?? 'secondary';
                            ?>
                                <tr>
                                    <td>#<?= $c['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($c['name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($c['creator_name'] ?? 'System') ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $icons = ['email' => 'fa-envelope text-primary', 'sms' => 'fa-sms text-info', 'whatsapp' => 'fab fa-whatsapp text-success', 'push' => 'fa-bell text-warning', 'multi' => 'fa-layer-group text-secondary'];
                                        ?>
                                        <i class="<?= $icons[$c['type']] ?? 'fa-paper-plane' ?>"></i>
                                        <?= ucfirst($c['type']) ?>
                                    </td>
                                    <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($c['status']) ?></span></td>
                                    <td><?= number_format($c['total_recipients'] ?? 0) ?></td>
                                    <td><?= number_format($c['delivered_count'] ?? 0) ?></td>
                                    <td>
                                        <?= number_format($c['opened_count'] ?? 0) ?>
                                        <?php if (($c['delivered_count'] ?? 0) > 0): ?>
                                            <br><small class="text-muted"><?= round((($c['opened_count'] ?? 0) / $c['delivered_count']) * 100, 1) ?>%</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= date('M j, Y', strtotime($c['created_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/marketing-campaigns/show/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <?php if ($c['status'] === 'draft'): ?>
                                            <a href="<?= BASE_URL ?>/admin/marketing-campaigns/send/<?= $c['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Send this campaign now?')"><i class="fas fa-paper-plane"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/admin/marketing-campaigns/delete?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this campaign?')"><i class="fas fa-trash"></i></a>
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
include APP_PATH . '/views/layouts/admin.php';
