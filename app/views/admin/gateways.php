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
                <i class="fas fa-list me-1"></i>View Twilio Logs
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
                    <?= htmlspecialchars(ucfirst($gateway)) ?> — last 100 calls
                </h6>
                <a href="<?= $baseUrl ?>/admin/gateways" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to cards
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Action</th>
                                <th>Recipient</th>
                                <th>Status</th>
                                <th>HTTP</th>
                                <th>Duration</th>
                                <th>Cost</th>
                                <th>When</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No log entries yet.</td></tr>
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
                                <td class="text-truncate" style="max-width: 240px;" title="<?= htmlspecialchars((string)($row['error_message'] ?? '')) ?>">
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
                                <span class="badge bg-success">Configured</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not configured</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                <?= htmlspecialchars($card['detail']) ?>
                            </p>

                            <?php $t = $card['total']; ?>
                            <div class="row text-center mb-2">
                                <div class="col">
                                    <div class="h5 mb-0"><?= (int)($t['total'] ?? 0) ?></div>
                                    <div class="small text-muted">Calls (24h)</div>
                                </div>
                                <div class="col">
                                    <div class="h5 mb-0 text-success"><?= (int)($t['success_count'] ?? 0) ?></div>
                                    <div class="small text-muted">Success</div>
                                </div>
                                <div class="col">
                                    <div class="h5 mb-0 text-danger"><?= (int)$card['error_count'] ?></div>
                                    <div class="small text-muted">Errors</div>
                                </div>
                                <div class="col">
                                    <div class="h5 mb-0">$<?= number_format((float)($t['total_cost'] ?? 0), 2) ?></div>
                                    <div class="small text-muted">Cost</div>
                                </div>
                            </div>

                            <hr/>

                            <p class="small text-muted mb-2">Last 5 calls:</p>
                            <ul class="list-group list-group-flush mb-2" style="max-height: 180px; overflow-y: auto;">
                                <?php if (empty($card['last_5'])): ?>
                                    <li class="list-group-item text-muted small">No calls yet.</li>
                                <?php else: foreach ($card['last_5'] as $row): ?>
                                    <li class="list-group-item p-2 d-flex justify-content-between align-items-center small">
                                        <span>
                                            <span class="badge bg-<?= ($row['status'] ?? '') === 'success' ? 'success' : (($row['status'] ?? '') === 'error' ? 'danger' : 'secondary') ?> me-1">
                                                <?= htmlspecialchars((string)($row['status'] ?? '-')) ?>
                                            </span>
                                            <code><?= htmlspecialchars((string)($row['action'] ?? '-')) ?></code>
                                            <?php if (!empty($row['recipient'])): ?>
                                                <span class="text-muted">→ <?= htmlspecialchars((string)$row['recipient']) ?></span>
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
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-paper-plane me-1"></i>Test
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mt-2">
                                <a href="<?= $baseUrl ?>/admin/gateways/logs/<?= htmlspecialchars($card['key']) ?>" class="small">
                                    <i class="fas fa-list me-1"></i>View logs
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
                    <div class="card-body">
                        <p class="mb-2"><strong>Twilio</strong> — set in <code>.env</code>:</p>
                        <pre class="bg-light p-2 small mb-3">TWILIO_ACCOUNT_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+15555555555
TWILIO_WHATSAPP_NUMBER=+14155238886
TWILIO_VERIFY_SERVICE_SID=VAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TEST_MODE=true  # skip real API during local dev</pre>

                        <p class="mb-2"><strong>Razorpay / Stripe / PhonePe</strong> — payment gateways are wired through their SDK; use the existing test mode flags.</p>

                        <p class="mb-0 text-muted small">All gateway calls are logged to the <code>gateway_logs</code> table (success and failure). Rate-limited at 100 calls/min per process by default.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
