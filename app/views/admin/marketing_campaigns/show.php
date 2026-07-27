<?php
$page_title = $page_title ?? 'Campaign Details';
$page_heading = $page_heading ?? 'Campaign Details';
$content = $content ?? '';
$campaign = $campaign ?? [];
$recipients = $recipients ?? [];
$status_breakdown = $status_breakdown ?? [];
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($campaign['name']) ?></h2>
            <p class="text-muted mb-0">
                <span class="badge bg-<?= ['draft' => 'secondary', 'sent' => 'success', 'scheduled' => 'info', 'sending' => 'warning'][$campaign['status']] ?? 'secondary' ?>">
                    <?= ucfirst($campaign['status']) ?>
                </span>
                · <?= ucfirst($campaign['type']) ?> campaign · Created <?= date('M j, Y', strtotime($campaign['created_at'])) ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/marketing-campaigns" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <?php if (($campaign['status'] ?? '') === 'draft'): ?>
                <a href="<?= BASE_URL ?>/admin/marketing-campaigns/send/<?= $campaign['id'] ?>" class="btn btn-success" onclick="return confirm('Send this campaign now?')">
                    <i class="fas fa-paper-plane me-1"></i> Send Now
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Total Recipients</p>
                    <h3><?= number_format($campaign['total_recipients'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Delivered</p>
                    <h3 class="text-success"><?= number_format($campaign['delivered_count'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Opened</p>
                    <h3 class="text-info"><?= number_format($campaign['opened_count'] ?? 0) ?></h3>
                    <small class="text-muted">
                        <?= ($campaign['delivered_count'] ?? 0) > 0 ? round((($campaign['opened_count'] ?? 0) / $campaign['delivered_count']) * 100, 1) : 0 ?>% rate
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Clicked</p>
                    <h3 class="text-warning"><?= number_format($campaign['clicked_count'] ?? 0) ?></h3>
                    <small class="text-muted">
                        <?= ($campaign['delivered_count'] ?? 0) > 0 ? round((($campaign['clicked_count'] ?? 0) / $campaign['delivered_count']) * 100, 1) : 0 ?>% rate
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i>Message</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($campaign['subject'])): ?>
                        <p class="fw-bold"><?= htmlspecialchars($campaign['subject']) ?></p>
                    <?php endif; ?>
                    <pre class="bg-light p-3 rounded mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($campaign['content']) ?></pre>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Recipient Status</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($status_breakdown)): ?>
                        <p class="text-muted mb-0">No recipients yet</p>
                    <?php else: ?>
                        <?php foreach ($status_breakdown as $s => $count): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= ucfirst($s) ?></span>
                                <strong><?= $count ?></strong>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-<?= ['delivered' => 'success', 'sent' => 'info', 'opened' => 'info', 'clicked' => 'warning', 'failed' => 'danger', 'bounced' => 'danger', 'unsubscribed' => 'secondary'][$s] ?? 'secondary' ?>"
                                     style="width: <?= ($count / max(array_sum($status_breakdown), 1)) * 100 ?>%"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recipients (<?= count($recipients) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Delivered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recipients)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No recipients</td></tr>
                        <?php else: ?>
                            <?php foreach ($recipients as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['name'] ?? 'Anonymous') ?></td>
                                    <td><small><?= htmlspecialchars($r['email'] ?? $r['phone'] ?? 'N/A') ?></small></td>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($r['channel']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= ['delivered' => 'success', 'sent' => 'info', 'opened' => 'info', 'clicked' => 'warning', 'failed' => 'danger', 'bounced' => 'danger', 'unsubscribed' => 'secondary', 'pending' => 'secondary'][$r['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= $r['delivered_at'] ? date('M j, H:i', strtotime($r['delivered_at'])) : '—' ?></small></td>
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
