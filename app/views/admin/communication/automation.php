<?php

/**
 * Communication Automation Dashboard
 */
$page_title = $page_title ?? 'Communication Automation';
$page_description = $page_description ?? 'Manage automated messaging across WhatsApp, Telegram, SMS, and Email';
$channels = $channels ?? ['whatsapp' => false, 'telegram' => false, 'sms' => false, 'email' => false];
$stats = $stats ?? ['total_messages' => 0, 'inbound' => 0, 'outbound' => 0, 'leads_generated' => 0, 'automated_sent' => 0];
$recent_logs = $recent_logs ?? [];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-bolt me-2 text-primary"></i>Communication Automation</h1>
            <p class="text-muted">Manage automated messaging across WhatsApp, Telegram, SMS, and Email with AI-powered responses</p>
        </div>
    </div>

    <!-- Channel Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="<?= $channels['whatsapp'] ? 'bg-success' : 'bg-secondary' ?> bg-opacity-10 text-success rounded p-3">
                                <i class="fab fa-whatsapp fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">WhatsApp Business</h6>
                            <span class="badge bg-<?= $channels['whatsapp'] ? 'success' : 'secondary' ?>">
                                <?= $channels['whatsapp'] ? 'Connected' : 'Not Configured' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/admin/communication/whatsapp-setup" class="btn btn-sm btn-outline-success w-100">
                            <i class="fas fa-cog me-1"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="<?= $channels['telegram'] ? 'bg-primary' : 'bg-secondary' ?> bg-opacity-10 text-primary rounded p-3">
                                <i class="fab fa-telegram fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Telegram Bot</h6>
                            <span class="badge bg-<?= $channels['telegram'] ? 'primary' : 'secondary' ?>">
                                <?= $channels['telegram'] ? 'Connected' : 'Not Configured' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/admin/communication/telegram-setup" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-cog me-1"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="<?= $channels['sms'] ? 'bg-warning' : 'bg-secondary' ?> bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-sms fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">SMS Gateway (MSG91)</h6>
                            <span class="badge bg-<?= $channels['sms'] ? 'warning' : 'secondary' ?>">
                                <?= $channels['sms'] ? 'Configured' : 'Not Configured' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/admin/communication/sms-setup" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-cog me-1"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="<?= $channels['email'] ? 'bg-info' : 'bg-secondary' ?> bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Email (SMTP)</h6>
                            <span class="badge bg-<?= $channels['email'] ? 'info' : 'secondary' ?>">
                                <?= $channels['email'] ? 'Configured' : 'Not Configured' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/admin/communication/email-templates" class="btn btn-sm btn-outline-info w-100">
                            <i class="fas fa-cog me-1"></i> Configure
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="text-primary mb-1"><?= number_format($stats['total_messages']) ?></h3>
                    <small class="text-muted">Total Messages</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="text-success mb-1"><?= number_format($stats['inbound']) ?></h3>
                    <small class="text-muted">Inbound</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="text-info mb-1"><?= number_format($stats['outbound']) ?></h3>
                    <small class="text-muted">Outbound</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-1"><?= number_format($stats['leads_generated']) ?></h3>
                    <small class="text-muted">Leads Generated</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title h6 fw-bold mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>/admin/communication/automation" class="btn btn-primary">
                            <i class="fas fa-magic me-1"></i> View Automation Rules
                        </a>
                        <a href="<?= BASE_URL ?>/admin/communication/logs" class="btn btn-outline-secondary">
                            <i class="fas fa-history me-1"></i> View All Logs
                        </a>
                        <button class="btn btn-outline-success" onclick="openTestSendModal()">
                            <i class="fas fa-paper-plane me-1"></i> Send Test Message
                        </button>
                        <a href="<?= BASE_URL ?>/admin/settings/email" class="btn btn-outline-info">
                            <i class="fas fa-envelope me-1"></i> SMTP Settings
                        </a>
                        <a href="<?= BASE_URL ?>/admin/settings/sms" class="btn btn-outline-warning">
                            <i class="fas fa-sms me-1"></i> SMS Settings
                        </a>
                        <a href="<?= BASE_URL ?>/admin/settings/payment" class="btn btn-outline-dark">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Communication Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0">Recent Communication Logs</h5>
                    <a href="<?= BASE_URL ?>/admin/communication/logs" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_logs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x mb-3" class="style-39608"></i>
                            <h5 class="text-muted">No communication logs yet</h5>
                            <p class="text-muted mb-0">Start receiving messages or send test messages to see logs here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Channel</th>
                                        <th>Direction</th>
                                        <th>Contact</th>
                                        <th>Message Preview</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_logs as $log): ?>
                                        <tr>
                                            <td><small class="text-muted"><?= date('M d, H:i', strtotime($log['created_at'])) ?></small></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $log['channel'] === 'whatsapp' ? 'success' : 
                                                    ($log['channel'] === 'telegram' ? 'primary' : 
                                                    ($log['channel'] === 'sms' ? 'warning' : 'info')) ?>">
                                                    <i class="fa-<?= 
                                                        $log['channel'] === 'whatsapp' ? 'fab fa-whatsapp' : 
                                                        ($log['channel'] === 'telegram' ? 'fab fa-telegram' : 
                                                        ($log['channel'] === 'sms' ? 'fas fa-sms' : 'fas fa-envelope')) ?> me-1"></i>
                                                    <?= ucfirst($log['channel']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $log['direction'] === 'inbound' ? 'info' : 'success' ?>">
                                                    <i class="fas fa-<?= $log['direction'] === 'inbound' ? 'arrow-down' : 'arrow-up' ?> me-1"></i>
                                                    <?= ucfirst($log['direction']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($log['contact_identifier'] ?? '') ?></td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars(mb_strimwidth($log['message_text'], 0, 60, '...')) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $log['status'] === 'sent' ? 'success' : 
                                                    ($log['status'] === 'delivered' ? 'primary' : 
                                                    ($log['status'] === 'read' ? 'info' : 'danger')) ?>">
                                                    <?= ucfirst($log['status']) ?>
                                                </span>
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
    </div>
</div>

<!-- Test Send Modal -->
<div class="modal fade" id="testSendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="testSendForm" action="<?= BASE_URL ?>/admin/communication/test-send" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Send Test Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Channel</label>
                        <select name="channel" class="form-select" required>
                            <option value="">Select Channel</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="telegram">Telegram</option>
                            <option value="sms">SMS</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <input type="text" name="to" class="form-control" placeholder="Phone number or email" required>
                        <small class="form-text">Phone: +91XXXXXXXXXX or email: user@example.com</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Enter your test message..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Test Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openTestSendModal() {
    var modal = new bootstrap.Modal(document.getElementById('testSendModal'));
    modal.show();
}

document.getElementById('testSendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = this.querySelector('button[type="submit"]');
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    btn.disabled = true;

    var formData = new FormData(this);
    showLoader();
    fetch('<?= BASE_URL ?>/admin/communication/test-send', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('Success: ' + data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('testSendModal')).hide();
        } else {
            showToast('Error: ' + data.message, 'danger');
        }
    })
    .catch(function() {
        showToast('Network error occurred', 'danger');
    })
    .finally(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
).finally(() => hideLoader());
</script>