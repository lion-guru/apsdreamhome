<?php
$page_title = $page_title ?? 'EMI Auto-Pay';
$page_heading = $page_heading ?? 'EMI Auto-Payment Dashboard';
$stats = $stats ?? [];
$mandates = $mandates ?? [];
$failedPayments = $failedPayments ?? [];
$isTestMode = $isTestMode ?? true;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>EMI Auto-Payment</h2>
        <div>
            <?php if ($isTestMode): ?>
                <span class="badge bg-warning text-dark me-2"><i class="fas fa-flask me-1"></i>Test Mode</span>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/finance/penalties" class="btn btn-outline-secondary btn-sm me-1"><i class="fas fa-exclamation-triangle me-1"></i>Penalties</a>
            <a href="<?= BASE_URL ?>/admin/finance/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Total Mandates</div>
                    <div class="aps-cp-stat-value text-primary"><?= (int)($stats['total_mandates'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Active Mandates</div>
                    <div class="aps-cp-stat-value text-success"><?= (int)($stats['active_mandates'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Upcoming EMIs</div>
                    <div class="aps-cp-stat-value text-info"><?= (int)($stats['upcoming_emis'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Total Due</div>
                    <div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($stats['total_due_amount'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Status -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Today's Due</div>
                    <div class="aps-cp-stat-value text-warning"><?= (int)($stats['today_due'] ?? 0) ?> installments</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Today's Collected</div>
                    <div class="aps-cp-stat-value text-success"><?= (int)($stats['today_collected'] ?? 0) ?> installments</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Run Now -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-play-circle me-1"></i>Auto-Payment Processing</span>
        </div>
        <div class="aps-cp-card-body">
            <p class="mb-2">Trigger auto-debit for all due installments that have active Razorpay mandates.</p>
            <form id="autoPayForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            </form>
            <button id="runAutoPayBtn" class="btn btn-primary" onclick="runAutoPay()">
                <i class="fas fa-bolt me-1"></i>Run Auto-Payment Now
            </button>
            <div id="autoPayResult" class="mt-3 style-2248"></div>
        </div>
    </div>

    <!-- Mandates Table -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-list me-1"></i>Active Mandates (<?= count($mandates) ?>)</span>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Booking</th>
                            <th>Plot</th>
                            <th class="text-end">Amount</th>
                            <th>Mandate ID</th>
                            <th>Status</th>
                            <th>Next Payment</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($mandates)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No mandates configured yet. Set up mandates from the booking detail page.</td></tr>
                    <?php else: foreach ($mandates as $m): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($m['customer_name'] ?? '-') ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($m['customer_email'] ?? '') ?></small>
                            </td>
                            <td><code><?= htmlspecialchars($m['booking_number'] ?? '-') ?></code></td>
                            <td><?= htmlspecialchars($m['plot_number'] ?? '-') ?></td>
                            <td class="text-end fw-bold">₹<?= number_format((float)$m['amount'], 2) ?></td>
                            <td><code class="small"><?= htmlspecialchars(substr($m['subscription_id'] ?? '-', 0, 20)) ?>...</code></td>
                            <td>
                                <?php
                                $statusCls = 'bg-secondary';
                                $st = $m['status'] ?? 'unknown';
                                if ($st === 'active') $statusCls = 'bg-success';
                                elseif ($st === 'failed') $statusCls = 'bg-danger';
                                elseif ($st === 'cancelled') $statusCls = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $statusCls ?>"><?= ucfirst(htmlspecialchars($st ?? '')) ?></span>
                            </td>
                            <td><?= htmlspecialchars($m['next_payment_date'] ?? 'N/A') ?></td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/admin/bookings/<?= (int)$m['booking_id'] ?>" class="btn btn-outline-primary btn-sm" title="View Booking"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Failed Payments -->
    <?php if (!empty($failedPayments)): ?>
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-exclamation-circle me-1 text-danger"></i>Failed Auto-Payments (<?= count($failedPayments) ?>)</span>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Booking</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Error</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($failedPayments as $fp): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($fp['created_at'] ?? '') ?></small></td>
                            <td><?= htmlspecialchars($fp['customer_name'] ?? '-') ?></td>
                            <td><code><?= htmlspecialchars($fp['booking_number'] ?? '-') ?></code></td>
                            <td class="text-end">₹<?= number_format(((float)($fp['amount_paise'] ?? 0)) / 100, 2) ?></td>
                            <td><code class="small"><?= htmlspecialchars(substr($fp['transaction_id'] ?? '-', 0, 16)) ?></code></td>
                            <td><small class="text-danger"><?= htmlspecialchars($fp['error_message'] ?? '-') ?></small></td>
                            <td class="text-center">
                                <button class="btn btn-outline-warning btn-sm" onclick="retryMandate('<?= htmlspecialchars($fp['subscription_id'] ?? '') ?>')" title="Retry">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function runAutoPay() {
    var btn = document.getElementById('runAutoPayBtn');
    var resultDiv = document.getElementById('autoPayResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';

    var token = '';
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) token = meta.getAttribute('content');
    if (!token) {
        var input = document.querySelector('#autoPayForm input[name="csrf_token"]');
        if (input) token = input.value;
    }

    showLoader();
    fetch('<?= BASE_URL ?>/admin/finance/emi-auto-pay/run', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': token
        },
        body: 'csrf_token=' + encodeURIComponent(token)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        resultDiv.style.display = 'block';
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>' +
                'Processed: <strong>' + data.processed + '</strong> | ' +
                'Failed: <strong>' + data.failed + '</strong> | ' +
                'Skipped: <strong>' + data.skipped + '</strong>. ' +
                'Page will reload in 2 seconds.</div>';
            setTimeout(function() { location.reload(); }, 2000);
            .catch(err => console.error('Request failed:', err));
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i>Error: ' +
                (data.error || 'Unknown error') + '</div>';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt me-1"></i>Run Auto-Payment Now';
    })
    .catch(function(err) {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i>Request failed: ' + err.message + '</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt me-1"></i>Run Auto-Payment Now';
    ).finally(() => hideLoader());
}

function retryMandate(subscriptionId) {
    apsConfirm('Retry auto-debit for this mandate?').then(function(ok) {
        if (!ok) return;
    showToast('Retry queued — will be processed on next cron run.', 'info');
    });
}
</script>
