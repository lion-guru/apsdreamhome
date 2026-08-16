<?php
$page_title = $page_title ?? 'Campaign Details';
$page_heading = $page_heading ?? 'Drip Campaign';
$content = $content ?? '';
$campaign = $campaign ?? [];
$emails = $emails ?? [];
$enrollments = $enrollments ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($campaign['name']) ?></h2>
            <p class="text-muted mb-0">
                <span class="badge bg-<?= ['active' => 'success', 'paused' => 'warning', 'draft' => 'secondary'][$campaign['status']] ?? 'secondary' ?>">
                    <?= ucfirst($campaign['status']) ?>
                </span>
                Â· Trigger: <strong><?= ucfirst(str_replace('_', ' ', $campaign['trigger_event'])) ?></strong>
                Â· <?= count($emails) ?> emails in sequence
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/drip-campaigns" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Enrolled</p>
                    <h3><?= number_format($campaign['total_enrolled']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Completed</p>
                    <h3 class="text-success"><?= number_format($campaign['total_completed']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Emails Sent</p>
                    <h3 class="text-info"><?= number_format($campaign['emails_sent']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Completion Rate</p>
                    <h3 class="text-warning">
                        <?= $campaign['total_enrolled'] > 0 ? round(($campaign['total_completed'] / $campaign['total_enrolled']) * 100, 1) : 0 ?>%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Sequence</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($emails)): ?>
                <p class="text-muted">No emails in this campaign</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($emails as $i => $e): ?>
                        <div class="d-flex mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" class="style-93328">
                                <strong><?= $e['sequence_order'] ?></strong>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($e['subject']) ?></h6>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-clock"></i> Send after
                                    <?= $e['delay_days'] > 0 ? $e['delay_days'] . ' day(s)' : '' ?>
                                    <?= $e['delay_hours'] > 0 ? ' ' . $e['delay_hours'] . ' hour(s)' : '' ?>
                                    <?= ($e['delay_days'] == 0 && $e['delay_hours'] == 0) ? 'enrollment' : '' ?>
                                    Â· <span class="badge bg-light text-dark"><?= ucfirst($e['channel']) ?></span>
                                </p>
                                <pre class="bg-light p-2 small rounded mb-0" class="style-12331"><?= htmlspecialchars($e['body']) ?></pre>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Recent Enrollments (<?= count($enrollments) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Recipient</th>
                            <th>Step</th>
                            <th>Total Sent</th>
                            <th>Status</th>
                            <th>Enrolled</th>
                            <th>Last Sent</th>
                            <th>Next Send</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enrollments)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No enrollments yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($enrollments as $e): ?>
                                <tr>
                                    <td>#<?= $e['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($e['name'] ?? 'Anonymous') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($e['email']) ?></small>
                                    </td>
                                    <td>Step <?= $e['current_step'] ?> / <?= count($emails) ?></td>
                                    <td><?= $e['total_sent'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= ['active' => 'success', 'paused' => 'warning', 'completed' => 'info', 'unsubscribed' => 'secondary', 'bounced' => 'danger'][$e['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($e['status']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= date('M j, H:i', strtotime($e['enrolled_at'])) ?></small></td>
                                    <td><small><?= $e['last_sent_at'] ? date('M j, H:i', strtotime($e['last_sent_at'])) : '—' ?></small></td>
                                    <td>
                                        <?php if ($e['next_send_at'] && $e['status'] === 'active'): ?>
                                            <small><?= date('M j, H:i', strtotime($e['next_send_at'])) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">—</small>
                                        <?php endif; ?>
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
