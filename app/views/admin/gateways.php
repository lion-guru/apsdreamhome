<?php
/**
 * APS Dream Home - Admin Gateway Manager
 *
 * Displays one card per configured gateway with:
 *  - Configuration status
 *  - Last 5 calls
 *  - 24h totals
 *  - Error count
 *  - Test button (where applicable)
 *
 * If `$logs_only` is true, the view renders a full log table for one gateway.
 *
 * @var array  $cards        List of card descriptors (index() only)
 * @var array  $stats        Raw aggregated stats per gateway
 * @var string $admin_phone  Admin's phone (prefill for test form)
 * @var string $csrf_token   CSRF token for forms
 * @var bool   $logs_only    True when this is a logs sub-page
 * @var string $gateway      Active gateway (logs sub-page)
 * @var array  $logs         Recent log rows (logs sub-page)
 */
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$page_title   = $page_title   ?? 'Gateway Manager';
$page_heading = $page_heading ?? 'Gateway Manager';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-network-wired me-2"></i><?= htmlspecialchars($page_heading) ?>
        </h1>
        <?php if (empty($logs_only)): ?>
            <a href="<?= $baseUrl ?>/admin/gateways/logs/twilio" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i><?= __('admin_gw_twilio_logs', null, 'View Twilio Logs') ?>
            </a>
        <?php endif; ?>
    </div>

    <?php
    // Flash messages (setFlash uses $_SESSION['success'|'error'|'warning'|'info'])
    foreach (['success', 'error', 'warning', 'info'] as $flashKey) {
        if (!empty($_SESSION[$flashKey]) || !empty($_SESSION['flash_' . $flashKey])) {
            $msg = $_SESSION[$flashKey] ?? $_SESSION['flash_' . $flashKey];
            $cls = $flashKey === 'success' ? 'success' : ($flashKey === 'error' ? 'danger' : $flashKey);
            echo '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
               . htmlspecialchars((string)$msg)
               . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
               . '</div>';
            unset($_SESSION[$flashKey], $_SESSION['flash_' . $flashKey]);
        }
    }
    ?>

    <?php if (!empty($logs_only)): ?>
        <!-- ============== LOGS SUB-PAGE ============== -->
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list me-1"></i>
                    <?= htmlspecialchars(ucfirst($gateway)) ?> à¢€—� last 100 calls
                </h6>
                                <a href="<?= $baseUrl ?>/admin/gateways" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i><?= __('admin_btn_back_cards', null, 'Back to cards') ?>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= __('admin_gw_th_action', null, 'Action') ?></th>
                                <th><?= __('admin_gw_th_recipient', null, 'Recipient') ?></th>
                                <th><?= __('admin_gw_th_status', null, 'Status') ?></th>
                                <th>HTTP</th>
                                <th><?= __('admin_gw_th_duration', null, 'Duration') ?></th>
                                <th><?= __('admin_gw_th_cost', null, 'Cost') ?></th>
                                <th><?= __('admin_gw_th_when', null, 'When') ?></th>
                                <th><?= __('admin_gw_th_error', null, 'Error') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><?= __('admin_gw_no_logs', null, 'No log entries yet.') ?></td></tr>
                        <?php else: foreach ($logs as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><code><?= htmlspecialchars((string)($row['action'] ?? '-')) ?></code></td>
                                <td><?= htmlspecialchars((string)($row['recipient'] ?? '-')) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($row['status'] ?? '') === 'success' ? 'success' : (($row['status'] ?? '') === 'error' ? 'danger' : 'secondary') ?>">
                                        <?= htmlspecialchars((string)($row['status'] ?? '-')) ?>
                                    </span>
                                </td>
                                <td><?= (int)($row['http_code'] ?? 0) ?: '-' ?></td>
                                <td><?= (int)($row['duration_ms'] ?? 0) ?>ms</td>
                                <td>$<?= number_format((float)($row['cost'] ?? 0), 4) ?></td>
                                <td><span title="<?= htmlspecialchars((string)$row['created_at']) ?>"><?= htmlspecialchars((string)$row['created_at']) ?></span></td>
                                <td class="text-truncate" class="style-96974" title="<?= htmlspecialchars((string)($row['error_message'] ?? '')) ?>">
                                    <?= htmlspecialchars((string)($row['error_message'] ?? '')) ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- ============== GATEWAY CARDS ============== -->
        <div class="row g-3">
            <?php foreach ($cards as $card): ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas <?= htmlspecialchars($card['icon']) ?> me-1"></i>
                                <?= htmlspecialchars($card['name']) ?>
                            </h6>
                            <?php if ($card['configured']): ?>
                                <span class="badge bg-success"><?= __('admin_gw_configured', null, 'Configured') ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= __('admin_gw_not_configured', null, 'Not configured') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <?= htmlspecialchars($card['detail']) ?>
                            </p>

                            <?php $t = $card['total']; ?>
                            <div class="row text-center mb-2">
                                <div class="col">
                                    <div class="h5 mb-0"><?= (int)($t['total'] ?? 0) ?></div>
                                    <div class="small text-muted"><?= __('admin_gw_calls_24h', null, 'Calls (24h)') ?></div>
                                </div>
                                <div class="col">
                                    <div class="h5 mb-0 text-success"><?= (int)($t['success_count'] ?? 0) ?></div>
                                    <div class="small text-muted"><?= __('admin_gw_success', null, 'Success') ?></div>
                                </div>
                                <div class="col">
                                    <div class="h5 mb-0 text-danger"><?= (int)$card['error_count'] ?></div>
                                    <div class="small text-muted"><?= __('admin_gw_errors', null, 'Errors') ?></div>
                                </div>
                                <div class="col">
                                    <div class="h5 mb-0">$<?= number_format((float)($t['total_cost'] ?? 0), 2) ?></div>
                                    <div class="small text-muted"><?= __('admin_gw_cost', null, 'Cost') ?></div>
                                </div>
                            </div>

                            <hr/>

                            <p class="small text-muted mb-2"><?= __('admin_gw_last_5', null, 'Last 5 calls:') ?></p>
                            <ul class="list-group list-group-flush mb-2" class="style-85686">
                                <?php if (empty($card['last_5'])): ?>
                                    <li class="list-group-item text-muted small"><?= __('admin_gw_no_calls_yet', null, 'No calls yet.') ?></li>
                                <?php else: foreach ($card['last_5'] as $row): ?>
                                    <li class="list-group-item p-2 d-flex justify-content-between align-items-center small">
                                        <span>
                                            <span class="badge bg-<?= ($row['status'] ?? '') === 'success' ? 'success' : (($row['status'] ?? '') === 'error' ? 'danger' : 'secondary') ?> me-1">
                                                <?= htmlspecialchars((string)($row['status'] ?? '-')) ?>
                                            </span>
                                            <code><?= htmlspecialchars((string)($row['action'] ?? '-')) ?></code>
                                            <?php if (!empty($row['recipient'])): ?>
                                                <span class="text-muted">à¢— —™ <?= htmlspecialchars((string)$row['recipient']) ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="text-muted"><?= htmlspecialchars((string)($row['created_at'] ?? '')) ?></span>
                                    </li>
                                <?php endforeach; endif; ?>
                            </ul>

                            <?php if (!empty($card['can_test'])): ?>
                                <form method="post" action="<?= $baseUrl ?>/admin/gateways/<?= htmlspecialchars($card['test_action']) ?>" class="mt-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>"/>
                                    <div class="input-group input-group-sm">
                                        <input type="tel" name="phone" class="form-control" placeholder="+91xxxxxxxxxx"
                                               value="<?= htmlspecialchars($admin_phone) ?>" required>
                                        <?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
<button class="btn btn-primary" type="submit">
                                            <i class="fas fa-paper-plane me-1"></i><?= __('admin_gw_btn_test', null, 'Test') ?>
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mt-2">
                                <a href="<?= $baseUrl ?>/admin/gateways/logs/<?= htmlspecialchars($card['key']) ?>" class="small">
                                    <i class="fas fa-list me-1"></i><?= __('admin_gw_view_logs', null, 'View logs') ?>
                                </a>
                                <?php if ($card['configured']): ?>
                                    <span class="small text-success">
                                        <i class="fas fa-check-circle me-1"></i>Ready
                                    </span>
                                <?php else: ?>
                                    <span class="small text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Set env vars
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-1"></i>How to configure
                        </h6>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <p class="mb-2"><strong>Twilio</strong> à¢€—� set in <code>.env</code>:</p>
                        <pre class="bg-light p-2 small mb-3">TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+15555555555
TWILIO_WHATSAPP_NUMBER=+14155238886
TWILIO_VERIFY_SERVICE_SID=VAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TEST_MODE=true  # skip real API during local dev</pre>

                        <p class="mb-2"><strong>Razorpay / Stripe / PhonePe</strong> à¢€—� payment gateways are wired through their SDK; use the existing test mode flags.</p>

                        <p class="mb-0 text-muted small">All gateway calls are logged to the <code>gateway_logs</code> table (success and failure). Rate-limited at 100 calls/min per process by default.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============== RAZORPAY WEBHOOK URL ============== -->
        <?php
            $webhookUrl = '';
            $webhookConfigured = false;
            if (!empty($_ENV['WEBHOOK_PUBLIC_URL']) || getenv('WEBHOOK_PUBLIC_URL')) {
                $webhookUrl = rtrim((string)($_ENV['WEBHOOK_PUBLIC_URL'] ?: getenv('WEBHOOK_PUBLIC_URL')), '/');
                $webhookConfigured = true;
            } else {
                $base = defined('BASE_URL') ? BASE_URL : '';
                $webhookUrl = $base . '/webhook/razorpay';
            }
            $webhookSecret = (string)($_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? getenv('RAZORPAY_WEBHOOK_SECRET') ?: '');
            $webhookSecretSet = $webhookSecret !== '' && strpos($webhookSecret, 'xxxxx') === false;
        ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow border-left-warning">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-link me-1"></i>Razorpay Webhook URL
                        </h6>
                        <?php if ($webhookConfigured): ?>
                            <span class="badge bg-success">Custom URL set</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Auto-derived from BASE_URL</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <p class="small text-muted mb-2">
                            Paste this URL into your <a href="https://dashboard.razorpay.com/app/webhooks" target="_blank" rel="noopener">Razorpay dashboard</a>
                            (Settings &rarr; Webhooks &rarr; Add new webhook) along with the secret below. Razorpay will POST payment events here.
                        </p>

                        <label class="small text-muted mb-1">Webhook URL</label>
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" id="razorpay-webhook-url" class="form-control font-monospace" readonly
                                   value="<?= htmlspecialchars($webhookUrl) ?>">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="var el=document.getElementById('razorpay-webhook-url'); el.select(); document.execCommand('copy'); this.innerHTML='<i class=\'fas fa-check me-1\'></i>Copied!'; setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy me-1\'></i>Copy',1500);">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>

                        <label class="small text-muted mb-1">Webhook Secret (paste into Razorpay dashboard &rarr; "Secret" field)</label>
                        <div class="input-group input-group-sm">
                            <input type="<?= $webhookSecretSet ? 'password' : 'text' ?>" id="razorpay-webhook-secret" class="form-control font-monospace" readonly
                                   value="<?= htmlspecialchars($webhookSecret ?: '(not set - put one in .env as RAZORPAY_WEBHOOK_SECRET)') ?>">
                            <?php if ($webhookSecretSet): ?>
                                <button class="btn btn-outline-secondary" type="button" onclick="var i=document.getElementById('razorpay-webhook-secret'); i.type = (i.type==='password'?'text':'password'); this.innerHTML=(i.type==='password'?'<i class=\'fas fa-eye me-1\'></i>Show':'<i class=\'fas fa-eye-slash me-1\'></i>Hide');">
                                    <i class="fas fa-eye me-1"></i>Show
                                </button>
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="var el=document.getElementById('razorpay-webhook-secret'); el.type='text'; el.select(); document.execCommand('copy'); this.innerHTML='<i class=\'fas fa-check me-1\'></i>Copied!';">
                                    <i class="fas fa-copy me-1"></i>Copy
                                </button>
                            <?php endif; ?>
                        </div>

                        <p class="small text-muted mt-3 mb-0">
                            <i class="fas fa-lightbulb me-1"></i>
                            The URL is auto-derived from <code>BASE_URL</code> + <code>/webhook/razorpay</code>. To override (e.g. behind a CDN or proxy),
                            set <code>WEBHOOK_PUBLIC_URL</code> in <code>.env</code> to the public-facing URL Razorpay should call.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
